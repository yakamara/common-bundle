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
use Symfony\Component\HttpFoundation\Request;

class SimpleFilter extends AbstractFilter
{
    protected $choices;
    /** @var null|int|string */
    private $current;
    /** @var bool|string */
    private $translationPrefix = 'label.';

    public function __construct(string $key, $choices)
    {
        parent::__construct($key);

        $this->setChoices($choices);
    }

    /**
     * @param string $key
     * @param array  $choices
     *
     * @return static
     */
    public static function create(string $key, $choices)
    {
        return new static($key, $choices);
    }

    public function setChoices($choices)
    {
        $this->choices = array_combine($choices, $choices);

        return $this;
    }

    public function getChoices(): array
    {
        return $this->choices;
    }

    /**
     * @param bool|string $prefix
     */
    public function setTranslationPrefix($prefix): self
    {
        $this->translationPrefix = $prefix;

        return $this;
    }

    public function getTranslationPrefix()
    {
        return $this->translationPrefix;
    }

    public function setCurrent($current): self
    {
        $this->current = $current;

        return $this;
    }

    public function getCurrent()
    {
        return $this->current;
    }

    public function getCurrentData()
    {
        return $this->choices[$this->current] ?? $this->current;
    }

    public function handleRequest(Request $request)
    {
        $current = $request->query->get($this->getKey());

        if (null === $current || !isset($this->choices[$current])) {
            return $this->setCurrent(null);
        }

        return $this->setCurrent($current);
    }

    public function handleQuery(ModelCriteria $query)
    {
        if (null === $this->current) {
            return $this;
        }

        $column = ucfirst($this->getDbKey());
        $query->filterBy($column, $this->current);

        return $this;
    }
}
