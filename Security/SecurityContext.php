<?php declare(strict_types=1);

/*
 * This file is part of the common-bundle package.
 *
 * (c) Yakamara Media GmbH & Co. KG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yakamara\CommonBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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
     * Returns the token storage.
     *
     * @return TokenStorageInterface
     */
    public function getTokenStorage(): TokenStorageInterface
    {
        return $this->tokenStorage;
    }

    /**
     * Returns the current security token.
     *
     * @return null|TokenInterface A TokenInterface instance or null if no authentication information is available
     */
    public function getToken(): ?TokenInterface
    {
        return $this->tokenStorage->getToken();
    }

    /** @noinspection PhpUndefinedNamespaceInspection */
    /** @noinspection PhpUndefinedClassInspection */

    /**
     * Returns a user representation.
     *
     * @return null|\App\Model\User
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
    public function isGranted($attributes, $object = null): bool
    {
        return $this->authorizationChecker->isGranted($attributes, $object);
    }

    /**
     * Checks if any given role is granted.
     */
    public function isGrantedAny(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->authorizationChecker->isGranted($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the user is switched.
     *
     * @return bool
     */
    public function isUserSwitched(): bool
    {
        return $this->getToken() instanceof SwitchUserToken;
    }

    /** @noinspection PhpUndefinedNamespaceInspection */
    /** @noinspection PhpUndefinedClassInspection */

    /**
     * Returns the orignal user.
     *
     * @return null|\App\Model\User
     */
    public function getSwitchedUserSource()
    {
        $token = $this->getToken();

        if (!$token instanceof SwitchUserToken) {
            return null;
        }

        return $token->getOriginalToken()->getUser();
    }
}
