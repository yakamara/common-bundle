<?php

namespace Yakamara\CommonBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Intl\Intl;

class UrlExtension extends \Twig_Extension
{
    protected $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('current_url', [$this, 'currentUrl']),
        ];
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
            $url .= '?'.http_build_query($parameters, null, '&');
        }

        return $url;
    }

    public function getName()
    {
        return 'yakamara_url_extension';
    }
}
