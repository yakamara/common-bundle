<?php declare(strict_types=1);

/*
 * This file is part of the common-bundle package.
 *
 * (c) Yakamara Media GmbH & Co. KG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yakamara\CommonBundle\Model;

use Propel\Runtime\ActiveQuery\ModelCriteria;

trait VirtualColumnTrait
{
    private function getOrFetchVirtualColumn($column, ?callable $fetch = null)
    {
        if ($this->hasVirtualColumn($column)) {
            return $this->getVirtualColumn($column);
        }

        if ($fetch) {
            $value = $fetch();

            $this->setVirtualColumn($column, $value);

            return $value;
        }

        $class = __CLASS__.'Query';
        /** @var ModelCriteria $query */
        $query = new $class();

        $method = 'with'.ucfirst($column);

        $value = $query
            ->filterByPrimaryKey($this->getPrimaryKey())
            ->$method()
            ->select($column)
            ->requireOne();

        $this->setVirtualColumn($column, $value);

        return $value;
    }
}
