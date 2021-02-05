<?php
/**
 * Copyright (C) SPACE Platform PTY LTD - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Nash Gao <nash@spaceplaform.co>
 * @organization Space Platform
 * @project composer
 * @create Created on 2021/1/31 下午1:31
 * @author Nash Gao
 */

declare(strict_types=1);

use Nashgao\MySQL\QueryBuilder\Bean\SplBean;


if (! function_exists('array_filter_null_value')) {
    /**
     * @param array $array
     * @return array
     */
    function array_filter_null_value(array $array): array
    {
        return array_filter(
            $array,
            function($item) {
                return !is_null($item);
            }
        );
    }
}

if (! function_exists('filterBean')) {
    /**
     * Filter the bean and to array with not null
     * @param SplBean $bean
     * @param array $filter
     * @return array
     */
    function filterBean(SplBean $bean, array $filter = []):array
    {
        return $bean->toArray(array_keys(array_diff_key($bean->toArrayWithMapping(), array_fill_keys($filter, null))), $bean::FILTER_NOT_NULL);
    }
}
