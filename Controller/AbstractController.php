<?php declare(strict_types=1);

/*
 * This file is part of the common-bundle package.
 *
 * (c) Yakamara Media GmbH & Co. KG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yakamara\CommonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Yakamara\CommonBundle\Security\SecurityContext;

abstract class AbstractController extends SymfonyAbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'kernel' => '?'.KernelInterface::class,
            'translator' => '?'.TranslatorInterface::class,
            '?'.SecurityContext::class,
        ]);
    }

    /** @noinspection PhpUndefinedNamespaceInspection */
    /** @noinspection PhpUndefinedClassInspection */
    protected function getKernel(): \App\Kernel
    {
        return $this->container->get('kernel');
    }

    /**
     * Translates the given message.
     *
     * @param string      $id         #TranslationKey The message id (may also be an object that can be cast to string)
     * @param array       $parameters An array of parameters for the message
     * @param null|string $domain     #TranslationDomain The domain for the message or null to use the default
     * @param null|string $locale     The locale or null to use the default
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     *
     * @return string The translated string
     */
    protected function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string
    {
        return $this->container->get('translator')->trans($id, $parameters, $domain, $locale);
    }

    /**
     * Translates the given choice message by choosing a translation according to a number.
     *
     * @param string      $id         The #TranslationKey (may also be an object that can be cast to string)
     * @param int         $number     The number to use to find the indice of the message
     * @param array       $parameters An array of parameters for the message
     * @param null|string $domain     The #TranslationDomain for the message or null to use the default
     * @param null|string $locale     The locale or null to use the default
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     *
     * @return string The translated string
     */
    protected function transChoice(string $id, int $number, array $parameters = [], string $domain = null, string $locale = null): string
    {
        return $this->container->get('translator')->transChoice($id, $number, $parameters, $domain, $locale);
    }

    /** @noinspection PhpUndefinedNamespaceInspection */
    /** @noinspection PhpUndefinedClassInspection */

    /**
     * @return null|UserInterface|\App\Model\User
     */
    protected function getUser()
    {
        return $this->container->get(SecurityContext::class)->getUser();
    }

    protected function isUserSwitched(): bool
    {
        return $this->container->get(SecurityContext::class)->isUserSwitched();
    }
}
