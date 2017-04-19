<?php declare(strict_types=1);

/*
 * This file is part of the common-bundle package.
 *
 * (c) Yakamara Media GmbH & Co. KG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yakamara\CommonBundle\Filter;

use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Collection\Collection;
use Symfony\Component\HttpFoundation\Request;

class ModelFilter extends SimpleFilter
{
    private $emptyChoice = false;

    public function __construct(string $key, $choices)
    {
        parent::__construct($key, $choices);
        $this->setTranslationPrefix(false);
    }

    /**
     * @param string                         $key
     * @param ModelCriteria|Collection|array $choices
     *
     * @return static
     */
    public static function create(string $key, $choices)
    {
        return new static($key, $choices);
    }

    public function setChoices($choices)
    {
        if ($choices instanceof ModelCriteria) {
            $choices = $choices->find();
        }

        if ($choices instanceof Collection) {
            $choices = $choices->toKeyIndex();
        }

        $this->choices = $choices;

        return $this;
    }

    public function setEmptyChoice(bool $emptyChoice): self
    {
        $this->emptyChoice = $emptyChoice;

        return $this;
    }

    public function hasEmptyChoice(): bool
    {
        return $this->emptyChoice;
    }

    public function handleRequest(Request $request)
    {
        $current = $request->query->get($this->getKey());

        if (null === $current) {
            return $this->setCurrent(null);
        }

        $current = (int) $current;

        if (isset($this->choices[$current]) || $this->emptyChoice && 0 === $current) {
            return $this->setCurrent($current);
        }

        return $this->setCurrent(null);
    }

    public function handleQuery(ModelCriteria $query)
    {
        if (null === $this->getCurrent()) {
            return $this;
        }

        $column = ucfirst($this->getDbKey()).'Id';
        $query->filterBy($column, $this->getCurrent() ?: null);

        return $this;
    }
}
