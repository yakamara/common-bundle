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

abstract class AbstractFilter
{
    private $key;
    private $dbKey;

    public function __construct(string $key)
    {
        $this->key = $key;
        $this->dbKey = $key;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     *
     * @return $this
     */
    public function setDbKey(string $key): self
    {
        $this->dbKey = $key;

        return $this;
    }

    public function getDbKey(): string
    {
        return $this->dbKey;
    }

    /**
     * @param Request $request
     *
     * @return $this
     */
    abstract public function handleRequest(Request $request);

    /**
     * @param ModelCriteria $query
     *
     * @return $this
     */
    abstract public function handleQuery(ModelCriteria $query);

    /**
     * @param Request       $request
     * @param ModelCriteria $query
     *
     * @return $this
     */
    public function handle(Request $request, ModelCriteria $query): self
    {
        return $this->handleRequest($request)->handleQuery($query);
    }

    /**
     * @param self[]             $filters
     * @param Request            $request
     * @param ModelCriteria|null $query
     */
    public static function handleAll(array $filters, Request $request, ModelCriteria $query = null): void
    {
        foreach ($filters as $filter) {
            $filter->handleRequest($request);
            if ($query) {
                $filter->handleQuery($query);
            }
        }
    }
}
