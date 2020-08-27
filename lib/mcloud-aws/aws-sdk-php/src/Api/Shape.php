<?php

namespace MediaCloud\Vendor\Aws\Api;

/**
 * Base class representing a modeled shape.
 */
class Shape extends AbstractModel
{
    /**
     * Get a concrete shape for the given definition.
     *
     * @param array    $definition
     * @param ShapeMap $shapeMap
     *
     * @return mixed
     * @throws \RuntimeException if the type is invalid
     */
    public static function create(array $definition, ShapeMap $shapeMap)
    {
        static $map = [
            'structure' => 'MediaCloud\Vendor\Aws\Api\StructureShape',
            'map'       => 'MediaCloud\Vendor\Aws\Api\MapShape',
            'list'      => 'MediaCloud\Vendor\Aws\Api\ListShape',
            'timestamp' => 'MediaCloud\Vendor\Aws\Api\TimestampShape',
            'integer'   => 'MediaCloud\Vendor\Aws\Api\Shape',
            'double'    => 'MediaCloud\Vendor\Aws\Api\Shape',
            'float'     => 'MediaCloud\Vendor\Aws\Api\Shape',
            'long'      => 'MediaCloud\Vendor\Aws\Api\Shape',
            'string'    => 'MediaCloud\Vendor\Aws\Api\Shape',
            'byte'      => 'MediaCloud\Vendor\Aws\Api\Shape',
            'character' => 'MediaCloud\Vendor\Aws\Api\Shape',
            'blob'      => 'MediaCloud\Vendor\Aws\Api\Shape',
            'boolean'   => 'MediaCloud\Vendor\Aws\Api\Shape'
        ];

        if (isset($definition['shape'])) {
            return $shapeMap->resolve($definition);
        }

        if (!isset($map[$definition['type']])) {
            throw new \RuntimeException('Invalid type: '
                . print_r($definition, true));
        }

        $type = $map[$definition['type']];

        return new $type($definition, $shapeMap);
    }

    /**
     * Get the type of the shape
     *
     * @return string
     */
    public function getType()
    {
        return $this->definition['type'];
    }

    /**
     * Get the name of the shape
     *
     * @return string
     */
    public function getName()
    {
        return $this->definition['name'];
    }
}
