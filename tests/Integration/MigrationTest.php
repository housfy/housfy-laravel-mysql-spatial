<?php

namespace Tests\Integration;

use Illuminate\Support\Facades\DB;
use Tests\Integration\Migrations\CreateTables;
use Tests\Integration\Migrations\UpdateTables;
use Tests\TestCase;

class MigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        (new CreateTables)->up();
        (new UpdateTables)->up();
    }

    public function tearDown(): void
    {
        (new UpdateTables)->down();
        (new CreateTables)->down();

        parent::tearDown();
    }

    public function testTableWasCreatedWithRightTypes(): void
    {
        $result = DB::selectOne('SHOW CREATE TABLE geometry');

        $expected = 'CREATE TABLE `geometry` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `geo` geometry DEFAULT NULL,
  `location` point NOT NULL,
  `line` linestring DEFAULT NULL,
  `shape` polygon DEFAULT NULL,
  `multi_locations` multipoint DEFAULT NULL,
  `multi_lines` multilinestring DEFAULT NULL,
  `multi_shapes` multipolygon DEFAULT NULL,
  `multi_geometries` geomcollection DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  SPATIAL KEY `geometry_location_spatialindex` (`location`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

        $this->assertEquals('geometry', $result->Table);
        $this->assertEquals($expected, $result->{'Create Table'});
    }

    public function testTableWasCreatedWithSrid(): void
    {
        $result = DB::selectOne('SHOW CREATE TABLE with_srid');

        $expected = 'CREATE TABLE `with_srid` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `geo` geometry /*!80003 SRID 3857 */ DEFAULT NULL,
  `location` point /*!80003 SRID 3857 */ DEFAULT NULL,
  `line` linestring /*!80003 SRID 3857 */ DEFAULT NULL,
  `shape` polygon /*!80003 SRID 3857 */ DEFAULT NULL,
  `multi_locations` multipoint /*!80003 SRID 3857 */ DEFAULT NULL,
  `multi_lines` multilinestring /*!80003 SRID 3857 */ DEFAULT NULL,
  `multi_shapes` multipolygon /*!80003 SRID 3857 */ DEFAULT NULL,
  `multi_geometries` geomcollection /*!80003 SRID 3857 */ DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

        $this->assertEquals('with_srid', $result->Table);
        $this->assertEquals($expected, $result->{'Create Table'});
    }
}
