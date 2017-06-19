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
    public function process(ContainerBuilder $container)
    {
        spl_autoload_register([$this, 'loadClass'], true, true);
    }

    private function loadClass($class)
    {
        if ('AppBundle\\Model\\' !== substr($class, 0, 16)) {
            return;
        }

        $class = substr($class, 16);
        $dir = __DIR__.'/../../../../../src/Model';

        if (file_exists($dir.'/Base/'.$class.'.php') || !file_exists($dir.'/'.$class.'.php')) {
            return;
        }

        eval('namespace AppBundle\\Model; class '.$class.' {}');
    }
}
