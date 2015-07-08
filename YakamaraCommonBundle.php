<?php

namespace Yakamara\CommonBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Yakamara\CommonBundle\DependencyInjection\Compiler\AddSecurityVotersPass;

class YakamaraCommonBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddSecurityVotersPass());
    }
}
