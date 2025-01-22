<?php declare(strict_types=1);

/*
 * This file is part of the common-bundle package.
 *
 * (c) Yakamara Media GmbH & Co. KG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yakamara\CommonBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BaseModelFakerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        spl_autoload_register([$this, 'loadClass'], true, true);
    }

    public function loadClass($class): void
    {
        if (!str_starts_with($class, 'App\\Model\\')) {
            return;
        }

        $class = substr($class, 10);

        if (str_contains($class, '\\') || class_exists('App\\Model\\Base\\'.$class)) {
            return;
        }

        $dir = __DIR__.'/../../../../../src/Model';

        if (!file_exists($dir.'/'.$class.'.php') || !file_exists($dir.'/'.$class.'Query.php')) {
            return;
        }

        eval('namespace App\\Model; class '.$class.' {}');
    }
}
