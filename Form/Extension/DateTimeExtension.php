<?php

namespace Yakamara\CommonBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateTimeExtension extends AbstractTypeExtension
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'date_widget' => 'single_text',
            'time_widget' => 'single_text',
            'html5' => false,
        ]);
    }

    public function getExtendedType()
    {
        return DateTimeType::class;
    }
}
