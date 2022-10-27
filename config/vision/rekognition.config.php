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
    "mcloud-vision-provider-settings" => [
        "title" => "Vision Provider Settings",
        "watch" => true,
        "dynamic" => true,
        "description" => "Supply any required credentials.  <strong>Note, these credentials are shared with Cloud Storage.</strong>  Changing credentials here will change your cloud storage credentials.  However, you can mix and match providers, meaning you can use Google Cloud Vision with Amazon S3 or DigitalOcean or any other cloud storage provider.  Amazon Rekognition, however, must be used with Amazon S3.",
        "options" => [
            "mcloud-storage-s3-access-key" => [
                "title" => "Access Key",
                "display-order" => 0,
                "type" => "text-field",
            ],
            "mcloud-storage-s3-secret" => [
                "title" => "Secret",
                "display-order" => 1,
                "type" => "password",
            ],
	        "mcloud-storage-s3-use-credential-provider" => [
		        "title" => "Use Credential Provider",
		        "description" => "When this is enabled, Media Cloud will load your S3 credentials from the environment, <code> ~/.aws/credentials</code> or <code>~/.aws/config</code>.  When this is enabled, the <strong>Access Key</strong> and <strong>Secret</strong> values specified in these settings will be ignored.  This is an advanced option and <strong>should only be enabled if you know what you are doing.</strong>",
		        "display-order" => 1,
		        "type" => "checkbox",
		        "default" => false,
	        ],
            "mcloud-storage-s3-bucket" => [
                "title" => "Bucket",
                "description" => "The bucket you wish to store your media in.  Must not be blank.",
                "display-order" => 2,
                "type" => "text-field",
            ],
            "mcloud-storage-s3-region" => [
                "title" => "Region",
                "description" => "The region that your bucket is in.  Set to 'auto' to have Media Cloud automatically determine what region your bucket is in.  Note that Rekognition is only available in <a target='_blank' href='https://aws.amazon.com/rekognition/faqs/'>select regions</a> and your S3 bucket must be in one of those regions.",
                "display-order" => 3,
                "type" => "select",
                "options" => [
                    "auto" => "Automatic",
                    'us-east-2' => 'US East (Ohio)',
                    'us-east-1' => 'US East (N. Virginia)',
                    'us-west-1' => 'US West (N. California)',
                    'us-west-2' => 'US West (Oregon)',
                    'ap-south-1' => 'Asia Pacific (Mumbai)',
                    'ap-northeast-2' => 'Asia Pacific (Seoul)',
                    'ap-southeast-1' => 'Asia Pacific (Singapore)',
                    'ap-southeast-2' => 'Asia Pacific (Sydney)',
                    'ap-northeast-1' => 'Asia Pacific (Tokyo)',
                    'eu-central-1' => 'EU (Frankfurt)',
                    'eu-west-1' => 'EU (Ireland)',
	                'eu-west-2' => 'EU (London)',
	                'us-gov-west-1' => 'AWS GovCloud (US)',
                ],
            ],
        ]
    ],
    "ilab-vision-options" => [
        "title" => "Vision Options",
        "dynamic" => true,
        "options" => [
	        "mcloud-vision-detect-labels" => [
		        "title" => "Detect Labels",
		        "description" => "Detects instances of real-world labels within an image (JPEG or PNG) provided as input. This includes objects like flower, tree, and table; events like wedding, graduation, and birthday party; and concepts like landscape, evening, and nature.",
		        "display-order" => 0,
		        "type" => "checkbox",
		        "default" => false
	        ],
	        "mcloud-vision-detect-labels-tax" => [
		        "title" => "Detect Labels Taxonomy",
		        "description" => "The taxonomy to apply the detected labels to.",
		        "display-order" => 1,
		        "type" => "select",
		        "default" => "post_tag",
		        "options" => 'attachmentTaxonomies'
	        ],
	        "mcloud-vision-detect-labels-confidence" => [
		        "title" => "Detect Labels Confidence",
		        "description" => "The minimum confidence (0-100) required to apply the returned label as tags.  Default is 70.",
		        "display-order" => 2,
		        "type" => "number",
		        "default" => 70
	        ],
	        "mcloud-vision-detect-moderation-labels" => [
		        "title" => "Detect Moderation Labels",
		        "description" => "Detects explicit or suggestive adult content in a specified JPEG or PNG format image. Use this to moderate images depending on your requirements. For example, you might want to filter images that contain nudity, but not images containing suggestive content.",
		        "display-order" => 3,
		        "type" => "checkbox",
		        "default" => false
	        ],
	        "mcloud-vision-detect-moderation-labels-tax" => [
		        "title" => "Detect Moderation Labels Taxonomy",
		        "description" => "The taxonomy to apply the detected moderation labels to.",
		        "display-order" => 4,
		        "type" => "select",
		        "default" => "post_tag",
		        "options" => 'attachmentTaxonomies'
	        ],
	        "mcloud-vision-detect-moderation-labels-confidence" => [
		        "title" => "Detect Moderation Labels Confidence",
		        "description" => "The minimum confidence (0-100) required to apply the returned label as tags.  Default is 70.",
		        "display-order" => 5,
		        "type" => "number",
		        "default" => 70
	        ],
	        "mcloud-vision-detect-celebrity" => [
                "title" => "Detect Celebrity Faces",
                "description" => "Detects celebrity faces in the image.  This will also detect non-celebrity faces.  If you use this option, you should not use the <em>Detect Faces<</em> option as either will overwrite the other.  Detected faces will be stored as additional metadata for the image.  If you are using Imgix, you can use this for cropping images centered on a face.",
                "display-order" => 6,
                "type" => "checkbox",
                "default" => false,
            ],
            "mcloud-vision-detect-celebrity-tax" => [
                "title" => "Detect Celebrity Faces Taxonomy",
                "description" => "The taxonomy to apply the detected moderation labels to.",
                "display-order" => 7,
                "type" => "select",
                "default" => "post_tag",
                "options" => 'attachmentTaxonomies',
            ],
	        "mcloud-vision-ignored-tags" => [
		        "title" => "Ignored Tags",
		        "description" => "Add a comma separated list of tags to ignore when parsing the results from the Rekognition API.",
		        "display-order" => 9,
		        "type" => "text-area"
	        ],
        ]
    ]
];