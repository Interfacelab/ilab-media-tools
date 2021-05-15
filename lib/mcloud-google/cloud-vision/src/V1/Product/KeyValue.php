<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/vision/v1/product_search_service.proto

namespace MediaCloud\Vendor\Google\Cloud\Vision\V1\Product;
use MediaCloud\Vendor\Google\Protobuf\Internal\GPBType;
use MediaCloud\Vendor\Google\Protobuf\Internal\RepeatedField;
use MediaCloud\Vendor\Google\Protobuf\Internal\GPBUtil;

/**
 * A product label represented as a key-value pair.
 *
 * Generated from protobuf message <code>google.cloud.vision.v1.Product.KeyValue</code>
 */
class KeyValue extends \MediaCloud\Vendor\Google\Protobuf\Internal\Message
{
    /**
     * The key of the label attached to the product. Cannot be empty and cannot
     * exceed 128 bytes.
     *
     * Generated from protobuf field <code>string key = 1;</code>
     */
    private $key = '';
    /**
     * The value of the label attached to the product. Cannot be empty and
     * cannot exceed 128 bytes.
     *
     * Generated from protobuf field <code>string value = 2;</code>
     */
    private $value = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $key
     *           The key of the label attached to the product. Cannot be empty and cannot
     *           exceed 128 bytes.
     *     @type string $value
     *           The value of the label attached to the product. Cannot be empty and
     *           cannot exceed 128 bytes.
     * }
     */
    public function __construct($data = NULL) { \MediaCloud\Vendor\GPBMetadata\Google\Cloud\Vision\V1\ProductSearchService::initOnce();
        parent::__construct($data);
    }

    /**
     * The key of the label attached to the product. Cannot be empty and cannot
     * exceed 128 bytes.
     *
     * Generated from protobuf field <code>string key = 1;</code>
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * The key of the label attached to the product. Cannot be empty and cannot
     * exceed 128 bytes.
     *
     * Generated from protobuf field <code>string key = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setKey($var)
    {
        GPBUtil::checkString($var, True);
        $this->key = $var;

        return $this;
    }

    /**
     * The value of the label attached to the product. Cannot be empty and
     * cannot exceed 128 bytes.
     *
     * Generated from protobuf field <code>string value = 2;</code>
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * The value of the label attached to the product. Cannot be empty and
     * cannot exceed 128 bytes.
     *
     * Generated from protobuf field <code>string value = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setValue($var)
    {
        GPBUtil::checkString($var, True);
        $this->value = $var;

        return $this;
    }

}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(KeyValue::class, \MediaCloud\Vendor\Google\Cloud\Vision\V1\Product_KeyValue::class);
