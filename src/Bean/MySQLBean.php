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


namespace Nashgao\MySQL\QueryBuilder\Bean;

class MySQLBean extends SplBean
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
