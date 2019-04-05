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

use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Yakamara\CommonBundle\DependencyInjection\ServiceLocatorAwareTrait;
use Yakamara\CommonBundle\Util\DateTimeUtil;
use Yakamara\CommonBundle\Util\FormatUtil;
use Yakamara\DateTime\AbstractDateTime;

class DateTimeExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    use ServiceLocatorAwareTrait;

    public static function getSubscribedServices(): array
    {
        return [
            '?'.DateTimeUtil::class,
            '?'.FormatUtil::class,
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('descriptive_date', [$this, 'descriptiveDate'], [
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

        $descriptiveDate = $this->container->get(DateTimeUtil::class)->descriptiveDateTime($datetime, $descriptive);

        if ($descriptive) {
            $format = $this->container->get(FormatUtil::class);

            $descriptiveDate = '<span data-toggle="tooltip" title="'.$format->date($datetime).'&nbsp;'.$format->time($datetime).'">'.$descriptiveDate.'</span>';
        }

        return $descriptiveDate;
    }
}
