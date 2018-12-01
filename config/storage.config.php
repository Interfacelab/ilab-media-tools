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
    "name" => "Storage",
	"title" => "Storage",
	"description" => "Automatically uploads media to Amazon S3, Google Cloud Storage, Backblaze, DigitalOcean Spaces and others.",
	"class" => "ILAB\\MediaCloud\\Tools\\Storage\\StorageTool",
	"env" => "ILAB_MEDIA_S3_ENABLED",
	"dependencies" => [],
    "compatibleImageOptimizers" => [
        "shortpixel" => "shortpixel-image-optimiser/wp-shortpixel.php",
        "smush" => "wp-smushit/wp-smush.php",
        "ewww" => "ewww-image-optimizer/ewww-image-optimizer.php",
        "imagify" => "imagify/imagify.php",
    ],
    "incompatiblePlugins" => [
        "NextGEN Gallery" => [
            "plugin" => "nextgen-gallery/nggallery.php",
            "description" => "NextGEN Gallery has their own media upload system that works outside of the WordPress one.  Obviously, this is not compatible with Media Cloud.  To be honest, we can't figure out why they do it this way and their codebase is too gnarly to untangle.  This plugin DOES work, but the media will be hosted on your WordPress server and not on S3 or Imgix."
        ],
    ],
    "badPlugins" => [
        "MetaSlider" => [
            "plugin" => "ml-slider/ml-slider.php",
            "description" => "This plugin rolls their own non-standard image resizing functionality which is incompatible with Media Cloud. They should be using WordPress's <code>image_downsize()</code> function."
        ]
    ],
    "batchTools" => [
        "\\ILAB\\MediaCloud\\Tools\\Storage\\Batch\\ImportStorageBatchTool",
        "\\ILAB\\MediaCloud\\Tools\\Storage\\Batch\\ThumbnailBatchTool"
    ],
	"settings" => [
		"title" => "Storage Settings",
		"menu" => "Storage Settings",
		"options-page" => "media-tools-s3",
		"options-group" => "ilab-media-s3",
        "watch" => true,
		"groups" => [
			"ilab-media-s3-aws-settings" => [
				"title" => "Storage Settings",
				"description" => "To get cloud storage working, you'll have to supply your credentials, specify the bucket and so on.  However, the better way of doing it, would be to place that information in a .env file, instead of storing it in the database.",
				"options" => [
					"ilab-media-storage-provider" => [
						"title" => "Storage Provider",
						"description" => "Specify the service you are using for cloud storage.  If you are supplying this value through a .env file, the key is: <strong>ILAB_CLOUD_STORAGE_PROVIDER</strong>.",
						"type" => "select",
						"options" => [
							"s3" => "Amazon S3",
							'google' => 'Google Cloud Storage',
							"minio" => "Minio",
							"wasabi" => "Wasabi",
							"do" => "DigitalOcean Spaces",
							"other-s3" => "Other S3 Compatible Service",
							'backblaze' => 'BackBlaze B2 Cloud Storage',
						],
					],
					"ilab-media-s3-access-key" => [
						"title" => "Access Key",
						"description" => "If you are supplying this value through a .env file, or environment variables, the key is: <strong>ILAB_AWS_S3_ACCESS_KEY</strong>",
						"type" => "text-field",
						"conditions" => [
							"ilab-media-storage-provider" => ["!google", "!backblaze"]
						]
					],
					"ilab-media-s3-secret" => [
						"title" => "Secret",
						"description" => "If you are supplying this value through a .env file, or environment variables, the key is: <strong>ILAB_AWS_S3_ACCESS_SECRET</strong>",
						"type" => "password",
						"conditions" => [
							"ilab-media-storage-provider" => ["!google", "!backblaze"]
						]
					],
					"ilab-media-backblaze-account-id" => [
						"title" => "Account Id",
						"description" => "If you are supplying this value through a .env file, or environment variables, the key is: <strong>ILAB_BACKBLAZE_ACCOUNT_ID</strong>",
						"type" => "text-field",
						"conditions" => [
							"ilab-media-storage-provider" => ["backblaze"]
						]
					],
					"ilab-media-backblaze-key" => [
						"title" => "Key",
						"description" => "If you are supplying this value through a .env file, or environment variables, the key is: <strong>ILAB_BACKBLAZE_KEY</strong>",
						"type" => "password",
						"conditions" => [
							"ilab-media-storage-provider" => ["backblaze"]
						]
					],
					"ilab-media-backblaze-bucket-url" => [
						"title" => "Bucket URL",
						"description" => "Before you can use Backblaze B2, you'll need to specify the URL for your bucket.  You only need to specify the host part of the url, eg. <code>https://f001.backblazeb2.com/</code>.  You can read about how to determine that <a href='https://help.backblaze.com/hc/en-us/articles/217666928-Creating-a-Vanity-URL-with-B2?_ga=2.229535388.1860693768.1506013044-1312126929.1505517805' target='_blank'>here</a>.  If you are supplying this value through a .env file, or environment variables, the key is: <strong>ILAB_BACKBLAZE_BUCKET_URL</strong>",
						"type" => "text-field",
						"conditions" => [
							"ilab-media-storage-provider" => ["backblaze"]
						]
					],
					"ilab-media-google-credentials" => [
						"title" => "Credentials",
						"description" => "If you are supplying this value through a .env file, or environment variables, the key is: <strong>ILAB_CLOUD_GOOGLE_CREDENTIALS</strong>",
						"type" => "text-area",
						"conditions" => [
							"ilab-media-storage-provider" => ["google"]
						]
					],
					"ilab-media-s3-bucket" => [
						"title" => "Bucket",
						"description" => "If you are supplying this value through a .env file, or environment variables, the key is: <strong>ILAB_AWS_S3_BUCKET</strong>",
						"type" => "text-field",
					],
					"ilab-media-s3-region" => [
						"title" => "Region",
						"description" => "The region that your bucket is in.  Setting this is only really useful if you are using compatible S3 services, and not if you are using Amazon S3 itself.  If you are supplying this value through a .env file, or environment variables, the key is: <strong>ILAB_AWS_S3_REGION</strong>",
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
						"conditions" => [
							"ilab-media-storage-provider" => ["!google", "!backblaze", "!do", "!wasabi"]
						]
					],
					"ilab-media-s3-use-transfer-acceleration" => [
						"title" => "Use Transfer Acceleration",
						"description" => "Amazon S3 Transfer Acceleration enables fast, easy, and secure transfers of files over long distances between your client and an S3 bucket. Transfer Acceleration takes advantage of Amazon CloudFront’s globally distributed edge locations.  <strong>You must have it enabled on your bucket in the S3 console.</strong>",
						"type" => "checkbox",
						"default" => false,
						"conditions" => [
							"ilab-media-storage-provider" => ["s3"]
						]
					],
					"ilab-media-s3-endpoint" => [
						"title" => "Custom Endpoint URL",
						"description" => "If you are supplying this value through a .env file, or environment variables, the key is: <strong>ILAB_AWS_S3_ENDPOINT</strong>",
						"type" => "text-field",
						"conditions" => [
							"ilab-media-storage-provider" => ["!google", "!backblaze", "!s3", "!wasabi"]
						]
					],
					"ilab-media-s3-use-path-style-endpoint" => [
						"title" => "Path Style Endpoint",
						"description" => "Set to true to send requests to an S3 path style endpoint by default.  If this is unchecked, requests will be sent to something like <code>https://your-bucket-name.your-s3-server.com/</code>, which is likely not to work.",
						"type" => "checkbox",
						"default" => true,
						"conditions" => [
							"ilab-media-storage-provider" => ["!google", "!backblaze", "!s3", "!wasabi"]
						]
					],
                    "ilab-media-s3-use-presigned-urls" => [
                        "title" => "Use Pre-Signed URLs",
                        "description" => "Set to true to generate signed URLs that will expire within a specified time period.  You should use this if you've set the default ACL to private.",
                        "type" => "checkbox",
                        "default" => false,
                        "conditions" => [
                            "ilab-media-storage-provider" => ["s3"]
                        ]
                    ],
                    "ilab-media-s3-presigned-expiration" => [
                        "title" => "Pre-Signed URL Expiration",
                        "description" => "The number of minutes the signed URL is valid for.",
                        "type" => "number",
                        "default" => 10,
                        "conditions" => [
                            "ilab-media-storage-provider" => ["s3"]
                        ]
                    ],
				]
			],
			"ilab-media-s3-upload-handling-settings" => [
				"title" => "Upload Handling",
				"description" => "The following options control how the storage tool handles uploads.",
				"options" => [
					"ilab-media-s3-prefix" => [
						"title" => "Upload File Prefix",
						"description" => "This will prepend a prefix to any file uploaded to cloud storage.  For dynamically created prefixes, you can use the following variables: <code>@{date:format}</code>, <code>@{site-name}</code>, <code>@{site-host}</code>, <code>@{site-id}</code>, <code>@{versioning}</code>, <code>@{user-name}</code>, <code>@{unique-id}</code>, <code>@{unique-path}</code>.  For the date token, format is any format string that you can use with php's <a href='http://php.net/manual/en/function.date.php' target='_blank'>date()</a> function.  Note that specifying a prefix here will remove WordPress's default date prefix.  WordPress's default prefix would look like: <code>@{date:Y/m}</code>. If you are supplying this value through a .env file, or environment variables, the key is: <strong>ILAB_MEDIA_S3_PREFIX</strong>.",
						"type" => "text-field"
					],
					"ilab-media-s3-privacy" => [
						"title" => "Upload Privacy ACL",
						"description" => "This will set the privacy for each upload.  You should leave it as <code>public-read</code> unless you are using Imgix.",
						"type" => "select",
						"options" => [
							"public-read" => "public-read",
							"authenticated-read" => "authenticated-read"
						],
						"conditions" => [
							"ilab-media-storage-provider" => ["!backblaze"]
						]
					],
					"ilab-media-s3-cache-control" => [
						"title" => "Cache Control",
						"description" => "Sets the Cache-Control metadata for uploads, e.g. <code>public,max-age=2592000</code> - If you are supplying this value through a .env file, or environment variables, the key is: <strong>ILAB_AWS_S3_CACHE_CONTROL</strong>.",
						"type" => "text-field",
						"conditions" => [
							"ilab-media-storage-provider" => ["!backblaze"]
						]
					],
					"ilab-media-s3-expires" => [
						"title" => "Content Expiration",
						"description" => "Sets the Expire metadata for uploads.  This is the number of minutes from the date of upload. If you are supplying this value through a .env file, or environment variables, the key is: <strong>ILAB_AWS_S3_EXPIRES</strong>.",
						"type" => "text-field",
						"conditions" => [
							"ilab-media-storage-provider" => ["!backblaze"]
						]
					],
					"ilab-media-s3-upload-documents" => [
						"title" => "Upload Non-image Files",
						"description" => "Upload non-image files such as Word documents, PDF files, zip files, etc.",
						"type" => "checkbox",
						"default" => true
					],
					"ilab-media-s3-ignored-mime-types" => [
						"title" => "Ignored MIME Types",
						"description" => "List of MIME types to ignore.  Any files with matching MIME types will not be uploaded.",
						"type" => "text-area"
					],
					"ilab-media-s3-delete-uploads" => [
						"title" => "Delete Uploaded Files",
						"description" => "Deletes uploaded files from the WordPress server after they've been uploaded.",
						"type" => "checkbox"
					],
					"ilab-media-s3-delete-from-s3" => [
						"title" => "Delete From Storage",
						"description" => "When you delete from the media library, turning this on will also delete the file from cloud storage.",
						"type" => "checkbox"
					]
				]
			],
            "ilab-media-s3-cdn-settings" => [
                "title" => "CDN Settings",
                "description" => "If you are using CloudFront, Fastly or another CDN, enter the CDN domain here.  If you are using Imgix, the <b>CDN Base URL</b> setting is ignored, but the <b>Document CDN Base URL</b> is not.  If both are left blank, Media Tools will use the cloud storage URLs.",
                "options" => [
                    "ilab-media-s3-cdn-base" => [
                        "title" => "CDN Base URL",
                        "description" => "This is the base URL for your CDN for serving images, including the scheme (meaning the http/https part).  If you don't have a CDN, you can simply use the AWS S3 URL, eg <strong>https://s3-ap-southeast-1.amazonaws.com/your-bucket-name/</strong>.",
                        "type" => "text-field"
                    ],
                    "ilab-doc-s3-cdn-base" => [
                        "title" => "Document CDN Base URL",
                        "description" => "This is the base URL for your CDN for serving non-image files, including the scheme (meaning the http/https part).  This is separated for your convenience.  If you don't specify a document CDN, it'll use the media/image CDN.",
                        "type" => "text-field"
                    ]
                ]
            ],
            "ilab-media-s3-batch-settings" => [
                "title" => "Batch Processing Settings",
                "description" => "These options control aspects of batch processing tasks like importer, thumbnail regeneration and Rekognition processing.",
                "options" => [
                    "ilab-media-s3-batch-timeout" => [
                        "title" => "Connection Timeout",
                        "description" => "The number of seconds to wait for a response before the connection times out. If you are having issues with the batch importer process, or the troubleshooting tool is complaining about <code>cURL error 23</code>, try setting this to 0.1 or even 1.",
                        "type" => "number",
                        "default" => 0.1,
                        "increment" => 0.01,
                        "min" => 0.01,
                        "max" => 30
                    ],
                    "ilab-media-s3-batch-background-processing" => [
                        "title" => "Process In Background",
                        "description" => "When this is selected, batch processing happens asynchronously in the background on your WordPress server.  However, some server configuration and hosting setups do not support this type of background processing.  If you set this to false/off, the import is processed in your browser via ajax.  This client-side ajax method is very slow and requires that the importer page be open during the entire import process.",
                        "type" => "checkbox",
                        "default" => true
                    ],
                ]
            ],
			"ilab-media-s3-display-settings" => [
				"title" => "Display Settings",
				"description" => "",
				"options" => [
					"ilab-media-s3-display-s3-badge" => [
						"title" => "Display Cloud Icon",
						"description" => "When this is selected, a cloud icon will be overlayed on items in the media library grid that have been uploaded to cloud storage.",
						"type" => "checkbox",
						"default" => true
					],
					"ilab-cloud-storage-display-media-list" => [
						"title" => "Media List Integration",
						"description" => "When this is selected, an extra column will be added to the media library's list view, as well as bulk actions for importing.",
						"type" => "checkbox",
						"default" => true
					]
				]
			]
		]
	]
];