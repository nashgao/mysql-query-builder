<?php

declare(strict_types=1);


namespace Nashgao\MySQL\QueryBuilder\Bean;

use Nashgao\Utils\Bean\SplBean;

class MySQLBean extends SplBean
{
    public function issetPrimaryKey(): bool
    {
        return false;
    }

    public function getPrimaryKey(): mixed
    {
        return null;
    }
}
