<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/api/servicemanagement/v1/servicemanager.proto

namespace MediaCloud\Vendor\Google\Cloud\ServiceManagement\V1;
use MediaCloud\Vendor\Google\Protobuf\Internal\GPBType;
use MediaCloud\Vendor\Google\Protobuf\Internal\RepeatedField;
use MediaCloud\Vendor\Google\Protobuf\Internal\GPBUtil;

/**
 * Request message for 'CreateServiceRollout'
 *
 * Generated from protobuf message <code>google.api.servicemanagement.v1.CreateServiceRolloutRequest</code>
 */
class CreateServiceRolloutRequest extends \MediaCloud\Vendor\Google\Protobuf\Internal\Message
{
    /**
     * Required. The name of the service.  See the [overview](/service-management/overview)
     * for naming requirements.  For example: `example.googleapis.com`.
     *
     * Generated from protobuf field <code>string service_name = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $service_name = '';
    /**
     * Required. The rollout resource. The `service_name` field is output only.
     *
     * Generated from protobuf field <code>.google.api.servicemanagement.v1.Rollout rollout = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $rollout = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $service_name
     *           Required. The name of the service.  See the [overview](/service-management/overview)
     *           for naming requirements.  For example: `example.googleapis.com`.
     *     @type \MediaCloud\Vendor\Google\Cloud\ServiceManagement\V1\Rollout $rollout
     *           Required. The rollout resource. The `service_name` field is output only.
     * }
     */
    public function __construct($data = NULL) { \MediaCloud\Vendor\GPBMetadata\Google\Api\Servicemanagement\V1\Servicemanager::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The name of the service.  See the [overview](/service-management/overview)
     * for naming requirements.  For example: `example.googleapis.com`.
     *
     * Generated from protobuf field <code>string service_name = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getServiceName()
    {
        return $this->service_name;
    }

    /**
     * Required. The name of the service.  See the [overview](/service-management/overview)
     * for naming requirements.  For example: `example.googleapis.com`.
     *
     * Generated from protobuf field <code>string service_name = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setServiceName($var)
    {
        GPBUtil::checkString($var, True);
        $this->service_name = $var;

        return $this;
    }

    /**
     * Required. The rollout resource. The `service_name` field is output only.
     *
     * Generated from protobuf field <code>.google.api.servicemanagement.v1.Rollout rollout = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \MediaCloud\Vendor\Google\Cloud\ServiceManagement\V1\Rollout
     */
    public function getRollout()
    {
        return $this->rollout;
    }

    /**
     * Required. The rollout resource. The `service_name` field is output only.
     *
     * Generated from protobuf field <code>.google.api.servicemanagement.v1.Rollout rollout = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \MediaCloud\Vendor\Google\Cloud\ServiceManagement\V1\Rollout $var
     * @return $this
     */
    public function setRollout($var)
    {
        GPBUtil::checkMessage($var, \MediaCloud\Vendor\Google\Cloud\ServiceManagement\V1\Rollout::class);
        $this->rollout = $var;

        return $this;
    }

}
