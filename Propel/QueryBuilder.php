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

class QueryBuilder extends \Propel\Generator\Builder\Om\QueryBuilder
{
    protected function addFilterByCol(&$script, Column $col)
    {
        if ($col->isEnumType()) {
            $this->addFilterByEnumCol($script, $col);

            return;
        }
        if ($col->isSetType()) {
            $this->addFilterBySetCol2($script, $col);

            return;
        }

        parent::addFilterByCol($script, $col);
    }

    private function addFilterByEnumCol(&$script, Column $col): void
    {
        $colPhpName = $col->getPhpName();
        $colName = $col->getName();
        $variableName = $col->getCamelCaseName();
        $qualifiedName = $this->getColumnConstant($col);
        $enumClass = $this->declareClass($col->getPhpType());
        $null = $col->isNotNull() ? '' : '|null';

        $script .= "
    /**
     * Filter the query on the $colName column
     *
     * @param $enumClass|{$enumClass}[]$null \$$variableName The value to use as filter.";

        $script .= "
     * @param string \$comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return \$this|" . $this->getQueryClassName() . " The current query, for fluid interface
     */
    public function filterBy$colPhpName(\$$variableName = null, \$comparison = null)
    {";

        $script .= "
        if (is_object(\$$variableName)) {
            \$$variableName = \${$variableName}->value;
        } elseif (is_array(\$$variableName)) {
            \$convertedValues = array();
            foreach (\$$variableName as \$value) {
                \$convertedValues[] = \$value->value;
            }
            \$$variableName = \$convertedValues;
            if (null === \$comparison) {
                \$comparison = Criteria::IN;
            }
        }";

        $script .= "

        return \$this->addUsingAlias($qualifiedName, \$$variableName, \$comparison);
    }
";
    }

    private function addFilterBySetCol2(&$script, Column $col): void
    {
        $colPhpName = $col->getPhpName();
        $colName = $col->getName();
        $variableName = $col->getCamelCaseName();
        $qualifiedName = $this->getColumnConstant($col);
        $enumClass = $this->declareClass($col->getPhpType());
        $null = $col->isNotNull() ? '' : '|null';

        $script .= "
    /**
     * Filter the query on the $colName column
     *
     * @param $enumClass|{$enumClass}[]$null \$$variableName The value to use as filter.";

        $script .= "
     * @param string \$comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return \$this|" . $this->getQueryClassName() . " The current query, for fluid interface
     */
    public function filterBy$colPhpName(\$$variableName = null, \$comparison = null)
    {";

        $script .= "
        if (null === \${$variableName}) {
            \$comparison = \$comparison ?? Criteria::ISNULL;
        } else {
            \${$variableName} = is_array(\${$variableName}) ? \${$variableName} : [\${$variableName}];
            \$cases = {$enumClass}::cases();
            \${$variableName} = array_reduce(\${$variableName}, function (int \$carry, $enumClass \$case) use (\$cases): int {
                return \$carry | (1 << array_search(\$case, \$cases, true));
            }, 0);
        }
        
        if (null === \$comparison || \$comparison == Criteria::CONTAINS_ALL) {
            if (\${$variableName} === 0) {
                return \$this;
            }
            \$comparison = Criteria::BINARY_ALL;
        } elseif (\$comparison == Criteria::CONTAINS_SOME || \$comparison == Criteria::IN) {
            if (\${$variableName} === 0) {
                return \$this;
            }
            \$comparison = Criteria::BINARY_AND;
        } elseif (\$comparison == Criteria::CONTAINS_NONE) {
            \$key = \$this->getAliasedColName($qualifiedName);
            if (\${$variableName} !== 0) {
                \$this->add(\$key, \${$variableName}, Criteria::BINARY_NONE);
            }
            \$this->addOr(\$key, null, Criteria::ISNULL);

            return \$this;
        }";

        $script .= "

        return \$this->addUsingAlias($qualifiedName, \$$variableName, \$comparison);
    }
";
    }
}
