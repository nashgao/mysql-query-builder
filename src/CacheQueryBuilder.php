<?php
/**
 * Copyright (C) SPACE Platform PTY LTD - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Nash Gao <nash@spaceplaform.co>
 * @organization Space Platform
 * @project composer
 * @create Created on 2021/2/5 下午1:43
 * @author Nash Gao
 */

declare(strict_types=1);


namespace Nashgao\MySQL\QueryBuilder;


use Hyperf\Database\Model\Model;
use Nashgao\MySQL\QueryBuilder\Bean\SplBean;

/**
 * @property Model $model
 * @method Model getModel
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
     * @param SplBean $bean
     * @return array
     */
    public function getColumnFromCache(SplBean $bean)
    {
        /** @var Model|null $result */
        $cacheResult = $this->getModel()::findFromCache($bean->getPrimaryKey());
        if (! isset($result))
            return [];

        $result = $cacheResult->toArray();
        $filter = filterBean($bean, [$this->model->primaryKey]);
        // make sure put the $result in front of the filter, other wise it would return wrong value
        return empty( $filter ) ? $result : array_intersect_key($result, $filter);
    }


    /**
     * @param array $ids
     * @param array $filter
     * @return array
     */
    public function getMultiFromCache(array $ids, array $filter = []):array
    {
        $result = $this->getModel()::findManyFromCache($ids);

        if (! isset($result))
            return [];

        $result = $result->toArray();
        return empty($filter) ? $result :
            ( function () use ( $result, $filter ){
                foreach ($result as &$item) {
                    $item = array_intersect_key($item, $filter);
                }
                return $result;
            })();
    }
}