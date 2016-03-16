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

    public function descriptiveDateTime(\DateTimeInterface $timestamp, &$descriptive = null)
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
            if ($day == (new \DateTime('today'))->format('d')) {
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

    public static function descriptiveRange(\DateTimeInterface $start, \DateTimeInterface $end)
    {
        $range = $start->format('d.m.Y') .' – '.$end->format('d.m.Y');
        if (1 != $start->format('j')) {
            return $range;
        }
        $end2 = clone $end;
        $end2->modify('+1day');
        if (1 != $end2->format('j')) {
            return $range;
        }
        if ($start->format('m.Y') != $end->format('m.Y')) {
            return $range;
        }
        return strftime('%B %Y', $start->getTimestamp());
    }

    public function addWeekdays(\DateTimeInterface $date, $days)
    {
        $interval = new \DateInterval('P1D');
        $method = $days < 0 ? 'sub' : 'add';
        $days = abs($days);
        for ($i = 0; $i < $days; ++$i) {
            do {
                $date = $date->$method($interval);
            } while (!$this->isWeekday($date));
        }

        return $date;
    }

    public function isWeekday(\DateTimeInterface $date)
    {
        $day = $date->format('w');
        if (0 == $day || 6 == $day) {
            return false;
        }

        if (in_array($date->getTimestamp(), self::holidays($date->format('Y')))) {
            return false;
        }

        return true;
    }

    public function diffWeekdays(\DateTimeInterface $date1, \DateTimeInterface $date2)
    {
        if ($date1 < $date2) {
            return -$this->diffWeekdays($date2, $date1);
        }

        $date1 = new \DateTime($date1->format('Y-m-d').' 00:00:00');
        $date2 = new \DateTime($date2->format('Y-m-d').' 00:00:00');
        $i = 0;
        while ($date2 < $date1) {
            $this->addWeekdays($date2, 1);
            ++$i;
        }
        return $i;
    }

    private static function holidays($year)
    {
        static $holidays = [];

        if (!isset($holidays[$year])) {
            $holidays[$year] = [
                mktime(0, 0, 0, 1, 1, $year), // Neujahr
                mktime(0, 0, 0, 5, 1, $year), // Tag der Arbeit
                mktime(0, 0, 0, 10, 3, $year), // Tag der Deutschen Einheit
                mktime(0, 0, 0, 12, 25, $year), // 1. Weihnachtstag
                mktime(0, 0, 0, 12, 26, $year), // 2. Weihnachtstag
            ];

            $easter = self::easter($year);
            $holidays[$year][] = strtotime('-2 days', $easter); // Karfreitag
            $holidays[$year][] = strtotime('+1 days', $easter); // Ostermontag
            $holidays[$year][] = strtotime('+39 days', $easter); // Christi Himmelfahrt
            $holidays[$year][] = strtotime('+50 days', $easter); // Pfingstmontag
        }
        return $holidays[$year];
    }

    private static function easter($year)
    {
        $G = $year % 19;
        $C = (int) ($year / 100);
        $H = (int) ($C - (int) ($C / 4) - (int) ((8 * $C + 13) / 25) + 19 * $G + 15) % 30;
        $I = (int) $H - (int) ($H / 28) * (1 - (int) ($H / 28) * (int) (29 / ($H + 1)) * ((int) (21 - $G) / 11));
        $J = ($year + (int) ($year / 4) + $I + 2 - $C + (int) ($C / 4)) % 7;
        $L = $I - $J;
        $m = 3 + (int) (($L + 40) / 44);
        $d = $L + 28 - 31 * ((int) ($m / 4));
        $y = $year;
        $E = mktime(0, 0, 0, $m, $d, $y);

        return $E;
    }
}
