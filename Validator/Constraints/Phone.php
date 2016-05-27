<?php

namespace Yakamara\CommonBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class Phone extends Constraint
{
    public $message = 'This value is not a valid phone number.';
}
