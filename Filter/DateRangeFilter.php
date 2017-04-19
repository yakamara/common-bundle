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
use Yakamara\DateTime\Date;
use Yakamara\DateTime\Range\DateRange;

class DateRangeFilter extends AbstractFilter
{
    /** @var DateRange */
    private $current;

    public function __construct(string $key)
    {
        parent::__construct($key);
    }

    public static function create(string $key = 'date'): self
    {
        return new static($key);
    }

    public function setCurrent(?DateRange $current): self
    {
        $this->current = $current;

        return $this;
    }

    public function getCurrent(): ?DateRange
    {
        return $this->current;
    }

    public function handleRequest(Request $request): self
    {
        $current = $request->query->get($this->getKey());

        if (null === $current || !preg_match('/\d{4}-\d{2}-\d{2}-\d{4}-\d{2}-\d{2}/', $current)) {
            return $this->setCurrent(null);
        }

        return $this->setCurrent(new DateRange(
            new Date(substr($current, 0, 10)),
            new Date(substr($current, 11))
        ));
    }

    public function handleQuery(ModelCriteria $query): self
    {
        if (null === $this->current) {
            return $this;
        }

        $method = 'filterBy'.ucfirst($this->getDbKey());
        $query->$method([
            'min' => $this->current->getStart(),
            'max' => $this->current->getEnd(),
        ]);

        return $this;
    }
}
