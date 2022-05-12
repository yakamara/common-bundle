<?php declare(strict_types=1);

/*
 * This file is part of the common-bundle package.
 *
 * (c) Yakamara Media GmbH & Co. KG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yakamara\CommonBundle\Propel;

use Propel\Generator\Model\Column;
use Propel\Generator\Model\PropelTypes;
use Propel\Runtime\Map\ColumnMap;
use Yakamara\DateTime\Date;
use Yakamara\DateTime\DateTime;

class MysqlPlatform extends \Propel\Generator\Platform\MysqlPlatform
{
    public function getColumnDDL(Column $col)
    {
        if ($col->isEnumType() || $col->isSetType()) {
            $class = $col->getPhpType();
            $reflection = new \ReflectionEnum($class);
            $type = (string) $reflection->getBackingType();

            if ('string' === $type) {
                $cases = array_map(function (\BackedEnum $case) {
                    return "'$case->value'";
                }, $class::cases());

                $col->getDomain()->replaceSqlType(strtolower($col->getType()).'('.implode(',', $cases).')');
            }
        }

        return parent::getColumnDDL($col);
    }
}
