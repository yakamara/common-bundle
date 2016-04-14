<?php

namespace Yakamara\CommonBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractVoter implements VoterInterface
{
    /** @var AccessDecisionManagerInterface */
    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!$this->supportsObject($object)) {
            return self::ACCESS_ABSTAIN;
        }

        // abstain vote by default in case none of the attributes are supported
        $vote = self::ACCESS_ABSTAIN;

        foreach ($attributes as $attribute) {
            if (!in_array($attribute, $this->getSupportedAttributes())) {
                continue;
            }

            // as soon as at least one attribute is supported, default is to deny access
            $vote = self::ACCESS_DENIED;

            $method = 'can'.strtr(ucwords(strtr($attribute, '_', ' ')), [' ' => '']);
            if ($token->getUser() instanceof UserInterface && $this->$method($token, $object)) {
                // grant access as soon as at least one voter returns a positive response
                return self::ACCESS_GRANTED;
            }
        }

        return $vote;
    }

    abstract protected function supportsObject($object);

    abstract protected function getSupportedAttributes();

    protected function isGranted(TokenInterface $token, $attributes, $object = null)
    {
        return $this->decisionManager->decide($token, (array) $attributes, $object);
    }
}
