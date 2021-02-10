<?php
/**
 * Copyright (C) SPACE Platform PTY LTD - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Nash Gao <nash@spaceplaform.co>
 * @organization Space Platform
 * @author Nash Gao
 */

declare(strict_types=1);


namespace Nashgao\MySQL\QueryBuilder;


use Hyperf\Database\Model\Model;
use Nashgao\MySQL\QueryBuilder\Concerns\CacheQueryBuilder;
use Nashgao\MySQL\QueryBuilder\Concerns\QueryBuilder;
use function DeepCopy\deep_copy;

class MySQLDao
{
    use QueryBuilder;
    use CacheQueryBuilder;


    /**
     * @return Model
     */
    public function getModel():Model
    {
        return deep_copy($this->model);
    }
}