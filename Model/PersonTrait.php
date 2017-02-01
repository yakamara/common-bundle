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

trait PersonTrait
{
    public function __toString()
    {
        return $this->getReverseFullName() ?: '';
    }

    abstract public function getFirstName();

    abstract public function getLastName();

    public function getFullName()
    {
        if (!$this->getLastName() && !$this->getFirstName()) {
            return null;
        }

        return trim($this->getFirstName().' '.$this->getLastName());
    }

    public function getReverseFullName()
    {
        if (!$this->getLastName() && !$this->getFirstName()) {
            return null;
        }

        return trim($this->getLastName().', '.$this->getFirstName(), ' ,');
    }
}
