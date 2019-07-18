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

namespace ILAB\MediaCloud\Vision\Driver\Rekognition;

use ILAB\MediaCloud\Storage\StorageManager;
use ILAB\MediaCloud\Tools\Storage\StorageTool;
use ILAB\MediaCloud\Tools\ToolsManager;
use ILAB\MediaCloud\Utilities\Environment;
use ILAB\MediaCloud\Utilities\Logging\Logger;
use ILAB\MediaCloud\Vision\VisionDriver;
use ILABAmazon\Credentials\CredentialProvider;
use ILABAmazon\Exception\AwsException;
use ILABAmazon\Rekognition\RekognitionClient;

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
        if (!$this->config->valid()) {
            $this->enabledError = "Configuration for Rekognition is invalid, probably from using old environment variables that are no longer supported or that have been renamed.";
            return false;
        }

        /** @var StorageTool $storageTool */
        $storageTool = ToolsManager::instance()->tools['storage'];
        if (!$storageTool || !$storageTool->enabled()) {
            $this->enabledError = "Cloud Storage must be enabled to use Rekognition.";
            return false;
        }

        if (StorageManager::driver() != 's3') {
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
		return ($this->config->detectLabels() || $this->config->detectFaces() || $this->config->detectExplicit() || $this->config->detectCelebrities());
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
            Logger::warning( "Post $postID is  missing 's3' metadata.", $meta);
            return $meta;
        }

        $s3 = $meta['s3'];

        if (!isset($s3['mime-type'])) {
            Logger::warning( "Post $postID is  missing 's3/mime-type' metadata.", $meta);
            return $meta;
        }

        $mime_parts = explode('/', $s3['mime-type']);
        if ((count($mime_parts)!=2) || ($mime_parts[0] != 'image') || (!in_array($mime_parts[1],['jpg','jpeg', 'png']))) {
            Logger::warning( "Post $postID is has invalid or missing mime-type.", $meta);
            return $meta;
        }

        Logger::info( "Processing Image Meta: $postID", $meta);

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

        if ($this->config->detectLabels()) {
            try {
                $result = $rekt->detectLabels([
                    'Attributes' => ['ALL'],
                    'Image' => [
                        'S3Object' => [
                            'Bucket' => $s3['bucket'],
                            'Name' => $s3['key']
                        ]
                    ],
                    'MinConfidence' => $this->config->detectLabelsConfidence()
                ]);

                $labels = $result->get('Labels');

                if (!empty($labels)) {
                    $tags = [];
                    foreach($labels as $label) {
                        if (!in_array(strtolower($label['Name']), $this->config->ignoredTags())) {
                            $tags[] = [
                                'tag' => $label['Name']
                            ];
                        }
                    }

                    $this->processTags($tags, $this->config->detectLabelsTax(), $postID);

                    Logger::info( 'Detect Labels', $tags);
                }
            } catch (AwsException $ex) {
                Logger::error( 'Detect Labels Error', [ 'exception' =>$ex->getMessage()]);
                return $meta;
            }
        }

        if ($this->config->detectExplicit()) {
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
                        if (!in_array(strtolower($label['Name']), $this->config->ignoredTags())) {
                            $tag = [
                                'tag' => $label['Name']
                            ];

                            if (!empty($label['ParentName'])) {
                                $tag['parent'] = $label['ParentName'];
                            }

                            $tags[] = $tag;
                        }
                    }

                    $this->processTags($tags, $this->config->detectExplicitTax(), $postID);
                }

                Logger::info( 'Detect Moderation Labels', $result->toArray());
            } catch (AwsException $ex) {
                Logger::error( 'Detect Moderation Error', [ 'exception' =>$ex->getMessage()]);
                return $meta;
            }
        }

        if ($this->config->detectCelebrities()) {
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
                        $ignoreCeleb = in_array(strtolower($celeb['Name']), $this->config->ignoredTags());

                        $face = $celeb['Face'];
                        if (!$ignoreCeleb) {
                            $face['celeb'] = $celeb['Name'];
                            $tags[] = [
                                'tag' => $celeb['Name']
                            ];
                        }

                        $allFaces[] = $face;
                    }

                    $this->processTags($tags, $this->config->detectCelebritiesTax(), $postID);
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

                Logger::info( 'Detect Celebrities', $result->toArray());
            } catch (AwsException $ex) {
                Logger::error( 'Detect Celebrities Error', [ 'exception' =>$ex->getMessage()]);
                return $meta;
            }
        }

        if ($this->config->detectFaces()) {
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

                Logger::info( 'Detect Faces', $result->toArray());
            } catch (AwsException $ex) {
                Logger::error( 'Detect Faces Error', [ 'exception' =>$ex->getMessage()]);
                return $meta;
            }
        }

        return $meta;
    }
}