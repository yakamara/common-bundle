<?php

namespace Yakamara\CommonBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class LabelExtension extends AbstractTypeExtension
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (null === $view->vars['label']) {
            $label = $view->vars['name'];
            $label = preg_replace('/[A-Z]/', '_$0', $label);
            $label = strtolower($label);
            $view->vars['label'] = 'label.'.$label;
        }
    }

    public function getExtendedType()
    {
        return FormType::class;
    }
}
