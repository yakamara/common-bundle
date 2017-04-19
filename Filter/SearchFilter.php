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

class SearchFilter extends AbstractFilter
{
    private $current;

    public static function create(string $key = 'search'): self
    {
        return new static($key);
    }

    public function setCurrent(?string $current): self
    {
        $this->current = $current;

        return $this;
    }

    public function getCurrent(): ?string
    {
        return $this->current;
    }

    public function handleRequest(Request $request): self
    {
        $current = $request->query->get($this->getKey());

        return $this->setCurrent($current ?: null);
    }

    public function handleQuery(ModelCriteria $query): self
    {
        if (null === $this->current) {
            return $this;
        }

        /* @noinspection PhpUndefinedMethodInspection */
        $query->search($this->current);

        return $this;
    }
}
