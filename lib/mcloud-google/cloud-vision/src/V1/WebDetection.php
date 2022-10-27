<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/vision/v1/web_detection.proto

namespace MediaCloud\Vendor\Google\Cloud\Vision\V1;
use MediaCloud\Vendor\Google\Protobuf\Internal\GPBType;
use MediaCloud\Vendor\Google\Protobuf\Internal\RepeatedField;
use MediaCloud\Vendor\Google\Protobuf\Internal\GPBUtil;

/**
 * Relevant information for the image from the Internet.
 *
 * Generated from protobuf message <code>google.cloud.vision.v1.WebDetection</code>
 */
class WebDetection extends \MediaCloud\Vendor\Google\Protobuf\Internal\Message
{
    /**
     * Deduced entities from similar images on the Internet.
     *
     * Generated from protobuf field <code>repeated .google.cloud.vision.v1.WebDetection.WebEntity web_entities = 1;</code>
     */
    private $web_entities;
    /**
     * Fully matching images from the Internet.
     * Can include resized copies of the query image.
     *
     * Generated from protobuf field <code>repeated .google.cloud.vision.v1.WebDetection.WebImage full_matching_images = 2;</code>
     */
    private $full_matching_images;
    /**
     * Partial matching images from the Internet.
     * Those images are similar enough to share some key-point features. For
     * example an original image will likely have partial matching for its crops.
     *
     * Generated from protobuf field <code>repeated .google.cloud.vision.v1.WebDetection.WebImage partial_matching_images = 3;</code>
     */
    private $partial_matching_images;
    /**
     * Web pages containing the matching images from the Internet.
     *
     * Generated from protobuf field <code>repeated .google.cloud.vision.v1.WebDetection.WebPage pages_with_matching_images = 4;</code>
     */
    private $pages_with_matching_images;
    /**
     * The visually similar image results.
     *
     * Generated from protobuf field <code>repeated .google.cloud.vision.v1.WebDetection.WebImage visually_similar_images = 6;</code>
     */
    private $visually_similar_images;
    /**
     * The service's best guess as to the topic of the request image.
     * Inferred from similar images on the open web.
     *
     * Generated from protobuf field <code>repeated .google.cloud.vision.v1.WebDetection.WebLabel best_guess_labels = 8;</code>
     */
    private $best_guess_labels;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \MediaCloud\Vendor\Google\Cloud\Vision\V1\WebDetection\WebEntity[]|\Google\Protobuf\Internal\RepeatedField $web_entities
     *           Deduced entities from similar images on the Internet.
     *     @type \MediaCloud\Vendor\Google\Cloud\Vision\V1\WebDetection\WebImage[]|\Google\Protobuf\Internal\RepeatedField $full_matching_images
     *           Fully matching images from the Internet.
     *           Can include resized copies of the query image.
     *     @type \MediaCloud\Vendor\Google\Cloud\Vision\V1\WebDetection\WebImage[]|\Google\Protobuf\Internal\RepeatedField $partial_matching_images
     *           Partial matching images from the Internet.
     *           Those images are similar enough to share some key-point features. For
     *           example an original image will likely have partial matching for its crops.
     *     @type \MediaCloud\Vendor\Google\Cloud\Vision\V1\WebDetection\WebPage[]|\Google\Protobuf\Internal\RepeatedField $pages_with_matching_images
     *           Web pages containing the matching images from the Internet.
     *     @type \MediaCloud\Vendor\Google\Cloud\Vision\V1\WebDetection\WebImage[]|\Google\Protobuf\Internal\RepeatedField $visually_similar_images
     *           The visually similar image results.
     *     @type \MediaCloud\Vendor\Google\Cloud\Vision\V1\WebDetection\WebLabel[]|\Google\Protobuf\Internal\RepeatedField $best_guess_labels
     *           The service's best guess as to the topic of the request image.
     *           Inferred from similar images on the open web.
     * }
     */
    public function __construct($data = NULL) { \MediaCloud\Vendor\GPBMetadata\Google\Cloud\Vision\V1\WebDetection::initOnce();
        parent::__construct($data);
    }

    /**
     * Deduced entities from similar images on the Internet.
     *
     * Generated from protobuf field <code>repeated .google.cloud.vision.v1.WebDetection.WebEntity web_entities = 1;</code>
     * @return \MediaCloud\Vendor\Google\Protobuf\Internal\RepeatedField
     */
    public function getWebEntities()
    {
        return $this->web_entities;
    }

    /**
     * Deduced entities from similar images on the Internet.
     *
     * Generated from protobuf field <code>repeated .google.cloud.vision.v1.WebDetection.WebEntity web_entities = 1;</code>
     * @param \MediaCloud\Vendor\Google\Cloud\Vision\V1\WebDetection\WebEntity[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setWebEntities($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \MediaCloud\Vendor\Google\Protobuf\Internal\GPBType::MESSAGE, \MediaCloud\Vendor\Google\Cloud\Vision\V1\WebDetection\WebEntity::class);
        $this->web_entities = $arr;

        return $this;
    }

    /**
     * Fully matching images from the Internet.
     * Can include resized copies of the query image.
     *
     * Generated from protobuf field <code>repeated .google.cloud.vision.v1.WebDetection.WebImage full_matching_images = 2;</code>
     * @return \MediaCloud\Vendor\Google\Protobuf\Internal\RepeatedField
     */
    public function getFullMatchingImages()
    {
        return $this->full_matching_images;
    }

    /**
     * Fully matching images from the Internet.
     * Can include resized copies of the query image.
     *
     * Generated from protobuf field <code>repeated .google.cloud.vision.v1.WebDetection.WebImage full_matching_images = 2;</code>
     * @param \MediaCloud\Vendor\Google\Cloud\Vision\V1\WebDetection\WebImage[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setFullMatchingImages($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \MediaCloud\Vendor\Google\Protobuf\Internal\GPBType::MESSAGE, \MediaCloud\Vendor\Google\Cloud\Vision\V1\WebDetection\WebImage::class);
        $this->full_matching_images = $arr;

        return $this;
    }

    /**
     * Partial matching images from the Internet.
     * Those images are similar enough to share some key-point features. For
     * example an original image will likely have partial matching for its crops.
     *
     * Generated from protobuf field <code>repeated .google.cloud.vision.v1.WebDetection.WebImage partial_matching_images = 3;</code>
     * @return \MediaCloud\Vendor\Google\Protobuf\Internal\RepeatedField
     */
    public function getPartialMatchingImages()
    {
        return $this->partial_matching_images;
    }

    /**
     * Partial matching images from the Internet.
     * Those images are similar enough to share some key-point features. For
     * example an original image will likely have partial matching for its crops.
     *
     * Generated from protobuf field <code>repeated .google.cloud.vision.v1.WebDetection.WebImage partial_matching_images = 3;</code>
     * @param \MediaCloud\Vendor\Google\Cloud\Vision\V1\WebDetection\WebImage[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setPartialMatchingImages($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \MediaCloud\Vendor\Google\Protobuf\Internal\GPBType::MESSAGE, \MediaCloud\Vendor\Google\Cloud\Vision\V1\WebDetection\WebImage::class);
        $this->partial_matching_images = $arr;

        return $this;
    }

    /**
     * Web pages containing the matching images from the Internet.
     *
     * Generated from protobuf field <code>repeated .google.cloud.vision.v1.WebDetection.WebPage pages_with_matching_images = 4;</code>
     * @return \MediaCloud\Vendor\Google\Protobuf\Internal\RepeatedField
     */
    public function getPagesWithMatchingImages()
    {
        return $this->pages_with_matching_images;
    }

    /**
     * Web pages containing the matching images from the Internet.
     *
     * Generated from protobuf field <code>repeated .google.cloud.vision.v1.WebDetection.WebPage pages_with_matching_images = 4;</code>
     * @param \MediaCloud\Vendor\Google\Cloud\Vision\V1\WebDetection\WebPage[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setPagesWithMatchingImages($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \MediaCloud\Vendor\Google\Protobuf\Internal\GPBType::MESSAGE, \MediaCloud\Vendor\Google\Cloud\Vision\V1\WebDetection\WebPage::class);
        $this->pages_with_matching_images = $arr;

        return $this;
    }

    /**
     * The visually similar image results.
     *
     * Generated from protobuf field <code>repeated .google.cloud.vision.v1.WebDetection.WebImage visually_similar_images = 6;</code>
     * @return \MediaCloud\Vendor\Google\Protobuf\Internal\RepeatedField
     */
    public function getVisuallySimilarImages()
    {
        return $this->visually_similar_images;
    }

    /**
     * The visually similar image results.
     *
     * Generated from protobuf field <code>repeated .google.cloud.vision.v1.WebDetection.WebImage visually_similar_images = 6;</code>
     * @param \MediaCloud\Vendor\Google\Cloud\Vision\V1\WebDetection\WebImage[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setVisuallySimilarImages($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \MediaCloud\Vendor\Google\Protobuf\Internal\GPBType::MESSAGE, \MediaCloud\Vendor\Google\Cloud\Vision\V1\WebDetection\WebImage::class);
        $this->visually_similar_images = $arr;

        return $this;
    }

    /**
     * The service's best guess as to the topic of the request image.
     * Inferred from similar images on the open web.
     *
     * Generated from protobuf field <code>repeated .google.cloud.vision.v1.WebDetection.WebLabel best_guess_labels = 8;</code>
     * @return \MediaCloud\Vendor\Google\Protobuf\Internal\RepeatedField
     */
    public function getBestGuessLabels()
    {
        return $this->best_guess_labels;
    }

    /**
     * The service's best guess as to the topic of the request image.
     * Inferred from similar images on the open web.
     *
     * Generated from protobuf field <code>repeated .google.cloud.vision.v1.WebDetection.WebLabel best_guess_labels = 8;</code>
     * @param \MediaCloud\Vendor\Google\Cloud\Vision\V1\WebDetection\WebLabel[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setBestGuessLabels($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \MediaCloud\Vendor\Google\Protobuf\Internal\GPBType::MESSAGE, \MediaCloud\Vendor\Google\Cloud\Vision\V1\WebDetection\WebLabel::class);
        $this->best_guess_labels = $arr;

        return $this;
    }

}

