<?php

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

    protected MySQLModel $model;

    public function __construct(MySQLModel $model)
    {
        $this->model = $model;
    }

    /**
     * @return Model
     */
    public function getModel():Model
    {
        return deep_copy($this->model);
    }
}
