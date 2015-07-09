<?php

namespace Yakamara\CommonBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class ModelExtension extends AbstractTypeExtension
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choice_translation_domain' => false,
            'empty_value' => function (Options $options) {
                if (!$options['multiple'] && !$options['expanded']) {
                    return $this->translator->trans('select.empty_value');
                }
                return false;
            },
            'data_class' => function (Options $options) {
                return $options['expanded'] ? $options['class'] : null;
            },
        ]);
    }

    public function getExtendedType()
    {
        return 'model';
    }
}
