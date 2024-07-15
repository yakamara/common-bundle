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
use Propel\Generator\Model\IdMethod;
use Propel\Generator\Model\PropelTypes;
use Propel\Generator\Platform\MysqlPlatform;
use Propel\Generator\Platform\OraclePlatform;
use Propel\Generator\Platform\PlatformInterface;

class ObjectBuilder extends \Propel\Generator\Builder\Om\ObjectBuilder
{
    protected function addHydrateBody(string &$script): void
    {
        $table = $this->getTable();
        $platform = $this->getPlatform();

        $tableMap = $this->getTableMapClassName();

        $script .= '
        try {';
        $n = 0;
        foreach ($table->getColumns() as $col) {
            if (!$col->isLazyLoad()) {
                $indexName = "TableMap::TYPE_NUM == \$indexType ? $n + \$startcol : $tableMap::translateFieldName('{$col->getPhpName()}', TableMap::TYPE_PHPNAME, \$indexType)";

                $script .= "

            \$col = \$row[$indexName];";
                $clo = $col->getLowercasedName();
                if ($col->getType() === PropelTypes::CLOB_EMU && $this->getPlatform() instanceof OraclePlatform) {
                    // PDO_OCI returns a stream for CLOB objects, while other PDO adapters return a string...
                    $script .= "
            \$this->$clo = stream_get_contents(\$col);";
                } elseif ($col->isLobType() && !$platform->hasStreamBlobImpl()) {
                    $script .= "
            if (null !== \$col) {
                \$this->$clo = fopen('php://memory', 'r+');
                fwrite(\$this->$clo, \$col);
                rewind(\$this->$clo);
            } else {
                \$this->$clo = null;
            }";
                } elseif ($col->isTemporalType()) {
                    $handleMysqlDate = false;
                    if ($this->getPlatform() instanceof MysqlPlatform) {
                        if (in_array($col->getType(), [PropelTypes::TIMESTAMP, PropelTypes::DATETIME], true)) {
                            $handleMysqlDate = true;
                            $mysqlInvalidDateString = '0000-00-00 00:00:00';
                        } elseif ($col->getType() === PropelTypes::DATE) {
                            $handleMysqlDate = true;
                            $mysqlInvalidDateString = '0000-00-00';
                        }
                        // 00:00:00 is a valid time, so no need to check for that.
                    }
                    if ($handleMysqlDate) {
                        $script .= "
            if (\$col === '$mysqlInvalidDateString') {
                \$col = null;
            }";
                    }
                    $script .= "
            if (null === \$col) {
                \$this->$clo = null;
            } else {";
                    if ($col->getType() === PropelTypes::DATE) {
                        $script .= "
                \$this->$clo = {$this->createDateFromDb('$col')};";
                    } elseif ($col->getType() === PropelTypes::TIME) {
                        $script .= "
                \$this->$clo = {$this->createTimeFromDb('$col')};";
                    } else {
                        $script .= "
                \$this->$clo = {$this->createDateTimeFromDb('$col')};";
                    }
                    $script .= '
            }';
                } elseif ($col->isEnumType()) {
                    $enum = $this->declareClass($col->getPhpType());
                    $script .= "
            \$this->$clo = (null !== \$col) ? $enum::from(\$col) : null;";
                } elseif ($col->isPhpPrimitiveType()) {
                    $script .= "
            \$this->$clo = (null !== \$col) ? (".$col->getPhpType().') $col : null;';
                } elseif ($col->getType() === PropelTypes::OBJECT) {
                    $script .= "
            \$this->$clo = \$col;";
                } elseif ($col->getType() === PropelTypes::PHP_ARRAY) {
                    $cloUnserialized = $clo.'_unserialized';
                    $script .= "
            \$this->$clo = \$col;
            \$this->$cloUnserialized = null;";
                } elseif ($col->isSetType()) {
                    $cloConverted = $clo.'_converted';
                    $script .= "
            \$this->$clo = \$col;
            \$this->$cloConverted = null;";
                } elseif ($col->isPhpObjectType()) {
                    $script .= "
            \$this->$clo = (null !== \$col) ? new ".$col->getPhpType().'($col) : null;';
                } else {
                    $script .= "
            \$this->$clo = \$col;";
                }
                ++$n;
            }
        }

        if ($this->getBuildProperty('generator.objectModel.addSaveMethod')) {
            $script .= '
            $this->resetModified();
';
        }

        $script .= '
            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }
';

        $this->applyBehaviorModifier('postHydrate', $script, '            ');

        $script .= "
            return \$startcol + $n; // $n = ".$this->getTableMapClass()."::NUM_HYDRATE_COLUMNS.

        } catch (Exception \$e) {
            throw new PropelException(sprintf('Error populating %s object', ".var_export($this->getStubObjectBuilder()->getClassName(), true).'), 0, $e);
        }';
    }

    protected function addBuildPkeyCriteriaBody(string &$script): void
    {
        if (!$this->getTable()->getPrimaryKey()) {
            $script .= "
        throw new LogicException('The {$this->getObjectName()} object has no primary key');";

            return;
        }

        $script .= "
        \$criteria = " . $this->getQueryClassName() . '::create();';
        foreach ($this->getTable()->getPrimaryKey() as $col) {
            $clo = $col->getLowercasedName();
            $suffix = $col->isEnumType() ? '?->value' : '';
            $script .= "
        \$criteria->add(" . $this->getColumnConstant($col) . ", \$this->{$clo}{$suffix});";
        }
    }

    protected function addBuildCriteriaBody(string &$script): void
    {
        $script .= "
        \$criteria = new Criteria(" . $this->getTableMapClass() . "::DATABASE_NAME);
";
        foreach ($this->getTable()->getColumns() as $col) {
            $clo = $col->getLowercasedName();
            $suffix = $col->isEnumType() ? '?->value' : '';
            $script .= "
        if (\$this->isColumnModified(" . $this->getColumnConstant($col) . ")) {
            \$criteria->add(" . $this->getColumnConstant($col) . ", \$this->{$clo}{$suffix});
        }";
        }
    }

    protected function createDateFromDb($var): string
    {
        return 'new \\Yakamara\\DateTime\\Date('.$var.')';
    }

    protected function createTimeFromDb($var): string
    {
        return 'new \\Yakamara\\DateTime\\DateTime('.$var.')';
    }

    protected function createDateTimeFromDb($var): string
    {
        return 'new \\Yakamara\\DateTime\\DateTime('.$var.')';
    }

    protected function addDoInsertBodyRaw(): string
    {
        $table = $this->getTable();
        $platform = $this->getPlatform();
        $primaryKeyMethodInfo = '';
        if ($table->getIdMethodParameters()) {
            $params = $table->getIdMethodParameters();
            $imp = $params[0];
            $primaryKeyMethodInfo = $imp->getValue();
        } elseif ($table->getIdMethod() == IdMethod::NATIVE && ($platform->getNativeIdMethod() == PlatformInterface::SEQUENCE || $platform->getNativeIdMethod() == PlatformInterface::SERIAL)) {
            $primaryKeyMethodInfo = $platform->getSequenceName($table);
        }

        $script = '';

        foreach ($table->getPrimaryKey() as $column) {
            if (!$column->isAutoIncrement()) {
                continue;
            }
            $constantName = $this->getColumnConstant($column);
            if ($platform->supportsInsertNullPk()) {
                $script .= "
        \$this->modifiedColumns[$constantName] = true;";
            }
            $columnProperty = $column->getLowercasedName();
            if (!$table->isAllowPkInsert()) {
                $script .= "
        if (null !== \$this->{$columnProperty}) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . $constantName . ')');
        }";
            } elseif (!$platform->supportsInsertNullPk()) {
                $script .= "
        // add primary key column only if it is not null since this database does not accept that
        if (null !== \$this->{$columnProperty}) {
            \$this->modifiedColumns[$constantName] = true;
        }";
            }
        }

        $script .= '

        $criteria = $this->buildCriteria();
        $criteria->setIdentifierQuoting(true);
        $criteria->doInsert($con);
';

        // if auto-increment, get the id after
        if ($platform->isNativeIdMethodAutoIncrement() && $table->getIdMethod() == 'native') {
            $script .= '
        try {';
            $script .= $platform->getIdentifierPhp('$pk', '$con', $primaryKeyMethodInfo);
            $script .= "
        } catch (Exception \$e) {
            throw new PropelException('Unable to get autoincrement id.', 0, \$e);
        }";
            $column = $table->getFirstPrimaryKeyColumn();
            if ($column) {
                if ($table->isAllowPkInsert()) {
                    $script .= '
        if ($pk !== null) {
            $this->set'.$column->getPhpName().'($pk);
        }';
                } else {
                    $script .= '
        $this->set'.$column->getPhpName().'($pk);';
                }
            }
            $script .= '
';
        }

        return $script;
    }

