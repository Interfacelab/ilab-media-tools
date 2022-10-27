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
    "name" => "Computer Vision",
	"description" => "Uses Amazon's Rekognition AI or Google's Cloud Vision service to automatically tag and categorize your uploaded images.",
	"class" => "MediaCloud\\Plugin\\Tools\\Vision\\VisionTool",
	"env" => "ILAB_VISION_ENABLED",
    "dynamic-config-option" => "mcloud-vision-provider",
	"CLI" => [
		"\\MediaCloud\\Plugin\\Tools\\Vision\\CLI\\VisionCLICommands"
	],
	"visionDrivers" => [
		'rekognition' => [
			'name' => 'Amazon Rekognition',
			'class' => "\\MediaCloud\\Plugin\\Tools\\Vision\\Driver\\Rekognition\\RekognitionDriver",
			'config' => '/vision/rekognition.config.php',
			'help' => [
				[ 'title' => 'Read Documentation', 'url' => 'https://help.mediacloud.press/article/59-setting-up-amazon-rekognition', 'beacon_id' => '59' ],
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
                ],
	            'hide-save' => true,
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
				"doc_link" => 'https://support.mediacloud.press/articles/documentation/vision/vision-options',
                "dynamic" => true,
				"options" => [
					"mcloud-vision-detect-faces" => [
						"title" => "Detect Faces",
						"description" => "Detects faces in the image.  If you use this option, you should not use the <em>Detect Celebrity Faces<</em> option as either will overwrite the other.  Detected faces will be stored as additional metadata for the image.  If you are using Imgix, you can use this for cropping images centered on a face.",
                        "display-order" => 8,
						"type" => "checkbox",
						"default" => false
					],
					"mcloud-vision-save-tags-to-alt" => [
						"title" => "Save Taxonomy to Alt Text",
						"description" => "Saves any detected taxonomy to the alt text of the attachment.",
						"display-order" => 10,
						"type" => "checkbox",
						"default" => false
					],
					"mcloud-vision-save-tags-to-caption" => [
						"title" => "Save Taxonomy to Caption",
						"description" => "Saves any detected taxonomy to the caption of the attachment.",
						"display-order" => 11,
						"type" => "checkbox",
						"default" => false
					],
					"mcloud-vision-save-tags-to-description" => [
						"title" => "Save Taxonomy to Description",
						"description" => "Saves any detected taxonomy to the description of the attachment.",
						"display-order" => 12,
						"type" => "checkbox",
						"default" => false
					],
					"mcloud-vision-tax-prefix" => [
						"title" => "Taxonomy Prefix Text",
						"description" => "When saving the detected labels to the alt text, caption and/or description, this text will be prefixed to it.",
						"type" => "text-field",
						"display-order" => 13,
					],
					"mcloud-vision-always-background" => [
						"title" => "Always Process in Background",
						"description" => "Controls if Vision tasks are processed during an upload or queued to a background task to be processed at a later time (usually within a few minutes).",
						"display-order" => 14,
						"type" => "checkbox",
						"default" => false
					],
					"mcloud-vision-force-term-count" => [
						"title" => "Force Term Count",
						"description" => "By default, WordPress will not include an attachment in a term or category's count if it is not attached to anything.  Enabling this will force WordPress to include it in the term count.",
						"display-order" => 15,
						"type" => "checkbox",
						"default" => false
					],
				]
			]
		]
	]
];
