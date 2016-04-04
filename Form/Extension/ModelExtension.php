<?php

namespace Yakamara\CommonBundle\Form\Extension;

use Propel\Bundle\PropelBundle\Form\Type\ModelType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModelExtension extends AbstractTypeExtension
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choice_translation_domain' => false,
        ]);
    }

    public function getExtendedType()
    {
        return ModelType::class;
    }
}
