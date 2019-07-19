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
    "ilab-media-cloud-provider-settings" => [
        "title" => "Provider Settings",
        "dynamic" => true,
        "options" => [
            "mcloud-storage-s3-access-key" => [
                "title" => "Access Key",
                "display-order" => 1,
                "type" => "text-field",
            ],
            "mcloud-storage-s3-secret" => [
                "title" => "Secret",
                "display-order" => 2,
                "type" => "password",
            ],
	        "mcloud-storage-s3-use-credential-provider" => [
		        "title" => "Use Credential Provider",
		        "description" => "When this is enabled, Media Cloud will load your S3 credentials from the environment, <code> ~/.aws/credentials</code> or <code>~/.aws/config</code>.  When this is enabled, the <strong>Access Key</strong> and <strong>Secret</strong> values specified in these settings will be ignored.  This is an advanced option and <strong>should only be enabled if you know what you are doing.</strong>",
		        "display-order" => 3,
		        "type" => "checkbox",
		        "default" => false,
	        ],
            "mcloud-storage-s3-bucket" => [
                "title" => "Bucket",
                "description" => "The bucket you wish to store your media in.  Must not be blank.",
                "display-order" => 10,
                "type" => "text-field",
            ],
            "mcloud-storage-s3-region" => [
                "title" => "Region",
                "description" => "The region that your bucket is in.  Set to 'auto' to have Media Cloud automatically determine what region your bucket is in.",
                "display-order" => 11,
                "type" => "select",
                "options" => [
                    "auto" => "Automatic",
                    'us-east-2' => 'US East (Ohio)',
                    'us-east-1' => 'US East (N. Virginia)',
                    'us-west-1' => 'US West (N. California)',
                    'us-west-2' => 'US West (Oregon)',
                    'ca-central-1' => 'Canada (Central)',
	                'ap-east-1' => 'Asia Pacific (Hong Kong)',
                    'ap-south-1' => 'Asia Pacific (Mumbai)',
	                'ap-northeast-1' => 'Asia Pacific (Tokyo)',
	                'ap-northeast-2' => 'Asia Pacific (Seoul)',
	                'ap-northeast-3' => 'Asia Pacific (Osaka-Local)',
                    'ap-southeast-1' => 'Asia Pacific (Singapore)',
                    'ap-southeast-2' => 'Asia Pacific (Sydney)',
                    'eu-central-1' => 'EU (Frankfurt)',
                    'eu-west-1' => 'EU (Ireland)',
                    'eu-west-2' => 'EU (London)',
	                'eu-west-3' => 'EU (Paris)',
	                'eu-north-1' => 'EU (Stockholm)',
                    'sa-east-1' => 'South America (São Paulo)',
	                'cn-north-1' => 'China (Beijing)',
	                'cn-northwest-1' => 'China (Ningxia)',
                ],
            ],
            "mcloud-storage-s3-use-transfer-acceleration" => [
                "title" => "Use Transfer Acceleration",
                "description" => "Amazon S3 Transfer Acceleration enables fast, easy, and secure transfers of files over long distances between your client and an S3 bucket. Transfer Acceleration takes advantage of Amazon CloudFront’s globally distributed edge locations.  <strong>You must have it enabled on your bucket in the S3 console.</strong>",
                "display-order" => 12,
                "type" => "checkbox",
                "default" => false,
            ],
	        "mcloud-storage-use-presigned-urls" => [
		        "title" => "Use Pre-Signed URLs",
		        "description" => "Set to true to generate signed URLs that will expire within a specified time period.  You should use this if you've set the default ACL to private.",
		        "display-order" => 14,
		        "type" => "checkbox",
		        "default" => false,
	        ],
	        "mcloud-storage-presigned-expiration" => [
		        "title" => "Pre-Signed URL Expiration",
		        "description" => "The number of minutes the signed URL is valid for.",
		        "display-order" => 15,
		        "type" => "number",
		        "default" => 10,
	        ],
        ]
    ],
    "ilab-media-cloud-upload-handling" => [
        "title" => "Upload Handling",
        "dynamic" => true,
        "description" => "The following options control how the storage tool handles uploads.",
        "options" => [
            "mcloud-storage-privacy" => [
                "title" => "Upload Privacy ACL",
                "description" => "This will set the privacy for each upload.  You should leave it as <code>public-read</code> unless you are using Imgix.",
                "display-order" => 1,
                "type" => "select",
                "options" => [
                    "public-read" => "public-read",
                    "authenticated-read" => "authenticated-read"
                ],
            ],
            "mcloud-storage-cache-control" => [
                "title" => "Cache Control",
                "description" => "Sets the Cache-Control metadata for uploads, e.g. <code>public,max-age=2592000</code>.",
                "display-order" => 2,
                "type" => "text-field",
            ],
            "mcloud-storage-expires" => [
                "title" => "Content Expiration",
                "description" => "Sets the Expire metadata for uploads.  This is the number of minutes from the date of upload.",
                "display-order" => 3,
                "type" => "text-field",
            ],
        ]
    ],
];