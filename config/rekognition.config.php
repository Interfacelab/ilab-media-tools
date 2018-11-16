<?php
// Copyright (c) 2016 Interfacelab LLC. All rights reserved.
//
// Released under the GPLv3 license
// http://www.gnu.org/licenses/gpl-3.0.html
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************

if (!defined('ABSPATH')) { header('Location: /'); die; }


return [
    "name" => "Rekognition",
	"title" => "Rekognition",
	"description" => "Uses Amazon's Rekognition AI service to automatically tag your uploaded images.",
	"class" => "ILAB\\MediaCloud\\Tools\\Rekognition\\RekognitionTool",
	"dependencies" => ["storage"],
	"env" => "ILAB_MEDIA_REKOGNITION_ENABLED",
    "batchTools" => [
        "\\ILAB\\MediaCloud\\Tools\\Rekognition\\Batch\\ImportRekognitionTool"
    ],
	"settings" => [
		"title" => "Rekognition Settings",
		"menu" => "Rekognition Settings",
		"only_when_enabled" => false,
		"options-page" => "media-tools-rekognition",
		"options-group" => "ilab-media-rekognition",
		"groups" => [
			"ilab-media-s3-rekognition-settings" => [
				"title" => "Rekognition Settings",
				"description" => "After you upload files to S3, those files will then be processed through the AWS Rekognition service.  The following settings control that process.  It's important to note that your S3 bucket must be in the same region that you are using the Rekognition service in.  Also, each option you select counts as 1 api call.  If you select detect faces, describe object <em>and</em> image moderation you will be using the service 3 times.",
				"options" => [
					"ilab-media-s3-rekognition-region" => [
						"title" => "Region",
						"description" => "Select which region you want to use for Rekognition.  <strong>Note:</strong> the S3 bucket you are uploading to <strong>must</strong> be in the same region.",
						"type" => "select",
						"default" => "none",
						"options" => [
                            "us-east-1" => "US East (Northern Virginia)",
                            "us-east-2" => "US East (Ohio)",
							"us-west-2" => "US West (Oregon)",
                            "eu-west-1" => "EU (Ireland)",
                            "ap-south-1" => "Asia Pacific (Mumbai)",
                            "ap-northeast-2" => "Asia Pacific (Seoul)",
                            "ap-southeast-2" => "Asia Pacific (Sydney)",
                            "ap-northeast-1" => "Asia Pacific (Tokyo)",
							"us-gov-west-1" => "AWS GovCloud (US)"
						]
					],
					"ilab-media-s3-rekognition-detect-labels" => [
						"title" => "Detect Labels",
						"description" => "Detects instances of real-world labels within an image (JPEG or PNG) provided as input. This includes objects like flower, tree, and table; events like wedding, graduation, and birthday party; and concepts like landscape, evening, and nature.",
						"type" => "checkbox",
						"default" => false
					],
					"ilab-media-s3-rekognition-detect-labels-tax" => [
						"title" => "Detect Labels Taxonomy",
						"description" => "The taxonomy to apply the detected labels to.",
						"type" => "select",
						"default" => "post_tag",
						"options" => 'attachmentTaxonomies'
					],
					"ilab-media-s3-rekognition-detect-labels-confidence" => [
						"title" => "Detect Labels Confidence",
						"description" => "The minimum confidence (0-100) required to apply the returned label as tags.  Default is 70.",
						"type" => "number",
						"default" => 70
					],
					"ilab-media-s3-rekognition-detect-moderation-labels" => [
						"title" => "Detect Moderation Labels",
						"description" => "Detects explicit or suggestive adult content in a specified JPEG or PNG format image. Use this to moderate images depending on your requirements. For example, you might want to filter images that contain nudity, but not images containing suggestive content.",
						"type" => "checkbox",
						"default" => false
					],
					"ilab-media-s3-rekognition-detect-moderation-labels-tax" => [
						"title" => "Detect Moderation Labels Taxonomy",
						"description" => "The taxonomy to apply the detected moderation labels to.",
						"type" => "select",
						"default" => "post_tag",
						"options" => 'attachmentTaxonomies'
					],
					"ilab-media-s3-rekognition-detect-moderation-labels-confidence" => [
						"title" => "Detect Moderation Labels Confidence",
						"description" => "The minimum confidence (0-100) required to apply the returned label as tags.  Default is 70.",
						"type" => "number",
						"default" => 70
					],
					"ilab-media-s3-rekognition-detect-celebrity" => [
						"title" => "Detect Celebrity Faces",
						"description" => "Detects celebrity faces in the image.  This will also detect non-celebrity faces.  If you use this option, you should not use the <em>Detect Faces<</em> option as either will overwrite the other.  Detected faces will be stored as additional metadata for the image.  If you are using Imgix, you can use this for cropping images centered on a face.",
						"type" => "checkbox",
						"default" => false
					],
					"ilab-media-s3-rekognition-detect-celebrity-tax" => [
						"title" => "Detect Celebrity Faces Taxonomy",
						"description" => "The taxonomy to apply the detected moderation labels to.",
						"type" => "select",
						"default" => "post_tag",
						"options" => 'attachmentTaxonomies'
					],
					"ilab-media-s3-rekognition-detect-faces" => [
						"title" => "Detect Faces",
						"description" => "Detects faces in the image.  If you use this option, you should not use the <em>Detect Celebrity Faces<</em> option as either will overwrite the other.  Detected faces will be stored as additional metadata for the image.  If you are using Imgix, you can use this for cropping images centered on a face.",
						"type" => "checkbox",
						"default" => false
					],
					"ilab-media-s3-rekognition-ignored-tags" => [
						"title" => "Ignored Tags",
						"description" => "Add a comma separated list of tags to ignore when parsing the results from the Rekognition API.",
						"type" => "text-area"
					],
				]
			]
		]
	]
];