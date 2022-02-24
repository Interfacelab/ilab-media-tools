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
		        "description" => "The region that your bucket is in.",
		        "display-order" => 11,
		        "type" => "select",
		        "options" => [
			        'us-east-1' => 'US East 1',
			        'us-east-2' => 'US East 2',
			        'us-west-1' => 'US West',
			        'us-central' => 'US Central',
			        'eu-central-1' => 'EU (Amsterdam)',
			        'eu-west-1' => 'EU (London)',
			        'eu-west-2' => 'EU (Paris)',
			        'ap-northeast-1' => 'Asia Pacific (Tokyo)',
			        'ap-northeast-2' => 'Asia Pacific (Osaka)',
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
		        "description" => "This will set the privacy for each upload.  You should leave it as <code>public-read</code> unless you are using Imgix.  If using Scaleways, for private uploads you must use <strong>Private</strong>, for other providers <strong>Authenticated Read</strong> is preferred.",
		        "type" => "select",
		        "options" => [
			        "public-read" => "Public",
			        "authenticated-read" => "Authenticated Read",
			        "private" => "Private"
		        ],
	        ],
	        "mcloud-storage-advanced-privacy" => [
		        "title" => "Advanced Privacy",
		        "display-order" => 2,
		        "description" => "",
		        "type" => "advanced-privacy",
		        "plan" => "pro"
	        ],
            "mcloud-storage-cache-control" => [
                "title" => "Cache Control",
	            "display-order" => 20,
                "description" => "Sets the Cache-Control metadata for uploads, e.g. <code>public,max-age=2592000</code>.",
                "type" => "text-field",
            ],
            "mcloud-storage-expires" => [
                "title" => "Content Expiration",
	            "display-order" => 21,
                "description" => "Sets the Expire metadata for uploads.  This is the number of minutes from the date of upload.",
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
		],
	],
];