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


use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Hyperf\DbConnection\Db;
use Hyperf\ModelCache\Cacheable;

class MySQLModel extends Model
{
    use Cacheable;

    /**
     * The attributes that hold geometrical data.
     *
     * @var array
     */
    protected array $geometry = array();

    /**
     * Select geometrical attributes as text from database.
     *
     * @var bool
     */
    protected bool $geometryAsText = false;


    /**
     * Get a new query builder for the model's table.
     * Manipulate in case we need to convert geometrical fields to text.
     *
     * @return Builder
     */
    public function newQuery(): Builder
    {
        if (!empty($this->geometry) && $this->geometryAsText === true) {
            $raw = '';
            foreach ($this->geometry as $column)
            {
                $raw .= 'st_asText(`' . $this->table . '`.`' . $column . '`) as `' . $column . '`, ';
            }
            $raw = substr($raw, 0, -2);

            return parent::newQuery()->addSelect('*', DB::raw($raw));
        }
        return parent::newQuery();
    }
}