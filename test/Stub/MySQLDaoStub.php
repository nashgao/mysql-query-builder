<?php

declare(strict_types=1);

namespace Nashgao\Test\Stub;

use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Nashgao\MySQL\QueryBuilder\Bean\MySQLBean;
use Nashgao\MySQL\QueryBuilder\MySQLDao;
use Nashgao\MySQL\QueryBuilder\MySQLModel;

abstract class MySQLDaoStub
{
    protected static MySQLBean $bean;

    protected static MySQLModel $model;

    abstract static function initBean();

    abstract static function initModel();

    /**
     * @return LegacyMockInterface|MockInterface|MySQLDao
     */
    public static function createMySQLDaoStub()
    {
        $dao = \Mockery::mock(MySQLDao::class);
        return static::addCommonMethod($dao);
    }

    /**
     * @param MockInterface|LegacyMockInterface|MySQLDaoStub $dao
     */
    public static function addCommonMethod($dao)
    {
        if (! isset(static::$bean)) {
            static::initBean();
        }

        if (! isset(static::$model)) {
            static::initModel();
        }

        $dao->shouldReceive('value')
            ->with(\Mockery::type(MySQLBean::class))
            ->andReturn(\Mockery::any());

        $dao->shouldReceive('pluck')
            ->with(\Mockery::type(MySQLBean::class))
            ->andReturn(static::$bean->toArray());

        $dao->shouldReceive('first')
            ->andReturn(static::$bean->toArray());

        $dao->shouldReceive('get')
            ->andReturn(null, static::$bean->toArray());

        $dao->shouldReceive('getMulti')
            ->with(\Mockery::type(MySQLBean::class))
            ->andReturn(null, static::$bean->toArray());

        $dao->shouldReceive('getAll')
            ->andReturn([static::$bean->toArray()]);

        $dao->shouldReceive('fieldExists')
            ->with(\Mockery::type('string'),\Mockery::any())
            ->andReturn(true);

        $dao->shouldReceive('find')
            ->with(\Mockery::any())
            ->andReturn(static::$model);

        $dao->shouldReceive('getFromCache')
            ->with(\Mockery::any())
            ->andReturn(null, static::$model);

        // add cache query
        $dao->shouldReceive('getColumnFromCache')
            ->with(\Mockery::type(MySQLBean::class))
            ->andReturn(\Mockery::type('array'));

        return $dao;
    }
}
