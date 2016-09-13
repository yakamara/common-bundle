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

        return $formatter->format($number);
    }

    public function decimal($number, $decimals = 2)
    {
        if (null === $number) {
            return null;
        }

        return number_format($number, $decimals, '.', '');
    }

    public function percent($number, $decimals = 2)
    {
        if (null === $number) {
            return null;
        }

        $formatter = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::PERCENT);
        $formatter->setSymbol(\NumberFormatter::MINUS_SIGN_SYMBOL, '−');
        $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $decimals);

        return $formatter->format($number);
    }

    public function currency($number, $decimals = 2, $currency = 'EUR')
    {
        if (null === $number) {
            return null;
        }

        $formatter = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::CURRENCY);
        $formatter->setSymbol(\NumberFormatter::MINUS_SIGN_SYMBOL, '−');
        $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $decimals);

        return $formatter->formatCurrency($number, $currency);
    }

    public function date(DateTimeInterface $date = null, $format = null)
    {
        if (null === $format) {
            $format = '%x';
        }

        return $this->datetime($date, $format);
    }

    public function time(DateTimeInterface $time = null, $format = null)
    {
        if (null === $format) {
            $format = '%H:%M';
        }

        return $this->datetime($time, $format);
    }

    public function datetime(DateTimeInterface $datetime = null, $format = null)
    {
        if (null === $datetime) {
            return null;
        }
        if (null === $format) {
            $format = '%x %H:%M';
        }

        return $datetime->formatLocalized($format);
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
