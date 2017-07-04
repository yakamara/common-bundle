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

    public function getFilters(): array
    {
        return [
            new \Twig_Filter('number', [$this, 'number']),
            new \Twig_Filter('decimal', [$this, 'decimal']),
            new \Twig_Filter('percent', [$this, 'percent']),
            new \Twig_Filter('currency', [$this, 'currency']),
            new \Twig_Filter('date', [$this, 'date']),
            new \Twig_Filter('time', [$this, 'time']),
            new \Twig_Filter('datetime', [$this, 'datetime']),
            new \Twig_Filter('datetimeRange', [$this, 'datetimeRange']),
            new \Twig_Filter('dateRange', [$this, 'dateRange']),
            new \Twig_Filter('bytes', [$this, 'bytes']),
            new \Twig_Filter('break_on_slash', [$this, 'breakOnSlash'], ['pre_escape' => 'html', 'is_safe' => ['html']]),
            new \Twig_Filter('country', [$this, 'country']),
            new \Twig_Filter('locale', [$this, 'locale']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new \Twig_Function('icon', [$this, 'icon'], [
                'pre_escape' => 'html',
                'is_safe' => ['html'],
            ]),
            new \Twig_Function('email', [$this, 'email'], [
                'pre_escape' => 'html',
                'is_safe' => ['html'],
            ]),
            new \Twig_Function('address', [$this, 'address'], [
                'is_safe' => ['html'],
            ]),
            new \Twig_Function('gender', [$this, 'gender']),
            new \Twig_Function('gender_name', [$this, 'genderName']),
            new \Twig_Function('iban', [$this, 'iban'], [
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

    public function datetimeRange($range, ?string $format = null, ?string $timeFormat = null): ?string
    {
        return $this->format->datetimeRange($range, $format, $timeFormat);
    }

    public function dateRange($range, ?string $format = null): ?string
    {
        return $this->format->dateRange($range, $format);
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

        /* @noinspection PhpUndefinedMethodInspection */
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

    public function getName(): string
    {
        return 'yakamara_format_extension';
    }
}
