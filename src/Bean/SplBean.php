<?php
/**
 * Copyright (C) SPACE Platform PTY LTD - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Nash Gao <nash@spaceplaform.co>
 * @organization Space Platform
 * @project composer
 * @create Created on 2021/1/31 下午1:24
 * @author Nash Gao
 */

declare(strict_types=1);


namespace Nashgao\MySQL\QueryBuilder\Bean;



abstract class SplBean extends \EasySwoole\Spl\SplBean implements SplBeanInterface
{
    /**
     * @param array|null $columns
     * @param null $filter
     * @return array
     */
    public function toArray(array $columns = null, $filter = null): array
    {
        $array = parent::toArray($columns, $filter);
        array_walk_recursive($array, function (&$item, $key) {
            if (! is_scalar($item) and $item instanceof SplBeanInterface) {
                $item = $item->toArray();
            }
        });
        return array_filter($array,
            function($item) {
                return !is_null($item);
            }
        );
    }

    /**
     * @param array|null $columns
     * @param null $filter
     * @return array
     */
    public function toArrayWithOneDimension(array $columns = null, $filter = null): array
    {
        return parent::toArray($columns, $filter);
    }
}