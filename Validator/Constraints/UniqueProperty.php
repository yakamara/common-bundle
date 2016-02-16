<?php

namespace Yakamara\CommonBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class UniqueProperty extends Constraint
{
    public $message = 'This value is already used.';
}
