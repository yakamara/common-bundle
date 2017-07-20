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

trait DeletableTrait
{
    use VirtualColumnTrait;

    public function isDeletable(): bool
    {
        return (bool) $this->getOrFetchVirtualColumn(__FUNCTION__);
    }
}
