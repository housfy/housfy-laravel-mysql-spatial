<?php

namespace Tests\Unit\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Mockery;
use ScaffoldDigital\LaravelMysqlSpatial\Eloquent\Builder;
use ScaffoldDigital\LaravelMysqlSpatial\Eloquent\SpatialExpression;
use ScaffoldDigital\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use ScaffoldDigital\LaravelMysqlSpatial\Types\LineString;
use ScaffoldDigital\LaravelMysqlSpatial\Types\Point;
use ScaffoldDigital\LaravelMysqlSpatial\Types\Polygon;
use Tests\TestCase;

class BuilderTest extends TestCase
{
    protected $builder;
    protected $queryBuilder;

    protected function setUp(): void
    {
        $connection = Mockery::mock(MysqlConnection::class)->makePartial();
        $grammar = Mockery::mock(MySqlGrammar::class)->makePartial();
        $this->queryBuilder = Mockery::mock(QueryBuilder::class, [$connection, $grammar]);

        $this->queryBuilder
            ->shouldReceive('from')
            ->once()
            ->andReturn($this->queryBuilder);

        $this->builder = new Builder($this->queryBuilder);
        $this->builder->setModel(new TestBuilderModel());
    }

    public function testUpdatePoint()
    {
        $point = new Point(1, 2);
        $this->queryBuilder
            ->shouldReceive('update')
            ->with(['point' => new SpatialExpression($point)])
            ->once()
            ->andReturn(1);

        $result = $this->builder->update(['point' => $point]);

        $this->assertSame(1, $result);
    }

    public function testUpdateLinestring()
    {
        $linestring = new LineString([new Point(0, 0), new Point(1, 1), new Point(2, 2)]);

        $this->queryBuilder
            ->shouldReceive('update')
            ->with(['linestring' => new SpatialExpression($linestring)])
            ->once()
            ->andReturn(1);

        $result = $this->builder->update(['linestring' => $linestring]);

        $this->assertSame(1, $result);
    }

    public function testUpdatePolygon()
    {
        $linestrings[] = new LineString([new Point(0, 0), new Point(0, 1)]);
        $linestrings[] = new LineString([new Point(0, 1), new Point(1, 1)]);
        $linestrings[] = new LineString([new Point(1, 1), new Point(0, 0)]);
        $polygon = new Polygon($linestrings);

        $this->queryBuilder
            ->shouldReceive('update')
            ->with(['polygon' => new SpatialExpression($polygon)])
            ->once()
            ->andReturn(1);

        $result = $this->builder->update(['polygon' => $polygon]);

        $this->assertSame(1, $result);
    }

    public function testUpdatePointWithSrid()
    {
        $point = new Point(1, 2, 4326);
        $this->queryBuilder
            ->shouldReceive('update')
            ->with(['point' => new SpatialExpression($point)])
            ->once()
            ->andReturn(1);

        $result = $this->builder->update(['point' => $point]);

        $this->assertSame(1, $result);
    }

    public function testUpdateLinestringWithSrid()
    {
        $linestring = new LineString([new Point(0, 0), new Point(1, 1), new Point(2, 2)], 4326);

        $this->queryBuilder
            ->shouldReceive('update')
            ->with(['linestring' => new SpatialExpression($linestring)])
            ->once()
            ->andReturn(1);

        $result = $this->builder->update(['linestring' => $linestring]);

        $this->assertSame(1, $result);
    }

    public function testUpdatePolygonWithSrid()
    {
        $linestrings[] = new LineString([new Point(0, 0), new Point(0, 1)]);
        $linestrings[] = new LineString([new Point(0, 1), new Point(1, 1)]);
        $linestrings[] = new LineString([new Point(1, 1), new Point(0, 0)]);
        $polygon = new Polygon($linestrings, 4326);

        $this->queryBuilder
            ->shouldReceive('update')
            ->with(['polygon' => new SpatialExpression($polygon)])
            ->once()
            ->andReturn(1);

        $result = $this->builder->update(['polygon' => $polygon]);

        $this->assertSame(1, $result);
    }
}

class TestBuilderModel extends Model
{
    use SpatialTrait;

    public $timestamps = false;
    protected $spatialFields = ['point', 'linestring', 'polygon'];
}
