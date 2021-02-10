<?php
/**
 * Copyright (C) SPACE Platform PTY LTD - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Nash Gao <nash@spaceplaform.co>
 * @organization Space Platform
 * @project composer
 * @create Created on 2021/1/31 下午1:27
 * @author Nash Gao
 */

declare(strict_types=1);



namespace Nashgao\MySQL\QueryBuilder\Bean;

interface SplBeanInterface
{
    public function issetPrimaryKey():bool;
    public function getPrimaryKey();
    public function toArray(array $columns = null, $filter = null);
}
