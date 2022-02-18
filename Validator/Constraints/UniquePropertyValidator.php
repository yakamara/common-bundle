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

use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Map\TableMap;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniquePropertyValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueProperty) {
            throw new UnexpectedTypeException($constraint, UniqueProperty::class);
        }

        if (null === $value) {
            return;
        }

        $object = $this->context->getRoot()->getData();
        $class = get_class($object);
        $queryClass = $class.'Query';
        $tableMapClass = $class::TABLE_MAP;

        if (!preg_match('/^children\[([^\]]*)\].data$/', $this->context->getPropertyPath(), $match)) {
            throw new ConstraintDefinitionException('Unsupported property');
        }

        $property = $match[1];

        $colname = $tableMapClass::translateFieldname($property, TableMap::TYPE_CAMELNAME, TableMap::TYPE_COLNAME);
        $property = $tableMapClass::translateFieldname($property, TableMap::TYPE_CAMELNAME, TableMap::TYPE_PHPNAME);

        if (!$object->isColumnModified($colname)) {
            return;
        }

        /** @var ObjectCollection $objects */
        $objects = $queryClass::create()->filterBy($property, $value)->find();

        if ($objects->isEmpty()) {
            return;
        }

        if (1 === $objects->count() && $object->equals($objects->getFirst())) {
            return;
        }

        $this->context->buildViolation($constraint->message)->addViolation();
    }
}
