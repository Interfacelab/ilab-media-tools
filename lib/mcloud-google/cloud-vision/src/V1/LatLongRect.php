<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/vision/v1/image_annotator.proto

namespace MediaCloud\Vendor\Google\Cloud\Vision\V1;
use MediaCloud\Vendor\Google\Protobuf\Internal\GPBType;
use MediaCloud\Vendor\Google\Protobuf\Internal\RepeatedField;
use MediaCloud\Vendor\Google\Protobuf\Internal\GPBUtil;

/**
 * Rectangle determined by min and max `LatLng` pairs.
 *
 * Generated from protobuf message <code>google.cloud.vision.v1.LatLongRect</code>
 */
class LatLongRect extends \MediaCloud\Vendor\Google\Protobuf\Internal\Message
{
    /**
     * Min lat/long pair.
     *
     * Generated from protobuf field <code>.google.type.LatLng min_lat_lng = 1;</code>
     */
    private $min_lat_lng = null;
    /**
     * Max lat/long pair.
     *
     * Generated from protobuf field <code>.google.type.LatLng max_lat_lng = 2;</code>
     */
    private $max_lat_lng = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \MediaCloud\Vendor\Google\Type\LatLng $min_lat_lng
     *           Min lat/long pair.
     *     @type \MediaCloud\Vendor\Google\Type\LatLng $max_lat_lng
     *           Max lat/long pair.
     * }
     */
    public function __construct($data = NULL) { \MediaCloud\Vendor\GPBMetadata\Google\Cloud\Vision\V1\ImageAnnotator::initOnce();
        parent::__construct($data);
    }

    /**
     * Min lat/long pair.
     *
     * Generated from protobuf field <code>.google.type.LatLng min_lat_lng = 1;</code>
     * @return \MediaCloud\Vendor\Google\Type\LatLng|null
     */
    public function getMinLatLng()
    {
        return $this->min_lat_lng;
    }

    public function hasMinLatLng()
    {
        return isset($this->min_lat_lng);
    }

    public function clearMinLatLng()
    {
        unset($this->min_lat_lng);
    }

    /**
     * Min lat/long pair.
     *
     * Generated from protobuf field <code>.google.type.LatLng min_lat_lng = 1;</code>
     * @param \MediaCloud\Vendor\Google\Type\LatLng $var
     * @return $this
     */
    public function setMinLatLng($var)
    {
        GPBUtil::checkMessage($var, \MediaCloud\Vendor\Google\Type\LatLng::class);
        $this->min_lat_lng = $var;

        return $this;
    }

    /**
     * Max lat/long pair.
     *
     * Generated from protobuf field <code>.google.type.LatLng max_lat_lng = 2;</code>
     * @return \MediaCloud\Vendor\Google\Type\LatLng|null
     */
    public function getMaxLatLng()
    {
        return $this->max_lat_lng;
    }

    public function hasMaxLatLng()
    {
        return isset($this->max_lat_lng);
    }

    public function clearMaxLatLng()
    {
        unset($this->max_lat_lng);
    }

    /**
     * Max lat/long pair.
     *
     * Generated from protobuf field <code>.google.type.LatLng max_lat_lng = 2;</code>
     * @param \MediaCloud\Vendor\Google\Type\LatLng $var
     * @return $this
     */
    public function setMaxLatLng($var)
    {
        GPBUtil::checkMessage($var, \MediaCloud\Vendor\Google\Type\LatLng::class);
        $this->max_lat_lng = $var;

        return $this;
    }

}

