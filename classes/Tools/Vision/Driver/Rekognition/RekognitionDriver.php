<?php
// Copyright (c) 2016 Interfacelab LLC. All rights reserved.
//
// Released under the GPLv3 license
// http://www.gnu.org/licenses/gpl-3.0.html
//
// Uses code from:
// Persist Admin Notices Dismissal
// by Agbonghama Collins and Andy Fragen
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************

namespace MediaCloud\Plugin\Tools\Vision\Driver\Rekognition;

use MediaCloud\Plugin\Tools\Storage\StorageToolSettings;
use MediaCloud\Plugin\Tools\Storage\StorageTool;
use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Utilities\Environment;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use MediaCloud\Plugin\Tools\Vision\VisionDriver;
use MediaCloud\Vendor\Aws\Credentials\CredentialProvider;
use MediaCloud\Vendor\Aws\Exception\AwsException;
use MediaCloud\Vendor\Aws\Rekognition\RekognitionClient;

if (!defined('ABSPATH')) { header('Location: /'); die; }

class RekognitionDriver extends VisionDriver {
    /** @var string|null */
    protected $key = null;

	/** @var string|null */
	protected $secret = null;

	/*** @var bool */
	protected $useCredentialProvider = false;

    /** @var string|null */
    protected $region;

    /** @var null|string */
    private $enabledError = null;

    private static $validRegions = [
        "us-east-1",
        "us-east-2",
        "us-west-2",
	    "eu-west-1",
	    "eu-west-2",
        "ap-south-1",
	    "ap-northeast-1",
	    "ap-northeast-2",
	    "ap-southeast-1",
	    "ap-southeast-2",
	    "eu-central-1",
        "us-gov-west-1"
    ];

    public function __construct() {
        parent::__construct();

        $this->bucket = Environment::Option('mcloud-storage-s3-bucket', [
            'ILAB_AWS_S3_BUCKET',
            'ILAB_CLOUD_BUCKET'
        ]);

        $this->key = Environment::Option('mcloud-storage-s3-access-key', [
            'ILAB_AWS_S3_ACCESS_KEY',
            'ILAB_CLOUD_ACCESS_KEY'
        ]);

        $this->secret = Environment::Option('mcloud-storage-s3-secret', [
            'ILAB_AWS_S3_ACCESS_SECRET',
            'ILAB_CLOUD_ACCESS_SECRET'
        ]);

        $region = Environment::Option('mcloud-storage-s3-region', [
            'ILAB_AWS_S3_REGION',
            'ILAB_CLOUD_REGION'
        ], 'auto');


	    $this->useCredentialProvider = Environment::Option('mcloud-storage-s3-use-credential-provider', [
		    'ILAB_AWS_S3_USE_CREDENTIAL_PROVIDER',
		    'ILAB_CLOUD_USE_CREDENTIAL_PROVIDER'
	    ], false);

        if($region != 'auto') {
            $this->region = $region;
        }
    }

    /**
     * Insures that all the configuration settings are valid and that the vision api is enabled.
     * @return bool
     */
    public function enabled() {
        /** @var StorageTool $storageTool */
        $storageTool = ToolsManager::instance()->tools['storage'];
        if (!$storageTool || !$storageTool->enabled()) {
            $this->enabledError = "Cloud Storage must be enabled to use Rekognition.";
            return false;
        }

        if (StorageToolSettings::driver() != 's3') {
            $this->enabledError = "You must use AWS S3 Cloud Storage to use Rekognition.";
            return false;
        }

        $requiredS3Settings = ((!empty($this->key) && !empty($this->secret)) || !empty($this->useCredentialProvider));
        if (empty($this->bucket) || empty($requiredS3Settings)) {
            $this->enabledError = "Missing required S3 settings.";
            return false;
        }

        if (!in_array($this->region, static::$validRegions)) {
            $this->enabledError = "The AWS region is not valid for Rekognition services.  The region is {$this->region} but valid Rekognition regions are ".implode(", ", static::$validRegions);
            return false;
        }

        $this->enabledError = null;
        return $this->minimumOptionsEnabled();
    }

