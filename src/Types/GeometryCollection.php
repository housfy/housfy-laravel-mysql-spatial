<?php

namespace ScaffoldDigital\LaravelMysqlSpatial\Types;

use ArrayAccess;
use ArrayIterator;
use Countable;
use GeoJson\Feature\FeatureCollection;
use GeoJson\GeoJson;
use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;
use IteratorAggregate;
use ScaffoldDigital\LaravelMysqlSpatial\Exceptions\InvalidGeoJsonException;
use Traversable;

class GeometryCollection extends Geometry implements IteratorAggregate, ArrayAccess, Arrayable, Countable
{
    /**
     * The minimum number of items required to create this collection.
     *
     * @var int
     */
    protected $minimumCollectionItems = 0;

    /**
     * The class of the items in the collection.
     *
     * @var string
     */
    protected $collectionItemType = GeometryInterface::class;

    /**
     * The items contained in the spatial collection.
     *
     * @var GeometryInterface[]
     */
    protected $items = [];

    /**
     * @param GeometryInterface[] $geometries
     * @param int                 $srid
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $geometries, $srid = 0)
    {
        parent::__construct($srid);

        $this->validateItems($geometries);

        $this->items = $geometries;
    }

    public function getGeometries()
    {
        return $this->items;
    }

    public function toWKT()
    {
        return sprintf('GEOMETRYCOLLECTION(%s)', (string) $this);
    }

    public function __toString()
    {
        return implode(',', array_map(function (GeometryInterface $geometry) {
            return $geometry->toWKT();
        }, $this->items));
    }

    public static function fromString($wktArgument, $srid = 0)
    {
        if (empty($wktArgument)) {
            return new static([]);
        }

        $geometry_strings = preg_split('/,\s*(?=[A-Za-z])/', $wktArgument);

        return new static(array_map(function ($geometry_string) {
            $klass = Geometry::getWKTClass($geometry_string);

            return call_user_func($klass . '::fromWKT', $geometry_string);
        }, $geometry_strings), $srid);
    }

    public function toArray()
    {
        return $this->items;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->offsetExists($offset) ? $this->items[$offset] : null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->validateItemType($value);

        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public static function fromJson($geoJson)
    {
        if (is_string($geoJson)) {
            $geoJson = GeoJson::jsonUnserialize(json_decode($geoJson));
        }

        if (!is_a($geoJson, FeatureCollection::class)) {
            throw new InvalidGeoJsonException('Expected ' . FeatureCollection::class . ', got ' . get_class($geoJson));
        }

        $set = [];
        foreach ($geoJson->getFeatures() as $feature) {
            $set[] = parent::fromJson($feature);
        }

        return new self($set);
    }

    /**
     * Convert to GeoJson GeometryCollection that is jsonable to GeoJSON.
     *
     * @return \GeoJson\Geometry\GeometryCollection
     */
    public function jsonSerialize(): mixed
    {
        $geometries = [];
        foreach ($this->items as $geometry) {
            $geometries[] = $geometry->jsonSerialize();
        }

        return new \GeoJson\Geometry\GeometryCollection($geometries);
    }

    /**
     * Checks whether the items are valid to create this collection.
     *
     * @param array $items
     */
    protected function validateItems(array $items)
    {
        $this->validateItemCount($items);

        foreach ($items as $item) {
            $this->validateItemType($item);
        }
    }

    /**
     * Checks whether the array has enough items to generate a valid WKT.
     *
     * @param array $items
     *
     * @see $minimumCollectionItems
     */
    protected function validateItemCount(array $items)
    {
        if (count($items) < $this->minimumCollectionItems) {
            $entries = $this->minimumCollectionItems === 1 ? 'entry' : 'entries';

            throw new InvalidArgumentException(sprintf(
                '%s must contain at least %d %s',
                get_class($this),
                $this->minimumCollectionItems,
                $entries
            ));
        }
    }

    /**
     * Checks the type of the items in the array.
     *
     * @param $item
     *
     * @see $collectionItemType
     */
    protected function validateItemType($item)
    {
        if (!$item instanceof $this->collectionItemType) {
            throw new InvalidArgumentException(sprintf(
                '%s must be a collection of %s',
                get_class($this),
                $this->collectionItemType
            ));
        }
    }
}
