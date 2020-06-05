<?php
/**
 * @see https://github.com/dotkernel/frontend/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/frontend/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace Dot\Console\Factory;

use Dot\Console\Dispatcher;
use Dot\ErrorHandler\ErrorHandler;
use Dot\ErrorHandler\ErrorHandlerInterface;
use Psr\Container\ContainerInterface;
use Laminas\Console\Console;
use Dot\Console\Application;
use Laminas\Log\Logger;
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
        /** @var Logger $logger */
        $logger = $container->get('dot-log.my_logger');
        $dispatcher = new Dispatcher($container);

        $app = new Application(
            $container->get('config')['dot_console']['name'],
            $container->get('config')['dot_console']['version'],
            $container->get('config')['dot_console']['commands'],
            Console::getInstance(),
            $dispatcher,
            $logger,
            $container->get(ErrorHandlerInterface::class)
        );

        return $app;
    }
}
