<?php

namespace Yakamara\CommonBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Role\SwitchUserRole;

class SwitchUserChecker
{
    /** @var SecurityContext */
    private $securityContext;

    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function isUserSwitched()
    {
        return $this->securityContext->isGranted('ROLE_PREVIOUS_ADMIN');
    }

    public function getSwitchedUserSource()
    {
        foreach ($this->securityContext->getToken()->getRoles() as $role) {
            if ($role instanceof SwitchUserRole) {
                return $role->getSource()->getUser();
            }
        }
    }
}
