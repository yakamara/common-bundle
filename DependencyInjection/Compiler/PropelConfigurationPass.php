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

use Propel\Generator\Builder\Om as PropelBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Yakamara\CommonBundle\Propel\MysqlPlatform;
use Yakamara\CommonBundle\Propel\ObjectBuilder;
use Yakamara\CommonBundle\Propel\QueryBuilder;

class PropelConfigurationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $config = $container->getParameter('propel.configuration');

        $config['generator']['platformClass'] ??= MysqlPlatform::class;
        $config['generator']['objectModel']['addClassLevelComment'] = false;

        if (PropelBuilder\ObjectBuilder::class === ltrim($config['generator']['objectModel']['builders']['object'], '\\')) {
            $config['generator']['objectModel']['builders']['object'] = ObjectBuilder::class;
        }
        if (PropelBuilder\QueryBuilder::class === ltrim($config['generator']['objectModel']['builders']['query'], '\\')) {
            $config['generator']['objectModel']['builders']['query'] = QueryBuilder::class;
        }

        $container->setParameter('propel.configuration', $config);
    }
}
