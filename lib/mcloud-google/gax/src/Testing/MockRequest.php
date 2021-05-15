<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: ApiCore/Testing/mocks.proto

namespace MediaCloud\Vendor\Google\ApiCore\Testing;
use MediaCloud\Vendor\Google\Protobuf\Internal\GPBType;
use MediaCloud\Vendor\Google\Protobuf\Internal\RepeatedField;
use MediaCloud\Vendor\Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>google.apicore.testing.MockRequest</code>
 */
class MockRequest extends \MediaCloud\Vendor\Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>string page_token = 1;</code>
     */
    private $page_token = '';
    /**
     * Generated from protobuf field <code>uint64 page_size = 2;</code>
     */
    private $page_size = 0;

    public function __construct() { \MediaCloud\Vendor\GPBMetadata\ApiCore\Testing\Mocks::initOnce();
        parent::__construct();
    }

    /**
     * Generated from protobuf field <code>string page_token = 1;</code>
     * @return string
     */
    public function getPageToken()
    {
        return $this->page_token;
    }

    /**
     * Generated from protobuf field <code>string page_token = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setPageToken($var)
    {
        GPBUtil::checkString($var, True);
        $this->page_token = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>uint64 page_size = 2;</code>
     * @return int|string
     */
    public function getPageSize()
    {
        return $this->page_size;
    }

    /**
     * Generated from protobuf field <code>uint64 page_size = 2;</code>
     * @param int|string $var
     * @return $this
     */
    public function setPageSize($var)
    {
        GPBUtil::checkUint64($var);
        $this->page_size = $var;

        return $this;
    }

}
