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

use Symfony\Component\Intl\Intl;
use Yakamara\CommonBundle\Util\FormatUtil;
use Yakamara\DateTime\AbstractDateTime;

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
            new \Twig_SimpleFilter('decimal', [$this, 'decimal']),
            new \Twig_SimpleFilter('percent', [$this, 'percent']),
            new \Twig_SimpleFilter('currency', [$this, 'currency']),
            new \Twig_SimpleFilter('date', [$this, 'date']),
            new \Twig_SimpleFilter('time', [$this, 'time']),
            new \Twig_SimpleFilter('datetime', [$this, 'datetime']),
            new \Twig_SimpleFilter('bytes', [$this, 'bytes']),
            new \Twig_SimpleFilter('break_on_slash', [$this, 'breakOnSlash'], ['pre_escape' => 'html', 'is_safe' => ['html']]),
            new \Twig_SimpleFilter('country', [$this, 'country']),
            new \Twig_SimpleFilter('locale', [$this, 'locale']),
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

    public function number($number, int $decimals = 2): ?string
    {
        return $this->format->number($number, $decimals);
    }

    public function decimal($number, int $decimals = 2): ?string
    {
        return $this->format->decimal($number, $decimals);
    }

    public function percent($number, int $decimals = 2): ?string
    {
        return $this->format->percent($number, $decimals);
    }

    public function currency($number, int $decimals = 2, string $currency = 'EUR'): ?string
    {
        return $this->format->currency($number, $decimals, $currency);
    }

    public function date($date, ?string $format = null): ?string
    {
        if (!$date) {
            return null;
        }

        $date = AbstractDateTime::createFromUnknown($date);

        return $this->format->date($date, $format);
    }

    public function time($time, ?string $format = null): ?string
    {
        if (!$time) {
            return null;
        }

        $time = AbstractDateTime::createFromUnknown($time);

        return $this->format->time($time, $format);
    }

    public function datetime($datetime, ?string $format = null, ?string $timeFormat = null): ?string
    {
        if (!$datetime) {
            return null;
        }

        $datetime = AbstractDateTime::createFromUnknown($datetime);

        return $this->format->datetime($datetime, $format, $timeFormat);
    }

    public function bytes($bytes): ?string
    {
        if (null === $bytes) {
            return null;
        }

        return $this->format->bytes($bytes);
    }

    public function breakOnSlash($text): string
    {
        if (!$text) {
            return $text;
        }

        return str_replace('/', '/&#8203;', $text);
    }

    public function country($country, ?string $displayLocale = null): ?string
    {
        if (!$country) {
            return null;
        }

        return Intl::getRegionBundle()->getCountryName($country, $displayLocale);
    }

    public function locale($locale, ?string $displayLocale = null): ?string
    {
        if (!$locale) {
            return null;
        }

        return Intl::getLocaleBundle()->getLocaleName($locale, $displayLocale);
    }

    public function icon(string $icon): string
    {
        return '<i class="fa fa-'.$icon.'"></i>';
    }

    public function email($email): ?string
    {
        if (!$email) {
            return '';
        }
        return '<a href="mailto:'.$email.'">'.$email.'</a>';
    }

    public function address($address): ?string
    {
        if (!$address) {
            return null;
        }
        return $address->getStreet().'<br>'.$address->getZip().' '.$address->getCity();
    }

    public function gender($person): ?string
    {
        return $this->format->gender($person);
    }

    public function genderName($person): ?string
    {
        return $this->format->genderName($person);
    }

    public function iban($iban): ?string
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
