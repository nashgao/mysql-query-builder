<?php

declare(strict_types=1);


namespace Nashgao\MySQL\QueryBuilder\Bean;

class MySQLBean extends \Nashgao\Utils\Bean\SplBean
{
    public function issetPrimaryKey(): bool
    {
        return false;
    }

    public function getPrimaryKey()
    {
        return null;
    }
}
