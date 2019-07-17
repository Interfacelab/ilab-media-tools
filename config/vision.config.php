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
    "id" => "vision",
    "name" => "Vision",
	"description" => "Uses Amazon's Rekognition AI or Google's Cloud Vision service to automatically tag and categorize your uploaded images.",
	"class" => "ILAB\\MediaCloud\\Tools\\Vision\\VisionTool",
	"env" => "ILAB_VISION_ENABLED",
    "dynamic-config-option" => "mcloud-vision-provider",
    "batchTools" => [
        "\\ILAB\\MediaCloud\\Tools\\Vision\\Batch\\ImportVisionBatchTool"
    ],
	"CLI" => [
		"\\ILAB\\MediaCloud\\Tools\\Vision\\CLI\\VisionCLICommands"
	],
	"visionDrivers" => [
		'rekognition' => [
			'name' => 'Amazon Rekognition',
			'class' => "\\ILAB\\MediaCloud\\Vision\\Driver\\Rekognition\\RekognitionDriver",
			'config' => '/vision/rekognition.config.php',
			'help' => [
				[ 'title' => 'Read Documentation', 'url' => admin_url('admin.php?page=media-cloud-docs&doc-page=vision#configuring-amazon-rekognition') ],
			]
		],
	],
	"settings" => [
		"options-page" => "media-tools-vision",
		"options-group" => "ilab-media-vision",
		"groups" => [
            "mcloud-vision-provider" => [
                "title" => "Vision Provider",
                "watch" => true,
	            "help" => [
		            'target' => 'footer',
		            'watch' => 'mcloud-vision-provider',
		            'data' => 'providerHelp',
	            ],
                "options" => [
                    "mcloud-vision-provider" => [
                        "title" => "Vision Provider",
                        "description" => "Specify the service you are using for vision processing.",
                        "type" => "select",
                        "options" => 'providerOptions',
                    ],
                ]
            ],
            "mcloud-vision-provider-settings" => [
                "title" => "Vision Provider Settings",
                "watch" => true,
                "dynamic" => true,
                "description" => "Supply any required credentials.  <strong>Note, these credentials are shared with Cloud Storage.</strong>  Changing credentials here will change your cloud storage credentials.  However, you can mix and match providers, meaning you can use Google Cloud Vision with Amazon S3 or DigitalOcean or any other cloud storage provider.  Amazon Rekognition, however, must be used with Amazon S3.",
                "options" => [
                ]
            ],
			"ilab-vision-options" => [
				"title" => "Vision Options",
                "dynamic" => true,
				"options" => [
					"mcloud-vision-detect-faces" => [
						"title" => "Detect Faces",
						"description" => "Detects faces in the image.  If you use this option, you should not use the <em>Detect Celebrity Faces<</em> option as either will overwrite the other.  Detected faces will be stored as additional metadata for the image.  If you are using Imgix, you can use this for cropping images centered on a face.",
                        "display-order" => 8,
						"type" => "checkbox",
						"default" => false
					],
					"mcloud-vision-always-background" => [
						"title" => "Always Process in Background",
						"description" => "Controls if Vision tasks are processed during an upload or queued to a background task to be processed at a later time (usually within a few minutes).",
						"display-order" => 9,
						"type" => "checkbox",
						"default" => false
					],
				]
			]
		]
	]
];