<?php
/**
 * @see https://github.com/dotkernel/frontend/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/frontend/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace Dot\Console\Command;

use Zend\Console\Adapter\AdapterInterface;
use ZF\Console\Route;

/**
 * Class AbstractCommand
 * @package Dot\Console\Command
 */
abstract class AbstractCommand
{
    /**
     * @param Route $route
     * @param AdapterInterface $console
     */
    abstract public function __invoke(Route $route, AdapterInterface $console);
}
