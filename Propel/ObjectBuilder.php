<?php declare(strict_types=1);

namespace Yakamara\CommonBundle\Propel;

use Propel\Generator\Model\Column;
use Propel\Generator\Model\PropelTypes;
use Propel\Generator\Platform\MysqlPlatform;
use Propel\Generator\Platform\OraclePlatform;

class ObjectBuilder extends \Propel\Generator\Builder\Om\ObjectBuilder
{
    protected function addHydrateBody(&$script)
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
                    $dateTimeClass = $this->getDateTimeClass($col);
                    $handleMysqlDate = false;
                    if ($this->getPlatform() instanceof MysqlPlatform) {
                        if ($col->getType() === PropelTypes::TIMESTAMP) {
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
                \$this->$clo = new \\Yakamara\\Date(\$col);";
                    } else {
                        $script .= "
                \$this->$clo = \\Yakamara\\DateTime::createFromUtc(\$col);";
                    }
                    $script .= '
            }';
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
            } // if col->isLazyLoad()
        } /* foreach */

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

    protected function getDateTimeClass(Column $column)
    {
        if (PropelTypes::isPhpObjectType($column->getPhpType())) {
            return $column->getPhpType();
        }

        if (PropelTypes::DATE === $column->getType()) {
            return '\\Yakamara\\Date';
        }

        return '\\Yakamara\\DateTime';
    }
}
