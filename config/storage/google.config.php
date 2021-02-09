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
	        "mcloud-storage-bucket-policy-only" => [
		        "title" => "Use Bucket Policy Only",
		        "description" => "Set to true to when using a bucket which has the 'Bucket Policy Only' flag enabled.  See <a target='_blank' href='https://cloud.google.com/storage/docs/bucket-policy-only'>this documentation</a> for more information.  Also, make sure to make the bucket public, as specified in <a target-'_blank' href='https://cloud.google.com/storage/docs/access-control/making-data-public#buckets'>this documentation</a>.",
		        "display-order" => 16,
		        "type" => "checkbox",
		        "default" => false,
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
	            "description" => "This will set the privacy for each upload.  You should leave it as <code>public-read</code> unless you are using Imgix.  If you set the <strong>Bucket Policy Only</strong> flag, this will have no effect.",
	            "display-order" => 1,
                "type" => "select",
                "options" => [
                    "public-read" => "Public",
	                "authenticated-read" => "Authenticated Read",
	                "private" => "Private",
                ],
            ],
	        "mcloud-storage-advanced-privacy" => [
		        "title" => "Advanced Privacy",
		        "description" => "",
		        "display-order" => 2,
		        "type" => "advanced-privacy",
		        "plan" => "pro"
	        ],
            "mcloud-storage-cache-control" => [
                "title" => "Cache Control",
                "description" => "Sets the Cache-Control metadata for uploads, e.g. <code>public,max-age=2592000</code>.",
                "display-order" => 20,
                "type" => "text-field",
            ],
            "mcloud-storage-expires" => [
                "title" => "Content Expiration",
                "description" => "Sets the Expire metadata for uploads.  This is the number of minutes from the date of upload.",
                "display-order" => 21,
                "type" => "text-field",
            ],
        ]
    ],
	"ilab-media-cloud-image-upload-handling" => [
		"title" => "Image Upload Handling",
		"dynamic" => true,
		"doc_link" => 'https://support.mediacloud.press/articles/documentation/cloud-storage/upload-handling-settings',
		"description" => "The following options control how the storage tool handles image uploads.",
		"options" => [
			"mcloud-storage-big-size-original-privacy" => [
				"title" => "Original Image Privacy ACL",
				"description" => "This will set the privacy for the original image upload.",
				"display-order" => 43,
				"type" => "select",
				"default" => 'private',
				"options" => [
					"public-read" => "Public",
					"authenticated-read" => "Authenticated Read",
					"private" => "Private"
				],
			],
		]
	],
	"ilab-media-cloud-signed-urls" => [
		"title" => "Secure URL Settings",
		"description" => "These settings control how pre-signed URLs work.",
		"dynamic" => true,
		"options" => [
			"mcloud-storage-use-presigned-urls" => [
				"title" => "Use Pre-Signed URLs",
				"description" => "Set to true to generate signed URLs that will expire within a specified time period.  You should use this if you've set the default ACL to private.",
				"display-order" => 1,
				"type" => "checkbox",
				"default" => false,
			],
			"mcloud-storage-presigned-expiration" => [
				"title" => "Pre-Signed URL Expiration",
				"description" => "The number of minutes the signed URL is valid for.",
				"display-order" => 2,
				"type" => "number",
				"default" => 10,
			],
			"mcloud-storage-use-presigned-urls-advanced" => [
				"title" => "Advanced Pre-Signed URL Settings",
				"description" => "",
				"display-order" => 3,
				"type" => "advanced-presigned",
				"plan" => "pro"
			],
		]
	],
];