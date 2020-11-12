<?php
/**
 * @see https://github.com/dotkernel/frontend/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/frontend/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace Dot\Console\Factory;

use Dot\Console\Application;
use Dot\Console\Dispatcher;
use Dot\ErrorHandler\ErrorHandlerInterface;
use Psr\Container\ContainerInterface;
use Laminas\Console\Console;
use Exception;

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
    public function __invoke(ContainerInterface $container): Application
    {
        if (! is_bool($container->get('config')['dot_console']['showVersion'])) {
            throw new Exception("Param : 'showVersion' must be a boolean value.");
        }
        return new Application(
            $container->get('config')['dot_console']['name'],
            $container->get('config')['dot_console']['version'],
            $container->get('config')['dot_console']['commands'],
            $container->get('config')['dot_console']['showVersion'],
            Console::getInstance(),
            new Dispatcher($container)
        );
    }
}
