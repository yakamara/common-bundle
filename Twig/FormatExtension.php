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

use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Locales;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Yakamara\CommonBundle\DependencyInjection\ServiceLocatorAwareTrait;
use Yakamara\CommonBundle\Util\FormatUtil;
use Yakamara\DateTime\AbstractDateTime;

class FormatExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    use ServiceLocatorAwareTrait;

    public static function getSubscribedServices(): array
    {
        return [
            '?'.FormatUtil::class,
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('number', [$this, 'number']),
            new TwigFilter('decimal', [$this, 'decimal']),
            new TwigFilter('percent', [$this, 'percent']),
            new TwigFilter('currency', [$this, 'currency']),
            new TwigFilter('date', [$this, 'date']),
            new TwigFilter('time', [$this, 'time']),
            new TwigFilter('datetime', [$this, 'datetime']),
            new TwigFilter('datetimeRange', [$this, 'datetimeRange']),
            new TwigFilter('dateRange', [$this, 'dateRange']),
            new TwigFilter('bytes', [$this, 'bytes']),
            new TwigFilter('break_on_slash', [$this, 'breakOnSlash'], ['pre_escape' => 'html', 'is_safe' => ['html']]),
            new TwigFilter('country', [$this, 'country']),
            new TwigFilter('locale', [$this, 'locale']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('icon', [$this, 'icon'], [
                'pre_escape' => 'html',
                'is_safe' => ['html'],
            ]),
            new TwigFunction('email', [$this, 'email'], [
                'pre_escape' => 'html',
                'is_safe' => ['html'],
            ]),
            new TwigFunction('address', [$this, 'address'], [
                'is_safe' => ['html'],
            ]),
            new TwigFunction('gender', [$this, 'gender']),
            new TwigFunction('gender_name', [$this, 'genderName']),
            new TwigFunction('iban', [$this, 'iban'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function number($number, int $decimals = 2, ?int $maxDecimals = null): ?string
    {
        return $this->getFormat()->number($number, $decimals, $maxDecimals);
    }

    public function decimal($number, int $decimals = 2): ?string
    {
        return $this->getFormat()->decimal($number, $decimals);
    }

    public function percent($number, int $decimals = 2, ?int $maxDecimals = null): ?string
    {
        return $this->getFormat()->percent($number, $decimals, $maxDecimals);
    }

    public function currency($number, int $decimals = 2, string $currency = 'EUR'): ?string
    {
        return $this->getFormat()->currency($number, $decimals, $currency);
    }

    public function date($date, ?string $format = null): ?string
    {
        if (!$date) {
            return null;
        }

        $date = AbstractDateTime::createFromUnknown($date);

        return $this->getFormat()->date($date, $format);
    }

    public function time($time, ?string $format = null): ?string
    {
        if (!$time) {
            return null;
        }

        $time = AbstractDateTime::createFromUnknown($time);

        return $this->getFormat()->time($time, $format);
    }

    public function datetime($datetime, ?string $format = null, ?string $timeFormat = null): ?string
    {
        if (!$datetime) {
            return null;
        }

        $datetime = AbstractDateTime::createFromUnknown($datetime);

        return $this->getFormat()->datetime($datetime, $format, $timeFormat);
    }

    public function datetimeRange($range, ?string $format = null, ?string $timeFormat = null): ?string
    {
        return $this->getFormat()->datetimeRange($range, $format, $timeFormat);
    }

    public function dateRange($range, ?string $format = null): ?string
    {
        return $this->getFormat()->dateRange($range, $format);
    }

    public function bytes($bytes): ?string
    {
        if (null === $bytes) {
            return null;
        }

        return $this->getFormat()->bytes($bytes);
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

        return Countries::getName($country, $displayLocale);
    }

    public function locale($locale, ?string $displayLocale = null): ?string
    {
        if (!$locale) {
            return null;
        }

        return Locales::getName($locale, $displayLocale);
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
        return $this->getFormat()->gender($person);
    }

    public function genderName($person): ?string
    {
        return $this->getFormat()->genderName($person);
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

    private function getFormat(): FormatUtil
    {
        return $this->container->get(FormatUtil::class);
    }
}
