<?php

declare(strict_types=1);


namespace Nashgao\MySQL\QueryBuilder\Concerns;

use Nashgao\MySQL\QueryBuilder\MySQLModel as Model;
use Nashgao\MySQL\QueryBuilder\Bean\MySQLBean;

/**
 * @property Model $model
 * @method Model getModel()
 */
trait CacheQueryBuilder
{
    /**
     * @param $primaryKey
     * @return Model|null
     */
    public function getFromCache($primaryKey): ?Model
    {
        return $this->getModel()::findFromCache($primaryKey);
    }

    /**
     * @param MySQLBean $bean
     * @return array
     */
    public function getColumnFromCache(MySQLBean $bean)
    {
        /** @var Model|null $result */
        $cacheResult = $this->getModel()::findFromCache($bean->getPrimaryKey());
        if (! isset($result)) {
            return [];
        }

        $result = $cacheResult->toArray();
        $filter = filterBean($bean, [$this->model->primaryKey]);
        // make sure put the $result in front of the filter, other wise it would return wrong value
        return empty($filter) ? $result : array_intersect_key($result, $filter);
    }


    /**
     * @param array $ids
     * @param array $filter
     * @return array
     */
    public function getMultiFromCache(array $ids, array $filter = []):array
    {
        $result = $this->getModel()::findManyFromCache($ids);

        if (! isset($result)) {
            return [];
        }

        $result = $result->toArray();
        return empty($filter) ? $result :
            (function () use ($result, $filter) {
                foreach ($result as &$item) {
                    $item = array_intersect_key($item, $filter);
                }
                return $result;
            })();
    }
}
