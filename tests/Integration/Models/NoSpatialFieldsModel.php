<?php

namespace Tests\Integration\Models;

use Illuminate\Database\Eloquent\Model;
use ScaffoldDigital\LaravelMysqlSpatial\Eloquent\SpatialTrait;

class NoSpatialFieldsModel extends Model
{
    use SpatialTrait;

    protected $table = 'no_spatial_fields';

    public $timestamps = false;
}
