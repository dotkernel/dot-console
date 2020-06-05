<?php

namespace Dot\Console;

use Laminas\Console\Adapter\AdapterInterface as ConsoleAdapter;

/**
 * Interface DispatcherInterface
 * @package Dot\Console
 */
interface DispatcherInterface
{

    /**
     * @param $command
     * @param $callable
     * @return mixed
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
