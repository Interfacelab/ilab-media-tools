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
	        "mcloud-storage-wasabi-region" => [
		        "title" => "Region",
		        "description" => "The region that your bucket is in.  Set to 'auto' to have Media Cloud automatically determine what region your bucket is in.",
		        "display-order" => 11,
		        "type" => "select",
		        "options" => [
			        'us-east-1' => 'US East',
			        'us-west-1' => 'US West',
			        'eu-central-1' => 'EU',
		        ],
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