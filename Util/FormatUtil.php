<?php declare(strict_types=1);

/*
 * This file is part of the common-bundle package.
 *
 * (c) Yakamara Media GmbH & Co. KG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yakamara\CommonBundle\Util;

use Symfony\Component\Translation\TranslatorInterface;
use Yakamara\DateTime\DateTimeInterface;

class FormatUtil
{
    const INTL_DATE_FORMAT = [
        'none' => \IntlDateFormatter::NONE,
        'full' => \IntlDateFormatter::FULL,
        'long' => \IntlDateFormatter::LONG,
        'medium' => \IntlDateFormatter::MEDIUM,
        'short' => \IntlDateFormatter::SHORT,
    ];

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function number($number, $decimals = 2)
    {
        if (null === $number) {
            return null;
        }

        $formatter = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::DECIMAL);
        $formatter->setSymbol(\NumberFormatter::MINUS_SIGN_SYMBOL, '−');
        $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $decimals);

        return $formatter->format((float) $number);
    }

    public function decimal($number, $decimals = 2)
    {
        if (null === $number) {
            return null;
        }

        return number_format((float) $number, $decimals, '.', '');
    }

    public function percent($number, $decimals = 2)
    {
        if (null === $number) {
            return null;
        }

        $formatter = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::PERCENT);
        $formatter->setSymbol(\NumberFormatter::MINUS_SIGN_SYMBOL, '−');
        $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $decimals);

        return $formatter->format((float) $number);
    }

    public function currency($number, $decimals = 2, $currency = 'EUR')
    {
        if (null === $number) {
            return null;
        }

        $formatter = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::CURRENCY);
        $formatter->setSymbol(\NumberFormatter::MINUS_SIGN_SYMBOL, '−');
        $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $decimals);

        return $formatter->formatCurrency((float) $number, $currency);
    }

    public function date(DateTimeInterface $date = null, $format = null)
    {
        if (null === $format) {
            $format = '%x';
        }

        return $this->datetime($date, $format, 'none');
    }

    public function time(DateTimeInterface $time = null, $format = null)
    {
        if (null === $format) {
            $format = '%H:%M';
        }

        if (array_key_exists($format, self::INTL_DATE_FORMAT)) {
            return $this->datetime($time, 'none', $format);
        }

        return $this->datetime($time, $format);
    }

    public function datetime(DateTimeInterface $datetime = null, $format = null, $timeFormat = null)
    {
        if (null === $datetime) {
            return null;
        }

        if (null === $format) {
            $format = '%x, %H:%M';
        }

        if (!array_key_exists($format, self::INTL_DATE_FORMAT)) {
            return $datetime->formatLocalized($format);
        }

        return $datetime->formatIntl(self::INTL_DATE_FORMAT[$format], self::INTL_DATE_FORMAT[$timeFormat] ?? null);
    }

    public function bytes(int $bytes)
    {
        $unit = 'B';

        if ($bytes >= 1000) {
            $bytes /= 1000;
            $unit = 'kB';
        }
        if ($bytes >= 1000) {
            $bytes /= 1000;
            $unit = 'MB';
        }
        if ($bytes >= 1000) {
            $bytes /= 1000;
            $unit = 'GB';
        }
        if ($bytes >= 1000) {
            $bytes /= 1000;
            $unit = 'TB';
        }

        if ($bytes >= 100) {
            return sprintf('%d %s', $bytes, $unit);
        }

        $decimals = $bytes >= 10 ? 1 : 2;

        $formatter = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::DECIMAL);
        $formatter->setSymbol(\NumberFormatter::MINUS_SIGN_SYMBOL, '−');
        $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $decimals);

        return $formatter->format($bytes).' '.$unit;
    }

    public function gender($person)
    {
        if (null === $person) {
            return null;
        }

        /* @noinspection PhpUndefinedMethodInspection */
        $gender = $person->getGender();

        if (!$gender) {
            return null;
        }

        return $this->translator->trans('label.gender.'.$gender);
    }

    public function genderName($person)
    {
        if (null === $person) {
            return null;
        }

        /* @noinspection PhpUndefinedMethodInspection */
        return trim($this->gender($person).' '.$person->getReverseFullName());
    }
}
