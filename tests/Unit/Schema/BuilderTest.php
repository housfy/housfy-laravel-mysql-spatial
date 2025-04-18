<?php

namespace Tests\Unit\Schema;

use Illuminate\Database\Schema\Blueprint;
use Mockery;
use Illuminate\Database\MySqlConnection;
use ScaffoldDigital\LaravelMysqlSpatial\Schema\Builder;
use Tests\TestCase;

class BuilderTest extends TestCase
{
    public function testReturnsCorrectBlueprint()
    {
        $connection = Mockery::mock(MysqlConnection::class);
        $connection->shouldReceive('getSchemaGrammar')->once()->andReturn(null);

        $mock = Mockery::mock(Builder::class, [$connection]);
        $mock->makePartial()->shouldAllowMockingProtectedMethods();
        $blueprint = $mock->createBlueprint('test', function () {
        });

        $this->assertInstanceOf(Blueprint::class, $blueprint);
    }
}
