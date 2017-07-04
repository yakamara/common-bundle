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
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class LabelExtension extends AbstractTypeExtension
{
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if (null === $view->vars['label']) {
            $label = $view->vars['name'];
            $label = preg_replace('/[A-Z]/', '_$0', $label);
            $label = strtolower($label);
            $view->vars['label'] = 'label.'.$label;
        }
    }

    public function getExtendedType(): string
    {
        return FormType::class;
    }
}
