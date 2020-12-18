<?php
/**
 * @see https://github.com/dotkernel/frontend/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/frontend/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace Dot\Console;

use Dot\Console\Factory\ApplicationFactory;
use Dot\Console\Application;

/**
 * Class ConfigProvider
 * @package Dot\Console
 */
class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencies(),

            'dot_console' => [
                'version' => '3.2.2',
                'name' => 'DotKernel Console',
                'showVersion' => true,
                'lock' => true,
                'commands' => [

                ],
            ]
        ];
    }

    public function getDependencies()
    {
        return [
            'factories' => [
                Application::class => ApplicationFactory::class,
            ]
        ];
    }
}
