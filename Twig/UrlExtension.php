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

class UrlExtension extends \Twig_Extension
{
    protected $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('url_decode', [$this, 'urlDecode']),
        ];
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('current_url', [$this, 'currentUrl']),
        ];
    }

    public function urlDecode($url)
    {
        return rawurldecode($url);
    }

    public function currentUrl(array $parameters = [], $removePattern = null)
    {
        $request = $this->requestStack->getMasterRequest();

        $parameters = array_merge(
            $request->query->all(),
            $parameters
        );

        $parameters = array_filter($parameters, function ($value, $key) use ($removePattern) {
            if (!is_array($value) && '' === (string) $value) {
                return false;
            }
            if ('_pjax' === $key) {
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

        $url = $request->getBaseUrl().$request->getPathInfo();
        if ($parameters) {
            $url .= '?'.http_build_query($parameters, '', '&');
        }

        return $url;
    }

    public function getName()
    {
        return 'yakamara_url_extension';
    }
}
