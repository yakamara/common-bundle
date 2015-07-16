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
        if (!$class) {
            return false;
        }

        $modelClass = $this->getModelClass();
        return $modelClass === $class || is_subclass_of($class, $modelClass);
    }

    abstract protected function getModelClass();
}
