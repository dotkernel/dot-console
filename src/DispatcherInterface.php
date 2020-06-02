<?php
/**
 * @see https://github.com/dotkernel/frontend/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/frontend/blob/master/LICENSE.md MIT License
 */

namespace Dot\Console;

use Laminas\Console\Adapter\AdapterInterface as ConsoleAdapter;
use Laminas\Console\RouteMatcher\RouteMatcherInterface;

/**
 * Interface DispatcherInterface
 * @package Dot\Console
 */
interface DispatcherInterface
{
    /**
     * Map a command name to its handler.
     *
     * @param string $command
     * @param callable|string $command A callable command, or a string service
     *     or class name to use as a handler.
     * @return self Should implement a fluent interface.
     */
    public function map($command, $callable);

    /**
     * Does the dispatcher have a handler for the given command?
     *
     * @param string $command
     * @return bool
     */
    public function has($command);

    /**
     * @param $args
     * @param RouteCollector $route
     * @param ConsoleAdapter $console
     * @return mixed
     */
    public function dispatch($args, RouteCollector $route, ConsoleAdapter $console);
}
