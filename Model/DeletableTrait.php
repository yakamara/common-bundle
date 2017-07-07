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

trait DeletableTrait
{
    public function isDeletable(): bool
    {
        if (!$this->hasVirtualColumn('isDeletable')) {
            $class = __CLASS__.'Query';
            /** @var ModelCriteria $query */
            $query = new $class();

            $isDeletable = (bool) $query
                ->filterByPrimaryKey($this->getPrimaryKey())
                ->withIsDeletable()
                ->select('isDeletable')
                ->findOne();

            $this->setVirtualColumn('isDeletable', $isDeletable);
        }

        return (bool) $this->getVirtualColumn('isDeletable');
    }
}
