<?php

namespace ScaffoldDigital\LaravelMysqlSpatial\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class LineString extends Type
{
    const LINESTRING = 'linestring';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return 'linestring';
    }

    public function getName()
    {
        return self::LINESTRING;
    }
}
