<?php

namespace ScaffoldDigital\LaravelMysqlSpatial\Types;

use GeoJson\GeoJson;
use GeoJson\Geometry\Polygon as GeoJsonPolygon;
use ScaffoldDigital\LaravelMysqlSpatial\Exceptions\InvalidGeoJsonException;

class Polygon extends MultiLineString
{
    public function toWKT()
    {
        return sprintf('POLYGON((%s))', (string) $this);
    }

    public static function fromJson($geoJson)
    {
        if (is_string($geoJson)) {
            $geoJson = GeoJson::jsonUnserialize(json_decode($geoJson));
        }

        if (!is_a($geoJson, GeoJsonPolygon::class)) {
            throw new InvalidGeoJsonException('Expected ' . GeoJsonPolygon::class . ', got ' . get_class($geoJson));
        }

        $set = [];
        foreach ($geoJson->getCoordinates() as $coordinates) {
            $points = [];
            foreach ($coordinates as $coordinate) {
                $points[] = new Point($coordinate[1], $coordinate[0]);
            }
            $set[] = new LineString($points);
        }

        return new self($set);
    }

    public function __toString(): string
    {
        return implode(',', array_map(function (LineString $lineString) {
            return sprintf('%s', (string) $lineString);
        }, $this->getLineStrings()));
    }

    /**
     * Convert to GeoJson Polygon that is jsonable to GeoJSON.
     *
     * @return \GeoJson\Geometry\Polygon
     */
    public function jsonSerialize(): mixed
    {
        $linearRings = [];
        foreach ($this->items as $lineString) {
            $linearRings[] = new \GeoJson\Geometry\LinearRing($lineString->jsonSerialize()->getCoordinates());
        }

        return new GeoJsonPolygon($linearRings);
    }
}
