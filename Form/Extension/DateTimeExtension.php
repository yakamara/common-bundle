<?php declare(strict_types=1);

/*
 * This file is part of the common-bundle package.
 *
 * (c) Yakamara Media GmbH & Co. KG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yakamara\CommonBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateTimeExtension extends AbstractTypeExtension
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'date_widget' => 'single_text',
            'time_widget' => 'single_text',
            'html5' => false,
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [DateTimeType::class];
    }
}