	public function minimumOptionsEnabled() {
		return ($this->settings->detectLabels || $this->settings->detectFaces || $this->settings->detectExplicit || $this->settings->detectCelebrities);
	}

    /**
     * If the driver isn't enabled, this returns the error message as to why
     * @return string|null
     */
    public function enabledError() {
        return $this->enabledError;
    }

    /**
     * Processes the image through the driver's vision API
     * @param $postID
     * @param $meta
     * @return array
     */
    public function processImage($postID, $meta) {
        if (!$this->enabled()) {
            return $meta;
        }

        if (!isset($meta['s3'])) {
            Logger::warning( "Post $postID is  missing 's3' metadata.", $meta, __METHOD__, __LINE__);
            return $meta;
        }

        $s3 = $meta['s3'];

	    if (!isset($s3['mime-type'])) {
		    Logger::warning( "Post $postID is  missing 's3/mime-type' metadata.", $meta, __METHOD__, __LINE__);
		    $mime = get_post_mime_type($postID);

		    if (isset($meta['original_image_s3']['mime-type'])) {
			    Logger::warning( "Applying metadata from original image.", [], __METHOD__, __LINE__);
			    $s3['mime-type'] = $meta['original_image_s3']['mime-type'];
		    } else if (!empty($mime)) {
			    Logger::warning( "Applying metadata from post via get_post_mime_type.", [], __METHOD__, __LINE__);
			    $s3['mime-type'] = $mime;
		    } else {
			    return $meta;
		    }
	    }

        $mime_parts = explode('/', $s3['mime-type']);
        if ((count($mime_parts)!=2) || ($mime_parts[0] != 'image') || (!in_array($mime_parts[1],['jpg','jpeg', 'png']))) {
            Logger::warning( "Post $postID is has invalid or missing mime-type.", $meta, __METHOD__, __LINE__);
            return $meta;
        }

        Logger::info( "Processing Image Meta: $postID", $meta, __METHOD__, __LINE__);

	    if($this->useCredentialProvider) {
		    $config = [
			    'version' => 'latest',
			    'credentials' => CredentialProvider::defaultProvider(),
			    'region' => $this->region
		    ];
	    } else {
		    $config = [
			    'version' => 'latest',
			    'credentials' => [
				    'key' => $this->key,
				    'secret' => $this->secret
			    ],
			    'region' => $this->region
		    ];
	    }

        $rekt = new RekognitionClient($config);

	    $tagsList = [];
        if ($this->settings->detectLabels) {
            try {
                $result = $rekt->detectLabels([
                    'Attributes' => ['ALL'],
                    'Image' => [
                        'S3Object' => [
                            'Bucket' => $s3['bucket'],
                            'Name' => $s3['key']
                        ]
                    ],
                    'MinConfidence' => $this->settings->detectLabelsConfidence
                ]);

                $labels = $result->get('Labels');

                if (!empty($labels)) {
                    $tags = [];
                    foreach($labels as $label) {
                        if (!in_array(strtolower($label['Name']), $this->settings->ignoredTags)) {
                            $tags[] = [
                                'tag' => $label['Name']
                            ];
                        }
                    }

                    if (!isset($tagsList[$this->settings->detectLabelsTax])) {
                    	$tagsList[$this->settings->detectLabelsTax] = [];
                    }

                    $this->processTags($tags, $this->settings->detectLabelsTax, $postID, $tagsList[$this->settings->detectLabelsTax]);

                    Logger::info( 'Detect Labels', $tags, __METHOD__, __LINE__);
                }
            } catch (AwsException $ex) {
                Logger::error( 'Detect Labels Error', [ 'exception' =>$ex->getMessage()], __METHOD__, __LINE__);
                return $meta;
            }
        }

        if ($this->settings->detectExplicit) {
            try {
                $result = $rekt->detectModerationLabels([
                    'Image' => [
                        'S3Object' => [
                            'Bucket' => $s3['bucket'],
                            'Name' => $s3['key']
                        ]
                    ],
                    //					                                        'MinConfidence' => $this->detectExplicitConfidence
                ]);

                $labels = $result->get('ModerationLabels');
                if (!empty($labels)) {
                    $tags = [];
                    foreach($labels as $label) {
                        if (!in_array(strtolower($label['Name']), $this->settings->ignoredTags)) {
                            $tag = [
                                'tag' => $label['Name']
                            ];

                            if (!empty($label['ParentName'])) {
                                $tag['parent'] = $label['ParentName'];
                            }

                            $tags[] = $tag;
                        }
                    }

	                if (!isset($tagsList[$this->settings->detectExplicitTax])) {
		                $tagsList[$this->settings->detectExplicitTax] = [];
	                }

	                $this->processTags($tags, $this->settings->detectExplicitTax, $postID, $tagsList[$this->settings->detectExplicitTax]);
                }

                Logger::info( 'Detect Moderation Labels', $result->toArray(), __METHOD__, __LINE__);
            } catch (AwsException $ex) {
                Logger::error( 'Detect Moderation Error', [ 'exception' =>$ex->getMessage()], __METHOD__, __LINE__);
                return $meta;
            }
        }

        if ($this->settings->detectCelebrities) {
            try {
                $result = $rekt->recognizeCelebrities([
                    'Attributes' => ['ALL'],
                    'Image' => [
                        'S3Object' => [
                            'Bucket' => $s3['bucket'],
                            'Name' => $s3['key']
                        ]
                    ]
                ]);

                $allFaces = [];

                $celebs = $result->get('CelebrityFaces');
                if (!empty($celebs)) {
                    $tags = [];

                    foreach($celebs as $celeb) {
                        $ignoreCeleb = in_array(strtolower($celeb['Name']), $this->settings->ignoredTags);

                        $face = $celeb['Face'];
                        if (!$ignoreCeleb) {
                            $face['celeb'] = $celeb['Name'];
                            $tags[] = [
                                'tag' => $celeb['Name']
                            ];
                        }

                        $allFaces[] = $face;
                    }


	                if (!isset($tagsList[$this->settings->detectCelebritiesTax])) {
		                $tagsList[$this->settings->detectCelebritiesTax] = [];
	                }

                    $this->processTags($tags, $this->settings->detectCelebritiesTax, $postID, $tagsList[$this->settings->detectCelebritiesTax]);
                }

                $otherFaces = $result->get('UnrecognizedFaces');
                if (!empty($otherFaces)) {
                    foreach($otherFaces as $face) {
                        $allFaces[] = $face;
                    }
                }

                if (!empty($allFaces)) {
                    $meta['faces'] = $allFaces;
                }

                Logger::info( 'Detect Celebrities', $result->toArray(), __METHOD__, __LINE__);
            } catch (AwsException $ex) {
                Logger::error( 'Detect Celebrities Error', [ 'exception' =>$ex->getMessage()], __METHOD__, __LINE__);
                return $meta;
            }
        }

        if ($this->settings->detectFaces) {
            try {
                $result = $rekt->detectFaces([
                    'Attributes' => ['ALL'],
                    'Image' => [
                        'S3Object' => [
                            'Bucket' => $s3['bucket'],
                            'Name' => $s3['key']
                        ]
                    ]
                ]);

                $faces = $result->get('FaceDetails');
                if (!empty($faces)) {
                    $meta['faces'] = $faces;
                }

                Logger::info( 'Detect Faces', $result->toArray(), __METHOD__, __LINE__);
            } catch (AwsException $ex) {
                Logger::error( 'Detect Faces Error', [ 'exception' =>$ex->getMessage()], __METHOD__, __LINE__);
                return $meta;
            }
        }

        return $meta;
    }
}