    protected function addTemporalMutator(string &$script, Column $col): void
    {
        $clo = $col->getLowercasedName();

        $dateTimeClass = $this->getDateTimeClass($col);

        $this->addTemporalMutatorComment($script, $col);
        $this->addMutatorOpenOpen($script, $col);
        $this->addMutatorOpenBody($script, $col);

        $fmt = var_export($this->getTemporalFormatter($col), true);

        $script .= "
        \$dt = null === \$v ? null : $dateTimeClass::createFromUnknown(\$v);

        if (\$this->$clo !== null || \$dt !== null) {";

        if (($def = $col->getDefaultValue()) !== null && !$def->isExpression()) {
            $defaultValue = $this->getDefaultValueString($col);
            $script .= "
            if ( (\$dt != \$this->{$clo}) // normalized values don't match
                || (\$dt->format($fmt) === $defaultValue) // or the entered value matches the default
                 ) {";
        } else {
            switch ($col->getType()) {
                case 'DATE':
                    $format = 'Y-m-d';
                    break;
                case 'TIME':
                    $format = 'H:i:s.u';
                    break;
                default:
                    $format = 'Y-m-d H:i:s.u';
            }
            $script .= "
            if (\$this->{$clo} === null || \$dt === null || \$dt->format(\"$format\") !== \$this->{$clo}->format(\"$format\")) {";
        }

        $script .= "
                \$this->$clo = \$dt;
                \$this->modifiedColumns[".$this->getColumnConstant($col).'] = true;
            }
        } // if either are not null
';
        $this->addMutatorClose($script, $col);
    }

    protected function getDateTimeClass(Column $column): string
    {
        if (PropelTypes::isPhpObjectType($column->getPhpType())) {
            return $column->getPhpType();
        }

        if (PropelTypes::DATE === $column->getType()) {
            return '\\Yakamara\\DateTime\\Date';
        }

        return '\\Yakamara\\DateTime\\DateTime';
    }

    public function addEnumAccessorComment(string &$script, Column $column): void
    {
        $clo = $column->getLowercasedName();

        $enum = $column->getPhpType();
        $orNull = $column->isNotNull() ? '' : '|null';

        $script .= "
    /**
     * Get the [$clo] column value.
     * " . $column->getDescription();
        if ($column->isLazyLoad()) {
            $script .= "
     * @param      ConnectionInterface An optional ConnectionInterface connection to use for fetching this lazy-loaded column.";
        }
        $script .= "
     * @return {$this->declareClass($enum)}{$orNull}
     */";
    }

    protected function addEnumAccessorBody(string &$script, Column $column): void
    {
        $this->addDefaultAccessorBody($script, $column);
    }

    protected function addEnumMutator(string &$script, Column $col): void
    {
        $clo = $col->getLowercasedName();

        $type = ($col->isNotNull() ? '' : '?').$this->declareClass($col->getPhpType());

        $script .= "
    /**
     * Set the value of [$clo] column.
     * " . $col->getDescription() . "
     * @param  {$type} \$v new value
     * @return \$this|" . $this->getObjectClassName(true) . " The current object (for fluent API support)
     */";

        $cfc = $col->getPhpName();
        $visibility = $this->getTable()->isReadOnly() ? 'protected' : $col->getMutatorVisibility();

        $null = $col->isNotNull() ? '' : ' = null';

        $script .= "
    " . $visibility . " function set$cfc($type \$v$null)
    {";

        $this->addMutatorOpenBody($script, $col);

        $script .= "
        if (\$this->$clo !== \$v) {
            \$this->$clo = \$v;
            \$this->modifiedColumns[" . $this->getColumnConstant($col) . "] = true;
        }
";

        $this->addMutatorClose($script, $col);
    }

    public function addSetAccessorComment(string &$script, Column $column): void
    {
        $clo = $column->getLowercasedName();

        $enum = $this->declareClass($column->getPhpType());

        $script .= "
    /**
     * Get the [$clo] column value.
     * " . $column->getDescription();
        if ($column->isLazyLoad()) {
            $script .= "
     * @param      ConnectionInterface An optional ConnectionInterface connection to use for fetching this lazy-loaded column.";
        }
        $script .= "
     * @return list<$enum>
     */";
    }

    protected function addSetAccessorBody(string &$script, Column $column): void
    {
        $clo = $column->getLowercasedName();
        $cloConverted = $clo . '_converted';
        if ($column->isLazyLoad()) {
            $script .= $this->getAccessorLazyLoadSnippet($column);
        }

        $enum = $this->declareClass($column->getPhpType());

        $script .= "
        if (null === \$this->$cloConverted) {
            \$this->$cloConverted = array();
        }
        if (!\$this->$cloConverted && null !== \$this->$clo) {
            \$this->$cloConverted = array_map(function (string \$value) {
                return $enum::from(\$value);
            }, explode(',', \$this->$clo));
        }

        return \$this->$cloConverted;";
    }

    protected function addSetMutator(string &$script, Column $col): void
    {
        $clo = $col->getLowercasedName();
        $enum = $this->declareClass($col->getPhpType());

        $script .= "
    /**
     * Set the value of [$clo] column.
     * " . $col->getDescription() . "
     * @param  array<$enum> \$v new value
     * @return \$this|" . $this->getObjectClassName(true) . " The current object (for fluent API support)
     */";

        $this->addMutatorOpenOpen($script, $col);
        $this->addMutatorOpenBody($script, $col);
        $cloConverted = $clo . '_converted';

        $script .= "
        \$v = !\$v ? null : implode(',', array_map(function ($enum \$value) {
            return \$value->value;
        }, \$v));
        if (\$this->$clo !== \$v) {
            \$this->$cloConverted = null;
            \$this->$clo = \$v;
            \$this->modifiedColumns[" . $this->getColumnConstant($col) . "] = true;
        }
";
        $this->addMutatorClose($script, $col);
    }
}
