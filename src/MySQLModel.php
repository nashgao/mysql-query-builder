<?php

declare(strict_types=1);


namespace Nashgao\MySQL\QueryBuilder;

use Hyperf\Database\Model\Model;
use Hyperf\ModelCache\Cacheable;

class MySQLModel extends Model
{
    use Cacheable;

    public $primaryKey;
}
