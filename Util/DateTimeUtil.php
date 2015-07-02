<?php

namespace Yakamara\CommonBundle\Util;

use Symfony\Component\Translation\TranslatorInterface;

class DateTimeUtil
{
    /** @var TranslatorInterface */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function descriptiveDateTime(\DateTime $timestamp, &$descriptive = null)
    {
        $diff = $timestamp->diff(new \DateTime());
        $yesterdayDay = (new \DateTime('yesterday'))->format('d');

        if ($diff->days > 1 || $diff->days == 1 && $timestamp->format('d') != $yesterdayDay) {
            $descriptive = false;
            return $timestamp->format('d.m.Y H:i');
        }

        $descriptive = true;
        if ($diff->days || $diff->h > 5) {
            $day = $timestamp->format('d');
            if ($day == $yesterdayDay) {
                return $this->translator->trans('descriptive_datetime.yesterday', ['%time%' => $timestamp->format('H:i')]);
            }
            if ($day == $this->today()->format('d')) {
                return $this->translator->trans('descriptive_datetime.today', ['%time%' => $timestamp->format('H:i')]);
            }
            return null;
        }
        if ($diff->h) {
            return $this->translator->transChoice('descriptive_datetime.diff.hour', $diff->h, ['%count%' => $diff->h]);
        }
        if ($diff->i) {
            return $this->translator->transChoice('descriptive_datetime.diff.minute', $diff->i, ['%count%' => $diff->i]);
        }
        return $this->translator->trans('descriptive_datetime.justNow');
    }

    public function today()
    {
        return new \DateTime('today');
    }
}
