<?php

declare(strict_types=1);

namespace Nashgao\Test\Stub;

use Nashgao\MySQL\QueryBuilder\Bean\MySQLBean;
use Nashgao\MySQL\QueryBuilder\MySQLModel;
use PHPUnit\Framework\TestCase;

class MySQLDaoStubTest extends TestCase
{
    public function testStub()
    {
        $daoStub = new class extends MySQLDaoStub {
            public static function initBean()
            {
                static::$bean = new class extends MySQLBean {
                    public int $id = 1;
                };
            }

            public static function initModel()
            {
                static::$model = new class extends MySQLModel {

                };
            }
        };

        $dao = $daoStub::createMySQLDaoStub();
        $value = $dao->getFromCache(1);
        $this->assertInstanceOf(MySQLModel::class, $value);
    }

    public function tearDown(): void
    {
        \Mockery::close();
    }
}
