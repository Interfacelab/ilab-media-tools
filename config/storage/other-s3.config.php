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
                    'ap-south-1' => 'Asia Pacific (Mumbai)',
                    'ap-northeast-2' => 'Asia Pacific (Seoul)',
                    'ap-southeast-1' => 'Asia Pacific (Singapore)',
                    'ap-southeast-2' => 'Asia Pacific (Sydney)',
                    'ap-northeast-1' => 'Asia Pacific (Tokyo)',
                    'eu-central-1' => 'EU (Frankfurt)',
                    'eu-west-1' => 'EU (Ireland)',
                    'eu-west-2' => 'EU (London)',
                    'sa-east-1' => 'South America (São Paulo)',
                ],
            ],
            "mcloud-storage-s3-endpoint" => [
                "title" => "Custom Endpoint",
                "description" => "Some S3 compatible services use a custom API endpoint URL or server name.  For example, with a DigitalOcean space in NYC-3 region, this value would be <code>nyc3.digitaloceanspaces.com</code>",
                "display-order" => 12,
                "type" => "text-field",
            ],
            "mcloud-storage-s3-use-path-style-endpoint" => [
                "title" => "Path Style Endpoint",
                "description" => "Set to true to send requests to an S3 path style endpoint by default.  If this is unchecked, requests will be sent to something like <code>https://your-bucket-name.your-s3-server.com/</code>, which is likely not to work.",
                "display-order" => 13,
                "type" => "checkbox",
                "default" => true,
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
                "type" => "select",
                "options" => [
                    "public-read" => "public-read",
                    "authenticated-read" => "authenticated-read"
                ],
            ],
            "mcloud-storage-cache-control" => [
                "title" => "Cache Control",
                "description" => "Sets the Cache-Control metadata for uploads, e.g. <code>public,max-age=2592000</code>.",
                "type" => "text-field",
            ],
            "mcloud-storage-expires" => [
                "title" => "Content Expiration",
                "description" => "Sets the Expire metadata for uploads.  This is the number of minutes from the date of upload.",
                "type" => "text-field",
            ],
        ]
    ],
];