<?php

namespace Yakamara\CommonBundle\Model;

trait PersonTrait
{
    abstract public function getFirstName();

    abstract public function getLastName();

    public function getFullName()
    {
        return trim($this->getFirstName().' '.$this->getLastName());
    }

    public function getReverseFullName()
    {
        return $this->getLastName().', '.$this->getFirstName();
    }

    public function __toString()
    {
        return $this->getFullName();
    }
}
