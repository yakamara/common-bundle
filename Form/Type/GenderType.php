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
                'f' => 'label.gender.f',
                'm' => 'label.gender.m',
            ],
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
