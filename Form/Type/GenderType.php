<?php

namespace Yakamara\CommonBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenderType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [
                'label.gender.f' => 'f',
                'label.gender.m' => 'm',
            ],
            'choices_as_values' => true,
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
