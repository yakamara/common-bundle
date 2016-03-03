<?php

namespace Yakamara\CommonBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Intl\Intl;
use Yakamara\CommonBundle\Util\FormatUtil;

class FormatExtension extends \Twig_Extension
{
    protected $format;

    public function __construct(FormatUtil $format)
    {
        $this->format = $format;
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
            new \Twig_SimpleFilter('date', [$this, 'date'], [
                'needs_environment' => true,
            ]),
            new \Twig_SimpleFilter('datetime', [$this, 'datetime'], [
                'needs_environment' => true,
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

    public function number($number, $decimals = 2, $decimalPoint = ',', $thousandSep = '.')
    {
        return $this->format->number($number, $decimals, $decimalPoint, $thousandSep);
    }

    public function percent($number, $decimals = 2, $html = true)
    {
        return $this->format->percent($number, $decimals, $html);
    }

    public function currency($number, $decimals = 2, $currency = '€', $html = true)
    {
        return $this->format->currency($number, $decimals, $currency, $html);
    }

    public function date(\Twig_Environment $env, $date, $format = 'd.m.Y')
    {
        if (!$date) {
            return null;
        }

        $date = \twig_date_converter($env, $date);

        return $this->format->date($date, $format);
    }

    public function datetime(\Twig_Environment $env, $datetime, $format = 'd.m.Y H:i')
    {
        if (!$datetime) {
            return null;
        }

        $datetime = \twig_date_converter($env, $datetime);

        return $this->format->datetime($datetime, $format);
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
        return $this->format->gender($person);
    }

    public function genderName($person)
    {
        return $this->format->genderName($person);
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
        return 'yakamara_format_extension';
    }
}
