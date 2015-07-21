<?php

namespace Yakamara\CommonBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateTimeExtension extends AbstractTypeExtension
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'date_widget' => 'single_text',
            'date_format' => 'dd.MM.yyyy',
            'time_widget' => 'single_text',
            'html5' => false,
        ]);
    }

    public function getExtendedType()
    {
        return 'date';
    }
}
