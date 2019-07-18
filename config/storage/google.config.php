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
            "mcloud-storage-google-credentials" => [
                "title" => "Credentials",
                "description" => "To create the appropriate credentials, <a target='_blank' href='https://cloud.google.com/video-intelligence/docs/common/auth#set_up_a_service_account'>follow this tutorial</a>.  Once you've created the credentials and downloaded the resulting JSON, copy and paste the <strong>contents</strong> of the JSON file into this text field.",
                "display-order" => 1,
                "type" => "text-area",
            ],
            "mcloud-storage-google-bucket" => [
                "title" => "Bucket",
                "description" => "The bucket you wish to store your media in.  Must not be blank.",
                "display-order" => 2,
                "type" => "text-field",
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