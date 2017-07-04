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
use Yakamara\DateTime\Date;
use Yakamara\DateTime\DateTime;
use Yakamara\DateTime\DateTimeInterface;

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

    public function descriptiveDateTime(DateTimeInterface $dateTime, ?bool &$descriptive = null): ?string
    {
        $diff = $dateTime->diff(new DateTime());

        if ($diff->days > 1 || $diff->days == 1 && $dateTime->getDay() !== Date::yesterday()->getDay()) {
            $descriptive = false;

            return $this->format->datetime($dateTime);
        }

        $descriptive = true;

        if ($diff->days || $diff->h > 5) {
            $day = $dateTime->getDay();
            if ($day === Date::yesterday()->getDay()) {
                return $this->translator->trans('descriptive_datetime.yesterday', ['%time%' => $this->format->time($dateTime)]);
            }
            if ($day === Date::today()->getDay()) {
                return $this->translator->trans('descriptive_datetime.today', ['%time%' => $this->format->time($dateTime)]);
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

    public function descriptiveRange(DateTimeInterface $start, DateTimeInterface $end): ?string
    {
        $range = $this->format->date($start).' – '.$this->format->date($end);

        if (1 !== $start->getDay()) {
            return $range;
        }

        $end2 = $end->addDays(1);

        if (1 !== $end2->getDay()) {
            return $range;
        }

        if ($start->format('Y-m') !== $end->format('Y-m')) {
            return $range;
        }

        return $this->format->date($start, '%B %Y');
    }
}
