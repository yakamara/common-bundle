<?php

namespace Yakamara\CommonBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class ModelExtension extends AbstractTypeExtension
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choice_translation_domain' => false,
            'empty_value' => function (Options $options) {
                if (!$options['multiple'] && !$options['expanded']) {
                    return 'select.empty_value';
                }
                return false;
            },
        ]);
    }

    public function getExtendedType()
    {
        return 'model';
    }
}
