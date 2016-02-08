<?php

namespace Yakamara\CommonBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Intl\Intl;
use Yakamara\CommonBundle\Security\SecurityContext;

class SecurityExtension extends \Twig_Extension
{
    protected $security;

    public function __construct(SecurityContext $security)
    {
        $this->security = $security;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('switched_user', function () {
                return $this->security->isUserSwitched();
            }),
            new \Twig_SimpleFunction('switched_user_source', function () {
                return $this->security->getSwitchedUserSource();
            }),
        ];
    }

    public function getName()
    {
        return 'yakamara_security_extension';
    }
}
