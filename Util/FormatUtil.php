<?php

namespace Yakamara\CommonBundle\Util;

use Symfony\Component\Translation\TranslatorInterface;

class FormatUtil
{
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function number($number, $decimals = 2, $decimalPoint = ',', $thousandSep = '.')
    {
        return str_replace('-', '−', number_format($number, $decimals, $decimalPoint, $thousandSep));
    }

    public function percent($number, $decimal = 2, $html = false)
    {
        if (null === $number) {
            return null;
        }

        return $this->number($number * 100, $decimal) . ($html ? '&nbsp;' : ' ') . '%';
    }

    public function currency($number, $decimal = 2, $currency = '€', $html = false)
    {
        if (null === $number) {
            return null;
        }

        return $this->number($number, $decimal) . ($html ? '&nbsp;' : ' ') . $currency;
    }

    public function date(\DateTimeInterface $date, $format = 'd.m.Y')
    {
        return $date->format($format);
    }

    public function datetime(\DateTimeInterface $datetime, $format = 'd.m.Y H:i')
    {
        return $datetime->format($format);
    }

    public function gender($person)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $gender = $person->getGender();

        if (!$gender) {
            return null;
        }

        return $this->translator->trans('label.gender.'.$gender);
    }

    public function genderName($person)
    {
        return trim($this->gender($person).' '.$person);
    }
}
