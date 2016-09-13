<?php declare(strict_types=1);

/*
 * This file is part of the common-bundle package.
 *
 * (c) Yakamara Media GmbH & Co. KG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yakamara\CommonBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class UniqueProperty extends Constraint
{
    public $message = 'This value is already used.';
}
