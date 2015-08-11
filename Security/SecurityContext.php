<?php

namespace Yakamara\CommonBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Role\SwitchUserRole;
use Symfony\Component\Security\Core\User\UserInterface;

class SecurityContext
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Returns the current security token.
     *
     * @return TokenInterface|null A TokenInterface instance or null if no authentication information is available
     */
    public function getToken()
    {
        return $this->tokenStorage->getToken();
    }

    /**
     * Returns a user representation.
     *
     * @return UserInterface|\AppBundle\Model\User|null
     */
    public function getUser()
    {
        $token = $this->getToken();

        return $token ? $token->getUser() : null;
    }

    /**
     * Checks if the attributes are granted against the current authentication token and optionally supplied object.
     *
     * @param mixed $attributes
     * @param mixed $object
     *
     * @return bool
     */
    public function isGranted($attributes, $object = null)
    {
        return $this->authorizationChecker->isGranted($attributes, $object);
    }

    /**
     * Checks if the user is switched
     *
     * @return bool
     */
    public function isUserSwitched()
    {
        return $this->isGranted('ROLE_PREVIOUS_ADMIN');
    }

    /**
     * Returns the orignal user
     *
     * @return UserInterface|\AppBundle\Model\User|null
     */
    public function getSwitchedUserSource()
    {
        foreach ($this->getToken()->getRoles() as $role) {
            if ($role instanceof SwitchUserRole) {
                return $role->getSource()->getUser();
            }
        }
    }
}