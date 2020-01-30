<?php
/**
 * @see https://github.com/dotkernel/frontend/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/frontend/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace Dot\Console\Factory;

use Psr\Container\ContainerInterface;
use Laminas\Console\Console;
use ZF\Console\Application;
use ZF\Console\Dispatcher;

/**
 * Class ApplicationFactory
 * @package Dot\Console\Factory
 */
class ApplicationFactory
{
    /**
     * @param ContainerInterface $container
     * @return Application
     */
    public function __invoke(ContainerInterface $container)
    {
        $dispatcher = new Dispatcher($container);

        $app = new Application(
            $container->get('config')['dot_console']['name'],
            $container->get('config')['dot_console']['version'],
            $container->get('config')['dot_console']['commands'],
            Console::getInstance(),
            $dispatcher
        );

        $app->setDebug($container->get('config')['debug']);
        return $app;
    }
}
