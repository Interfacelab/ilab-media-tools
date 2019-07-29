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
    "id" => "storage",
    "name" => "Cloud Storage",
	"description" => "Automatically uploads media to Amazon S3, Google Cloud Storage, Backblaze, DigitalOcean Spaces and others.",
	"class" => "ILAB\\MediaCloud\\Tools\\Storage\\StorageTool",
	"env" => "ILAB_MEDIA_S3_ENABLED",
	"dependencies" => [],
    "related" => ["media-upload", "crop"],
    "dynamic-config-option" => "mcloud-storage-provider",
    "compatibleImageOptimizers" => include 'image-optimizers.config.php',
    "incompatiblePlugins" => [
	    "Photo Gallery" => [
		    "plugin" => "photo-gallery/photo-gallery.php",
		    "description" => "If someone were to ask me for a statement about this photo gallery plug-in, the only thing I could say that would be remotely positive is that it doesn't work with Media Cloud.  We'll just leave it at that."
	    ],
	    "Webcraftic Robin Image Optimizer" => [
		    "plugin" => "robin-image-optimizer/robin-image-optimizer.php",
		    "description" => "The image optimization process is a black box with no available way for Media Cloud to hook into it.  So while it will optimize your images, Media Cloud will be unaware that any optimizations occurred and will not transfer the result to cloud storage."
	    ],
    ],
    "badPlugins" => [
	    "OptiMole" => [
		    "plugin" => "optimole-wp/optimole-wp.php",
		    "description" => "Optimole uploads and hosts your images on their servers and is fundamentally incompatible with Media Cloud."
	    ],
    ],
	"batchTools" => [
		"\\ILAB\\MediaCloud\\Tools\\Storage\\Batch\\MigrateToStorageBatchTool",
		"\\ILAB\\MediaCloud\\Tools\\Storage\\Batch\\RegenerateThumbnailBatchTool"
	],
	"CLI" => [
		"\\ILAB\\MediaCloud\\Tools\\Storage\\CLI\\StorageCommands"
	],
	"storageDrivers" => [
		's3' => [
			'name' => 'Amazon S3',
			'class' => "\\ILAB\\MediaCloud\\Storage\\Driver\\S3\\S3Storage",
			'config' => '/storage/s3.config.php',
			'help' => [
				[ 'title' => 'Read Documentation', 'url' => admin_url('admin.php?page=media-cloud-docs&doc-page=cloud-storage/setup/amazon-s3') ],
			]
		],
		'google' => [
			'name' => 'Google Cloud Storage',
			'class' => "\\ILAB\\MediaCloud\\Storage\\Driver\\GoogleCloud\\GoogleStorage",
			'config' => '/storage/google.config.php',
			'help' => [
				[ 'title' => 'Read Documentation', 'url' => admin_url('admin.php?page=media-cloud-docs&doc-page=cloud-storage/setup/google-cloud-storage')],
			]
		],
		'do' => [
			'name' => 'DigitalOcean Spaces',
			'class' => "\\ILAB\\MediaCloud\\Storage\\Driver\\S3\\DigitalOceanStorage",
			'config' => '/storage/do.config.php',
			'help' => [
				[ 'title' => 'Read Documentation', 'url' => admin_url('admin.php?page=media-cloud-docs&doc-page=cloud-storage/setup/do-spaces') ],
			]
		],
		'minio' => [
			'name' => 'Minio',
			'class' => "\\ILAB\\MediaCloud\\Storage\\Driver\\S3\\MinioStorage",
			'config' => '/storage/minio.config.php',
			'help' => [
			]
		],
		'wasabi' => [
			'name' => 'Wasabi',
			'class' => "\\ILAB\\MediaCloud\\Storage\\Driver\\S3\\WasabiStorage",
			'config' => '/storage/wasabi.config.php',
			'help' => [
				[ 'title' => 'Read Documentation', 'url' => admin_url('admin.php?page=media-cloud-docs&doc-page=cloud-storage/setup/wasabi') ],
			]
		],
		'other-s3' => [
			'name' => 'Other S3 Compatible Service',
			'class' => "\\ILAB\\MediaCloud\\Storage\\Driver\\S3\\OtherS3Storage",
			'config' => '/storage/other-s3.config.php',
			'help' => [
			]
		],
		'backblaze' => [
			'name' => 'Backblaze',
			'class' => \ILAB\MediaCloud\Storage\Driver\Backblaze\BackblazeStorage::class,
			'config' => '/storage/backblaze.config.php',
			'help' => [
				[ 'title' => 'Read Documentation', 'url' => admin_url('admin.php?page=media-cloud-docs&doc-page=cloud-storage/setup/backblaze') ],
			]
		],
	],
	"settings" => [
		"options-page" => "media-tools-s3",
		"options-group" => "ilab-media-s3",
        "watch" => true,
		"groups" => [
            "ilab-media-cloud-provider" => [
                "title" => "Cloud Provider",
                "description" => "To get Cloud Storage working, select a provider and supply the requested credentials.  For Amazon S3, this <a target='_blank' href='https://gist.github.com/jawngee/9cc2031f5ad154558b14e1fb395414cf'>IAM Policy</a> is the minimum policy you will need (remember to replace YOURBUCKET in the example with the actual name of your bucket).",
                "help" => [
                	'target' => 'footer',
                	'watch' => 'mcloud-storage-provider',
	                'data' => 'providerHelp',
                ],
	            "options" => [
	                "mcloud-storage-provider" => [
		                "title" => "Storage Provider",
		                "type" => "select",
		                "options" => "providerOptions",
	                ],
                ],
            ],
			"ilab-media-cloud-provider-settings" => [
				"title" => "Provider Settings",
				"dynamic" => true,
				"options" => [],
			],
			"ilab-media-cloud-signed-urls" => [
				"title" => "Pre-Signed URL Settings",
				"description" => "These settings control how pre-signed URLs work.",
				"dynamic" => true,
				"options" => [],
			],
			"ilab-media-cloud-upload-handling" => [
				"title" => "Upload Handling",
                "dynamic" => true,
				"description" => "The following options control how the storage tool handles uploads.",
                "options" => [
                    "mcloud-storage-prefix" => [
                        "title" => "Upload File Prefix",
                        "display-order" => 10,
                        "description" => "This will prepend a prefix to any file uploaded to cloud storage.  For dynamically created prefixes, you can use the following variables: <code>@{date:format}</code>, <code>@{site-name}</code>, <code>@{site-host}</code>, <code>@{site-id}</code>, <code>@{versioning}</code>, <code>@{user-name}</code>, <code>@{unique-id}</code>, <code>@{unique-path}</code>.  For the date token, format is any format string that you can use with php's <a href='http://php.net/manual/en/function.date.php' target='_blank'>date()</a> function.  Note that specifying a prefix here will remove WordPress's default date prefix.  WordPress's default prefix would look like: <code>@{date:Y/m}</code>.",
                        "type" => "text-field"
                    ],
                    "mcloud-storage-upload-documents" => [
                        "title" => "Upload Non-image Files",
                        "description" => "Upload non-image files such as Word documents, PDF files, zip files, etc.",
                        "display-order" => 10,
                        "type" => "checkbox",
                        "default" => true
                    ],
                    "mcloud-storage-ignored-mime-types" => [
                        "title" => "Ignored MIME Types",
                        "description" => "List of MIME types to ignore.  Any files with matching MIME types will not be uploaded.",
                        "display-order" => 10,
                        "type" => "text-area"
                    ],
                    "mcloud-storage-delete-uploads" => [
                        "title" => "Delete Uploaded Files",
                        "description" => "Deletes uploaded files from the WordPress server after they've been uploaded.",
                        "display-order" => 10,
                        "type" => "checkbox"
                    ],
                    "mcloud-storage-delete-from-server" => [
                        "title" => "Delete From Storage",
                        "description" => "When you delete from the media library, turning this on will also delete the file from cloud storage.",
                        "display-order" => 10,
                        "type" => "checkbox"
                    ]
                ]
			],
            "ilab-media-cloud-cdn-settings" => [
                "title" => "CDN Settings",
                "description" => "If you are using CloudFront, Fastly or another CDN, enter the CDN domain here.  If you are using Imgix, the <b>CDN Base URL</b> setting is ignored, but the <b>Document CDN Base URL</b> is not.  If both are left blank, Media Tools will use the cloud storage URLs.",
                "options" => [
                    "mcloud-storage-cdn-base" => [
                        "title" => "CDN Base URL",
                        "description" => "This is the base URL for your CDN for serving images, including the scheme (meaning the http/https part).  If you don't have a CDN, you can simply use the AWS S3 URL, eg <strong>https://s3-ap-southeast-1.amazonaws.com/your-bucket-name/</strong>.",
                        "type" => "text-field"
                    ],
                    "mcloud-storage-doc-cdn-base" => [
                        "title" => "Document CDN Base URL",
                        "description" => "This is the base URL for your CDN for serving non-image files, including the scheme (meaning the http/https part).  This is separated for your convenience.  If you don't specify a document CDN, it'll use the media/image CDN.",
                        "type" => "text-field"
                    ]
                ]
            ],
			"ilab-media-cloud-display-settings" => [
				"title" => "Display Settings",
				"description" => "",
				"options" => [
					"mcloud-storage-display-badge" => [
						"title" => "Display Cloud Icon",
						"description" => "When this is selected, a cloud icon will be overlayed on items in the media library grid that have been uploaded to cloud storage.",
						"type" => "checkbox",
						"default" => true
					],
					"mcloud-storage-display-media-list" => [
						"title" => "Media List Integration",
						"description" => "When this is selected, an extra column will be added to the media library's list view, as well as bulk actions for importing.",
						"type" => "checkbox",
						"default" => true
					]
				]
			],
			"ilab-media-cloud-gutenberg-settings" => [
				"title" => "Gutenberg Integration",
				"description" => "Controls integration of Dynamic Images with Gutenberg",
				"options" => [
					"mcloud-storage-disable-srcset" => [
						"title" => "Disable srcset on image tags",
						"description" => "Gutenberg's image block has a lot of issues and problems.  For example, it omits the width and height attributes which is a really bad practice.  And it's also because of this that it's impossible to calculate a srcset that is realistic when using Imgix.  Until they fix this, we recommend disabling srcset on image tags - <strong>but only if you use Gutenberg</strong>.  If you are not using Gutenberg, carry on with your bad self!",
						"type" => "checkbox",
						"default" => false
					]
				]
			]
		]
	]
];