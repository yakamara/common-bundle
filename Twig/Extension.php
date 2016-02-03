<?php

namespace Yakamara\CommonBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Intl\Intl;

class Extension extends \Twig_Extension
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('number', [$this, 'number']),
            new \Twig_SimpleFilter('percent', [$this, 'percent'], [
                'is_safe' => ['html'],
            ]),
            new \Twig_SimpleFilter('currency', [$this, 'currency'], [
                'is_safe' => ['html'],
            ]),
            new \Twig_SimpleFilter('break_on_slash', function ($text) {
                return str_replace('/', '/&#8203;', $text);
            }, ['pre_escape' => 'html', 'is_safe' => ['html']]),
            new \Twig_SimpleFilter('country', function ($country, $displayLocale = null) {
                if ($country) {
                    return Intl::getRegionBundle()->getCountryName($country, $displayLocale);
                }
            }),
        ];
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('switched_user', function () {
                return $this->container->get('yakamara_common.security_context')->isUserSwitched();
            }),
            new \Twig_SimpleFunction('switched_user_source', function () {
                return $this->container->get('yakamara_common.security_context')->getSwitchedUserSource();
            }),
            new \Twig_SimpleFunction('current_url', [$this, 'currentUrl']),
            new \Twig_SimpleFunction('descriptive_date', [$this, 'descriptiveDate'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]),
            new \Twig_SimpleFunction('icon', [$this, 'icon'], [
                'pre_escape' => 'html',
                'is_safe' => ['html'],
            ]),
            new \Twig_SimpleFunction('email', [$this, 'email'], [
                'pre_escape' => 'html',
                'is_safe' => ['html'],
            ]),
            new \Twig_SimpleFunction('address', [$this, 'address'], [
                'is_safe' => ['html'],
            ]),
            new \Twig_SimpleFunction('gender', [$this, 'gender']),
            new \Twig_SimpleFunction('gender_name', [$this, 'genderName']),
            new \Twig_SimpleFunction('iban', [$this, 'iban'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function number($number, $decimals = 2, $decimalPoint = null, $thousandSep = null)
    {
        return $this->container->get('yakamara_common.format')->number($number, $decimals, $decimalPoint, $thousandSep);
    }

    public function percent($number, $decimals = 2, $html = true)
    {
        return $this->container->get('yakamara_common.format')->percent($number, $decimals, $html);
    }

    public function currency($number, $decimals = 2, $currency = '€', $html = true)
    {
        return $this->container->get('yakamara_common.format')->currency($number, $decimals, $currency, $html);
    }

    public function currentUrl(array $parameters, $removePattern = null)
    {
        $request = $this->container->get('request_stack')->getMasterRequest();

        $parameters = array_merge(
            $request->query->all(),
            $parameters
        );

        $parameters = array_filter($parameters, function ($value, $key) use ($removePattern) {
            if ('' === (string) $value) {
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

    public function descriptiveDate(\Twig_Environment $env, $datetime)
    {
        if (!$datetime) {
            return '';
        }
        $datetime = \twig_date_converter($env, $datetime);
        $descriptiveDate = $this->container->get('yakamara_common.datetime')->descriptiveDateTime($datetime, $descriptive);
        if ($descriptive) {
            $descriptiveDate = '<span data-toggle="tooltip" title="' . $datetime->format('d.m.Y') . '&nbsp;' . $datetime->format('H:i') . '">' . $descriptiveDate . '</span>';
        }
        return $descriptiveDate;
    }

    public function icon($icon)
    {
        return '<i class="fa fa-'.$icon.'"></i>';
    }

    public function email($email)
    {
        if (!$email) {
            return '';
        }
        return '<a href="mailto:'.$email.'">'.$email.'</a>';
    }

    public function address($address)
    {
        if (!$address) {
            return '';
        }
        return $address->getStreet().'<br>'.$address->getZip().' '.$address->getCity();
    }

    public function gender($person)
    {
        return $this->container->get('yakamara_common.format')->gender($person);
    }

    public function genderName($person)
    {
        return $this->container->get('yakamara_common.format')->genderName($person);
    }

    public function iban($iban)
    {
        if (!$iban) {
            return null;
        }

        $iban = implode(array_map(function ($part) {
            return '<span>'.$part.'</span>';
        }, str_split($iban, 4)));

        return '<span class="iban">'.$iban.'</span>';
    }

    public function getName()
    {
        return 'yakamara_extension';
    }
}
