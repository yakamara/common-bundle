<?php declare(strict_types=1);

/*
 * This file is part of the common-bundle package.
 *
 * (c) Yakamara Media GmbH & Co. KG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yakamara\CommonBundle\Twig;

use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Yakamara\CommonBundle\DependencyInjection\ServiceLocatorAwareTrait;
use Yakamara\CommonBundle\Security\SecurityContext;

class SecurityExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    use ServiceLocatorAwareTrait;

    public static function getSubscribedServices(): array
    {
        return [
            '?'.SecurityContext::class,
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('switched_user', function () {
                return $this->container->get(SecurityContext::class)->isUserSwitched();
            }),
            new TwigFunction('switched_user_source', function () {
                return $this->container->get(SecurityContext::class)->getSwitchedUserSource();
            }),
        ];
    }
}
