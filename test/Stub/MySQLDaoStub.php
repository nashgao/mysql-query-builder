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

    abstract public static function initBean();

    abstract public static function initModel();

    /**
     * @return MySQLBean
     */
    public static function getBean(): MySQLBean
    {
        return static::$bean;
    }

    /**
     * @return MySQLModel
     */
    public static function getModel(): MySQLModel
    {
        return static::$model;
    }

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
            ->andReturn(static::$bean->toArray(),null);

        $dao->shouldReceive('getMulti')
            ->with(\Mockery::type(MySQLBean::class))
            ->andReturn(static::$bean->toArray(),null);

        $dao->shouldReceive('getAll')
            ->andReturn([static::$bean->toArray()]);

        $dao->shouldReceive('fieldExists')
            ->with(\Mockery::type('string'), \Mockery::any())
            ->andReturn(true);

        $dao->shouldReceive('find')
            ->with(\Mockery::any())
            ->andReturn(static::$model);

        $dao->shouldReceive('getFromCache')
            ->with(\Mockery::any())
            ->andReturn(static::$model,null);

        // add cache query
        $dao->shouldReceive('getColumnFromCache')
            ->with(\Mockery::type(MySQLBean::class))
            ->andReturn(\Mockery::type('array'));

        return $dao;
    }
}
