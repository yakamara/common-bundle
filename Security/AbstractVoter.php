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

abstract class AbstractVoter implements VoterInterface
{
    /** @var AccessDecisionManagerInterface */
    private $decisionManager;

    /** @var string|null */
    private $class;

    /** @var string|null */
    private $name;

    /** @var array|null */
    private $methods;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        if (null === $subject) {
            return self::ACCESS_ABSTAIN;
        }

        if (null === $this->class) {
            $this->class = $this->getSupportedClass();
            $this->name = $this->getSupportedName();
        }

        if (!$subject instanceof $this->class && $subject !== $this->class && $subject !== $this->name) {
            return self::ACCESS_ABSTAIN;
        }

        if (null === $this->methods) {
            $this->methods = $this->getSupportedMethods();
        }

        // abstain vote by default in case none of the attributes are supported
        $vote = self::ACCESS_ABSTAIN;

        $arguments = [$token];
        if (is_object($subject)) {
            $arguments[] = $subject;
        }

        foreach ($attributes as $attribute) {
            if (!isset($this->methods[$attribute])) {
                continue;
            }

            // as soon as at least one attribute is supported, default is to deny access
            $vote = self::ACCESS_DENIED;

            $method = $this->methods[$attribute];
            if ($token->getUser() instanceof \App\Model\User && $this->$method(...$arguments)) {
                // grant access as soon as at least one voter returns a positive response
                return self::ACCESS_GRANTED;
            }
        }

        return $vote;
    }

    /** @noinspection PhpUndefinedNamespaceInspection */
    /** @noinspection PhpUndefinedClassInspection */

    /**
     * @return null|\App\Model\User
     */
    public function getUser(TokenInterface $token)
    {
        $user = $token->getUser();

        return $user instanceof \App\Model\User ? $user : null;
    }

    protected function getSupportedClass(): string
    {
        return 'App\\Model\\'.ucfirst($this->getSupportedName());
    }

    protected function getSupportedName(): string
    {
        $pos = strrpos(get_class($this), '\\');
        $pos = false === $pos ? 0 : $pos + 1;

        return lcfirst(substr(get_class($this), $pos, -5));
    }

    protected function getSupportedMethods(): array
    {
        $methods = [];
        foreach (get_class_methods($this) as $method) {
            if ('can' !== substr($method, 0, 3)) {
                continue;
            }

            $name = lcfirst(substr($method, 3));
            $attribute = '';

            $len = strlen($name);
            for ($i = 0; $i < $len; ++$i) {
                if (ctype_upper($name[$i])) {
                    $attribute .= '_'.strtolower($name[$i]);
                } else {
                    $attribute .= $name[$i];
                }
            }

            $methods[$attribute] = $method;
        }

        return $methods;
    }

    protected function isGranted(TokenInterface $token, $attributes, $subject = null): bool
    {
        return $this->decisionManager->decide($token, (array) $attributes, $subject);
    }

    protected function isGrantedAny(TokenInterface $token, array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->decisionManager->decide($token, [$role])) {
                return true;
            }
        }

        return false;
    }
}
