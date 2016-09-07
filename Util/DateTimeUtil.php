<?php

namespace Yakamara\CommonBundle\Util;

use Symfony\Component\Translation\TranslatorInterface;

class DateTimeUtil
{
    /** @var TranslatorInterface */
    protected $translator;

    /** @var FormatUtil */
    protected $format;

    public function __construct(TranslatorInterface $translator, FormatUtil $format)
    {
        $this->translator = $translator;
        $this->format = $format;
    }

    public function descriptiveDateTime(\DateTimeInterface $timestamp, &$descriptive = null)
    {
        $diff = $timestamp->diff(new \DateTime());
        $yesterdayDay = (new \DateTime('yesterday'))->format('d');

        if ($diff->days > 1 || $diff->days == 1 && $timestamp->format('d') != $yesterdayDay) {
            $descriptive = false;
            return $this->format->datetime($timestamp);
        }

        $descriptive = true;

        if ($diff->days || $diff->h > 5) {
            $day = $timestamp->format('d');
            if ($day == $yesterdayDay) {
                return $this->translator->trans('descriptive_datetime.yesterday', ['%time%' => $this->format->time($timestamp)]);
            }
            if ($day == (new \DateTime('today'))->format('d')) {
                return $this->translator->trans('descriptive_datetime.today', ['%time%' => $this->format->time($timestamp)]);
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

    public function descriptiveRange(\DateTimeInterface $start, \DateTimeInterface $end)
    {
        $range = $this->format->date($start) .' – '.$this->format->date($end);

        if (1 != $start->format('j')) {
            return $range;
        }

        $end2 = clone $end;
        $end2->modify('+1day');

        if (1 != $end2->format('j')) {
            return $range;
        }

        if ($start->format('Y-m') != $end->format('Y-m')) {
            return $range;
        }

        return $this->format->date($start, '%B %Y');
    }
}
