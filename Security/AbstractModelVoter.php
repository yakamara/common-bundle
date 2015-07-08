<?php

namespace Yakamara\CommonBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractModelVoter extends AbstractVoter
{
    public function supportsClass($class)
    {
        $modelClass = $this->getModelClass();
        return $modelClass === $class || is_subclass_of($class, $modelClass);
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!$object || !$this->supportsClass(get_class($object))) {
            return self::ACCESS_ABSTAIN;
        }

        return parent::vote($token, $object, $attributes);
    }

    abstract protected function getModelClass();
}
