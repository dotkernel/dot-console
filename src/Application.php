<?php

namespace Dot\Console;

use Dot\ErrorHandler\ErrorHandlerInterface;
use InvalidArgumentException;
use Laminas\Console\RouteMatcher\RouteMatcherInterface;
use Traversable;
use Laminas\Console\Adapter\AdapterInterface as Console;
use Laminas\Console\Console as DefaultConsole;
use Laminas\Console\ColorInterface as Color;
use Laminas\Log\Logger;

/**
 * Create and execute console applications.
 */
class Application
{
    /**
     * @var null|string|callable
     */
    protected $banner;

    /**
     * @var Console
     */
    protected Console $console;

    /**
     * Flag to specify if the application is in debug mode
     *
     * @var boolean
     */
    protected bool $debug = false;

    /**
     * @var DispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var string
     */
    protected string $commandName;

    /**
     * @var RouteCollector
     */
    protected RouteCollector $routeCollection;

    /**
     * @var string
     */
    protected string $version;

    /**
     * @var bool
     */
    protected bool $showVersion;

    /**
     * @var bool
     */
    protected bool $lock;

    /**
     * @var bool
     */
    protected bool $bannerDisabledForUserCommands = false;

    /**
     * Application constructor.
     * @param string $name
     * @param string $version
     * @param array $routes
     * @param bool $showVersion
     * @param bool $lock
     * @param Console|null $console
     * @param DispatcherInterface|null $dispatcher
     */
    public function __construct(
        string $name,
        string $version,
        array $routes,
        bool $showVersion,
        bool $lock,
        Console $console = null,
        DispatcherInterface $dispatcher = null
    ) {
        if (! is_array($routes) && ! $routes instanceof Traversable) {
            throw new InvalidArgumentException('Routes must be provided as an array or Traversable object');
        }

        $this->name       = $name;
        $this->version    = $version;
        $this->showVersion = $showVersion;
        $this->lock = $lock;

        if (null === $console) {
            $console = DefaultConsole::getInstance();
        }

        $this->console    = $console;

        if (null === $dispatcher) {
            $dispatcher = new Dispatcher();
        }

        $this->dispatcher = $dispatcher;

        $this->routeCollection = $routeCollection = new RouteCollector();
        $this->setRoutes($routes);

        if (true === $showVersion) {
            $this->showVersion($console);
        }
    }

    /**
     * Run the application
     *
     * @param array $args
     * @return int
     */
    public function run(array $args = null)
    {
        $this->setProcessTitle();

        if ($args === null) {
            global $argv;
            $args = array_slice($argv, 1);
        }

        return $this->processRun($args);
    }

    /**
     * Set CLI process title (PHP versions >= 7.1)
     */
    protected function setProcessTitle()
    {
        if (version_compare(PHP_VERSION, '7.1', 'lt')) {
            return;
        }

        // Mac OS X does not support cli_set_process_title() due to security issues
        if (PHP_OS == 'Darwin') {
            return;
        }

        cli_set_process_title($this->name);
    }

    /**
     * Process run
     *
     * @param array $args
     * @return int
     */
    protected function processRun(array $args)
    {
        if (empty($args)) {
            $this->showMessage($this->banner);
            $this->showUsageMessage();
            return 0;
        }

        if ($this->lock) {
            $cwd = getcwd();
            if (! is_dir($cwd . '/data/lock')) {
                mkdir($cwd . '/data/lock');
            }

            $lockFile = sprintf('%s/data/lock/%s.lock', $cwd, $args[0] . '-cron');
            $fp = fopen($lockFile, "w+");
            if (! flock($fp, LOCK_EX | LOCK_NB, $wouldBlock)) {
                if ($wouldBlock) {
                    $this->console->writeLine('Another process holds the lock!');
                    fclose($fp);
                    return 0;
                }
            }
        }

        $route = $this->routeCollection->match($args);

        if (! $route instanceof RouteMatcherInterface) {
            $this->showMessage($this->banner);
            $name  = $args[0];
            $this->routeCollection->getRoute($name);
            $this->showUnmatchedRouteMessage($args);
            return 1;
        }

        if (! $this->bannerDisabledForUserCommands) {
            $this->showMessage($this->banner);
        }

        return $this->dispatcher->dispatch($args, $this->routeCollection, $this->console);
    }

    /**
     * Display a message
     *
     * @param string|callable $messageOrCallable
     */
    public function showMessage($messageOrCallable)
    {
        if (is_string($messageOrCallable) && ! is_callable($messageOrCallable)) {
            $this->console->writeLine($messageOrCallable);
            return;
        }

        if (is_callable($messageOrCallable)) {
            call_user_func($messageOrCallable, $this->console);
        }
    }

    /**
     * Displays a usage message for the router
     *
     * @param null|string $name
     */
    public function showUsageMessage($name = null)
    {
        $console = $this->console;

        if ($name === null) {
            $console->writeLine('Available commands:', Color::GREEN);
            $console->writeLine('');
        }

        $maxSpaces = $this->calcMaxString($this->routeCollection->getRouteNames()) + 2;

        foreach ($this->routeCollection as $routeName => $route) {

            $spaces = $maxSpaces - strlen($routeName);
            $console->write(' ' . $routeName, Color::GREEN);
            $console->writeLine(str_repeat(' ', $spaces));
        }
    }

    /**
     * Show message indicating inability to match a route.
     *
     * @param array $args
     */
    protected function showUnmatchedRouteMessage(array $args)
    {
        $this->console->write('Unrecognized command: ', Color::RED);
        $this->console->writeLine(implode(' ', $args));
        $this->console->writeLine('');
        $this->showUsageMessage();
    }

    /**
     * Calculate the maximum string length for an array
     *
     * @param array $data
     *
     * @return int
     */
    protected function calcMaxString(array $data = [])
    {
        $maxLength = 0;

        foreach ($data as $name) {
            if (strlen($name) > $maxLength) {
                $maxLength = strlen($name);
            }
        }

        return $maxLength;
    }

    /**
     * Set routes to use
     *
     * @param array|Traversable $routes
     * @return self
     */
    protected function setRoutes($routes)
    {
        foreach ($routes as $route) {
            if (is_array($route)) {
                $this->routeCollection->addRouteSpec($route);
                $this->mapRouteHandler($route);
                continue;
            }
        }

        return $this;
    }

    /**
     * Map a route handler
     *
     * @param array $route
     */
    protected function mapRouteHandler(array $route)
    {
        if (! isset($route['handler'])) {
            return;
        }

        $command = $route['name'];
        $this->commandName = $command;

        $this->dispatcher->map($command, $route['handler']);
    }


    /**
     * Display the application version
     *
     * @param Console $console
     * @return int
     */
    public function showVersion(Console $console): int
    {
        $console->writeLine(sprintf("%s, version %s %s", $this->name, $this->version, PHP_EOL));
        return 0;
    }
}