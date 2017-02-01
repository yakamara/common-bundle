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

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractVoter implements VoterInterface
{
    /** @var AccessDecisionManagerInterface */
    private $decisionManager;

    private $supportedClass;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        if (!$this->supports($subject)) {
            return self::ACCESS_ABSTAIN;
        }

        // abstain vote by default in case none of the attributes are supported
        $vote = self::ACCESS_ABSTAIN;

        $arguments = [$token];
        if (is_object($subject)) {
            $arguments[] = $subject;
        }

        foreach ($attributes as $attribute) {
            if (!in_array($attribute, $this->getSupportedAttributes())) {
                continue;
            }

            // as soon as at least one attribute is supported, default is to deny access
            $vote = self::ACCESS_DENIED;

            $method = 'can'.strtr(ucwords(strtr($attribute, '_', ' ')), [' ' => '']);
            if ($token->getUser() instanceof UserInterface && $this->$method(...$arguments)) {
                // grant access as soon as at least one voter returns a positive response
                return self::ACCESS_GRANTED;
            }
        }

        return $vote;
    }

    /**
     * @param string|object $subject
     *
     * @return bool
     */
    protected function supports($subject): bool
    {
        $class = $this->getSupportedClass();

        if (is_object($subject)) {
            return $subject instanceof $class;
        }

        if (!is_string($subject)) {
            return false;
        }

        $subject = strtolower($subject);

        if ($subject === $class || is_subclass_of($subject, $class)) {
            return true;
        }

        $subject = 'appbundle\\model\\'.$subject;

        return $subject === $class || is_subclass_of($subject, $class);
    }

    protected function getSupportedClass(): string
    {
        if (!$this->supportedClass) {
            $pos = strrpos(get_class($this), '\\');
            $pos = false === $pos ? 0 : $pos + 1;
            $this->supportedClass = 'appbundle\\model\\'.strtolower(substr(get_class($this), $pos, -5));
        }

        return $this->supportedClass;
    }

    abstract protected function getSupportedAttributes(): array;

    protected function isGranted(TokenInterface $token, $attributes, $subject = null): bool
    {
        return $this->decisionManager->decide($token, (array) $attributes, $subject);
    }
}
