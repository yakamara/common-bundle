<?php

namespace Yakamara\CommonBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Intl\Intl;
use Yakamara\CommonBundle\Util\DateTimeUtil;

class DateTimeExtension extends \Twig_Extension
{
    protected $dateTimeUtil;

    public function __construct(DateTimeUtil $dateTimeUtil)
    {
        $this->dateTimeUtil = $dateTimeUtil;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('descriptive_date', [$this, 'descriptiveDate'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function descriptiveDate(\Twig_Environment $env, $datetime)
    {
        if (!$datetime) {
            return '';
        }
        $datetime = \twig_date_converter($env, $datetime);
        $descriptiveDate = $this->dateTimeUtil->descriptiveDateTime($datetime, $descriptive);
        if ($descriptive) {
            $descriptiveDate = '<span data-toggle="tooltip" title="' . $datetime->format('d.m.Y') . '&nbsp;' . $datetime->format('H:i') . '">' . $descriptiveDate . '</span>';
        }
        return $descriptiveDate;
    }

    public function getName()
    {
        return 'yakamara_datetime_extension';
    }
}
