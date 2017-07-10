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

use Yakamara\CommonBundle\Util\DateTimeUtil;
use Yakamara\CommonBundle\Util\FormatUtil;
use Yakamara\DateTime\AbstractDateTime;

class DateTimeExtension extends \Twig_Extension
{
    private $dateTimeUtil;
    private $format;

    public function __construct(DateTimeUtil $dateTimeUtil, FormatUtil $format)
    {
        $this->dateTimeUtil = $dateTimeUtil;
        $this->format = $format;
    }

    public function getFunctions(): array
    {
        return [
            new \Twig_Function('descriptive_date', [$this, 'descriptiveDate'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function descriptiveDate($datetime): ?string
    {
        if (!$datetime) {
            return null;
        }

        $datetime = AbstractDateTime::createFromUnknown($datetime);

        $descriptiveDate = $this->dateTimeUtil->descriptiveDateTime($datetime, $descriptive);

        if ($descriptive) {
            $descriptiveDate = '<span data-toggle="tooltip" title="'.$this->format->date($datetime).'&nbsp;'.$this->format->time($datetime).'">'.$descriptiveDate.'</span>';
        }

        return $descriptiveDate;
    }
}
