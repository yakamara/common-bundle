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

use Symfony\Component\DependencyInjection\ServiceSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Yakamara\CommonBundle\DependencyInjection\ServiceLocatorAwareTrait;

class UrlExtension extends \Twig_Extension implements ServiceSubscriberInterface
{
    use ServiceLocatorAwareTrait;

    public static function getSubscribedServices(): array
    {
        return [
            '?'.RequestStack::class,
            '?'.RouterInterface::class,
        ];
    }

    public function getFilters(): array
    {
        return [
            new \Twig_Filter('url_decode', [$this, 'urlDecode']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new \Twig_Function('current_url', [$this, 'currentUrl']),
        ];
    }

    public function urlDecode($url): string
    {
        return rawurldecode((string) $url);
    }

    public function currentUrl(array $parameters = [], ?string $removePattern = null): string
    {
        $request = $this->container->get(RequestStack::class)->getMasterRequest();

        $parameters = array_merge(
            $request->attributes->get('_route_params'),
            $request->query->all(),
            $parameters
        );

        $parameters = array_filter($parameters, function ($value, $key) use ($removePattern) {
            if (!is_array($value) && '' === (string) $value) {
                return false;
            }
            if ('_pjax' === $key || '_ajax' === $key) {
                return false;
            }
            if (1 == $value && ('page' === $key || '-page' === substr($key, -5))) {
                return false;
            }
            if ($removePattern && preg_match($removePattern, $key)) {
                return false;
            }

            return true;
        }, ARRAY_FILTER_USE_BOTH);

        $router = $this->container->get(RouterInterface::class);

        return $router->generate($request->attributes->get('_route'), $parameters);
    }
}
