<?php
namespace ILAB_Aws\Api;

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
            'structure' => 'ILAB_Aws\Api\StructureShape',
            'map'       => 'ILAB_Aws\Api\MapShape',
            'list'      => 'ILAB_Aws\Api\ListShape',
            'timestamp' => 'ILAB_Aws\Api\TimestampShape',
            'integer'   => 'ILAB_Aws\Api\Shape',
            'double'    => 'ILAB_Aws\Api\Shape',
            'float'     => 'ILAB_Aws\Api\Shape',
            'long'      => 'ILAB_Aws\Api\Shape',
            'string'    => 'ILAB_Aws\Api\Shape',
            'byte'      => 'ILAB_Aws\Api\Shape',
            'character' => 'ILAB_Aws\Api\Shape',
            'blob'      => 'ILAB_Aws\Api\Shape',
            'boolean'   => 'ILAB_Aws\Api\Shape'
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
