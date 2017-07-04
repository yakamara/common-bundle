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

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class UrlExtension extends \Twig_Extension
{
    private $requestStack;
    private $router;

    public function __construct(RequestStack $requestStack, RouterInterface $router)
    {
        $this->requestStack = $requestStack;
        $this->router = $router;
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
        $request = $this->requestStack->getMasterRequest();

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

        return $this->router->generate($request->attributes->get('_route'), $parameters);
    }

    public function getName(): string
    {
        return 'yakamara_url_extension';
    }
}
