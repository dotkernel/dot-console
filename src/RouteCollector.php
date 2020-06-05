<?php

namespace Dot\Console;

use ArrayIterator;
use Countable;
use DomainException;
use InvalidArgumentException;
use IteratorAggregate;
use Laminas\Console\RouteMatcher\RouteMatcherInterface;
use Laminas\Filter\Callback as CallbackFilter;
use Laminas\Filter\FilterInterface;
use Laminas\Validator\Callback as CallbackValidator;
use Laminas\Validator\ValidatorInterface;
use Laminas\Console\RouteMatcher\DefaultRouteMatcher as Route;
use Traversable;

/**
 * Class RouteCollector
 * @package Dot\Console
 */
class RouteCollector implements Countable, IteratorAggregate, RouteMatcherInterface
{
    /**
     * @var array
     */
    protected array $routes = [];

    /**
     * Implement Countable
     *
     * @return int
     */
    public function count()
    {
        return count($this->routes);
    }

    /**
     * @var null|array
     */
    protected ?array $matches;

    /**
     * @return ArrayIterator|Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->routes);
    }

    /**
     * @param Route $route
     * @param $name
     * @return $this
     */
    public function addRoute(Route $route, $name)
    {
        $this->routes[$name] = $route;
        ksort($this->routes, defined('SORT_NATURAL') ? constant('SORT_NATURAL') : SORT_STRING);

        return $this;
    }

    /**
     * @param array $spec
     * @return $this
     */
    public function addRouteSpec(array $spec)
    {
        if (! isset($spec['name'])) {
            throw new InvalidArgumentException('Route specification is missing a route name');
        }
        $name = $spec['name'];

        if (! isset($spec['route'])) {
            $spec['route'] = $spec['name'];
        }
        $routeString = $this->prependRouteWithCommand(
            $name,
            $spec['route'],
            array_key_exists('prepend_command_to_route', $spec) ? $spec['prepend_command_to_route'] : true
        );
        
        $constraints        = (isset($spec['constraints']) && is_array($spec['constraints']))
            ? $spec['constraints']
            : [];
        $defaults           = (isset($spec['defaults']) && is_array($spec['defaults']))
            ? $spec['defaults']
            : [];
        $aliases            = (isset($spec['aliases']) && is_array($spec['aliases']))
            ? $spec['aliases']
            : [];
        $filters            = (isset($spec['filters']) && is_array($spec['filters']))
            ? $spec['filters']
            : null;
        $validators         = (isset($spec['validators']) && is_array($spec['validators']))
            ? $spec['validators']
            : null;

        $filters    = $this->prepareFilters($filters);
        $validators = $this->prepareValidators($validators);


        $route = new Route($routeString, $constraints, $defaults, $aliases, $filters, $validators);

        $this->addRoute($route, $name);
        return $this;
    }

    /**
     * Does the named route exist?
     *
     * @param string $name
     * @return bool
     */
    public function hasRoute($name)
    {
        return array_key_exists($name, $this->routes);
    }

    /**
     * Retrieve a named route
     *
     * @param  string $name
     * @return null|Route
     */
    public function getRoute($name)
    {
        if (! $this->hasRoute($name)) {
            return null;
        }
        return $this->routes[$name];
    }

    /**
     * Retrieve a custom named route
     *
     * @param  string $name
     * @return null|string
     */
    public function getCustomRoute($name)
    {
        foreach ($this->routes as $route => $content) {
            $extractor = explode(' ', $route);
            if($name === $extractor[0]){
                return $route;
            }
        }
        return null;
    }

    /**
     * Retrieve all route names
     *
     * @return array
     */
    public function getRouteNames()
    {
        return array_keys($this->routes);
    }

    /**
     * Determine if any route matches
     *
     * @param  array|null $params
     * @return false|Route
     */
    public function match($params)
    {
        if (! is_array($params) && null !== $params) {
            throw new InvalidArgumentException(sprintf(
                '%s expects an array of arguments (typically $argv) or a null value',
                __METHOD__
            ));
        }

        $params = (array) $params;

        foreach ($this as $route) {
            $matches = $route->match($params);
            if (is_array($matches)) {
                $this->matches = $matches;
                return $route;
            }
        }

        return false;
    }

    /**
     * Was the parameter matched?
     *
     * @param string $param
     * @return bool
     */
    public function matchedParam($param)
    {
        if (! is_array($this->matches)) {
            return false;
        }
        return array_key_exists($param, $this->matches);
    }

    /**
     * Retrieve a matched parameter
     *
     * @param string $param
     * @param mixed $default
     * @return mixed
     */
    public function getMatchedParam($param, $default = null)
    {
        if (! $this->matchedParam($param)) {
            return $default;
        }
        return $this->matches[$param];
    }

    /**
     * Prepare filters
     *
     *
     * @param  null|array $filters
     * @return array|null
     * @throws DomainException
     */
    protected function prepareFilters(array $filters = null)
    {
        if (null === $filters) {
            return null;
        }

        foreach ($filters as $name => $filter) {
            if (is_string($filter) && class_exists($filter)) {
                $filter = new $filter();
            }

            if ($filter instanceof FilterInterface) {
                $filters[$name] = $filter;
                continue;
            }

            if (is_callable($filter)) {
                $filters[$name] = new CallbackFilter($filter);
                continue;
            }

            throw new DomainException(sprintf(
                'Invalid filter provided for "%s"; expected Callable or Laminas\Filter\FilterInterface, received "%s"',
                $name,
                $this->getType($filter)
            ));
        }

        return $filters;
    }

    /**
     * Prepare validators
     *
     *
     * @param  array $validators
     * @return array|null
     * @throws DomainException
     */
    protected function prepareValidators(array $validators = null)
    {
        if (null === $validators) {
            return null;
        }

        foreach ($validators as $name => $validator) {
            if (is_string($validator) && class_exists($validator)) {
                $validator = new $validator();
            }

            if ($validator instanceof ValidatorInterface) {
                $validators[$name] = $validator;
                continue;
            }

            if (is_callable($validator)) {
                $validators[$name] = new CallbackValidator($validator);
                continue;
            }

            throw new DomainException(sprintf(
                'Invalid validator provided for "%s"; expected Callable or '
                . 'Laminas\Validator\ValidatorInterface, received "%s"',
                $name,
                $this->getType($validator)
            ));
        }

        return $validators;
    }

    /**
     * Get an item's type, for error reporting
     *
     * @param  mixed $subject
     * @return string
     */
    protected function getType($subject)
    {
        switch (true) {
            case (is_object($subject)):
                $type = get_class($subject);
                break;
            case (is_string($subject)):
                $type = $subject;
                break;
            default:
                $type = gettype($subject);
                break;
        }
        return $type;
    }

    /**
     * Prepend the route with the command
     *
     * @param string $command
     * @param string $route
     * @param bool $prependFlag
     * @return string
     */
    protected function prependRouteWithCommand($command, $route, $prependFlag)
    {
        if (true !== $prependFlag) {
            return $route;
        }

        if (preg_match('/^(?:' . preg_quote($command) . ')(?:\s|$)/', $route)) {
            return $route;
        }

        return sprintf('%s %s', $command, $route);
    }
}
