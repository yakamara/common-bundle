<?php

namespace Yakamara\CommonBundle\Validator\Constraints;

use Propel\Runtime\Map\TableMap;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

class UniquePropertyValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $object = $this->context->getRoot()->getData();
        $class = get_class($object);
        $queryClass = $class . 'Query';
        $tableMapClass = $class::TABLE_MAP;

        if (!preg_match('/^children\[([^\]]*)\].data$/', $this->context->getPropertyPath(), $match)) {
            throw new ConstraintDefinitionException('Unsupported property');
        }

        $property = $match[1];

        $colname = $tableMapClass::translateFieldname($property, TableMap::TYPE_CAMELNAME, TableMap::TYPE_COLNAME);
        $property = $tableMapClass::translateFieldname($property, TableMap::TYPE_CAMELNAME, TableMap::TYPE_PHPNAME);

        if ($object->isColumnModified($colname) && $queryClass::create()->filterBy($property, $value)->exists()) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
