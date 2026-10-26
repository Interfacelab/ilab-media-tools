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
    "id" => "video-encoding",
    "name" => "Video Encoding",
	"description" => "Video encoding via <a href='https://bunny.net/stream/?ref=33lsyjqfr3' target='_blank'>Bunny CDN Stream</a>, <a href='https://www.cloudflare.com/products/cloudflare-stream/' target='_blank'>Cloudflare Stream</a>, <a href='https://api.video' target='_blank'>api.video</a> or <a href='https://mux.com/' target='_blank'>Mux</a>.",
	"class" => \MediaCloud\Plugin\Tools\Video\VideoEncodingTool::class,
	"dependencies" => [
		"video-player",
	],
	"dynamic-config-option" => "media-cloud-video-encoding-provider",
	"env" => "MCLOUD_MUX_ENABLED",
	"CLI" => [
		\MediaCloud\Plugin\Tools\Video\CLI\VideoCommands::class,
	],
	"videoEncodingDrivers" => [
		'mux' => [
			'name' => 'Mux',
			'class' => \MediaCloud\Plugin\Tools\Video\Driver\Mux\MuxTool::class,
			'config' => '/video-encoding/mux.config.php',
			'help' => [
				[ 'title' => 'Sign Up For Mux Account', 'external_url' => 'https://mux.com' ],
				[ 'title' => 'Read Documentation', 'url' => 'https://support.mediacloud.press/articles/documentation/video-encoding/about-video-encoding' ],
			]
		],
		'bunny-stream' => [
			'name' => 'Bunny CDN Stream (Beta)',
			'class' => \MediaCloud\Plugin\Tools\Video\Driver\Bunny\BunnyStreamTool::class,
			'config' => '/video-encoding/bunny-stream.config.php',
			'help' => [
				[ 'title' => 'Sign Up For Bunny CDN Account', 'url' => 'https://bunny.net?ref=33lsyjqfr3' ],
				[ 'title' => 'Read Documentation', 'url' => 'https://docs.mediacloud.press/articles/documentation/cloud-storage/setting-up-bunny-cdn/' ],
			]
		],
		'cloudflare-stream' => [
			'name' => 'Cloudflare Stream (Beta)',
			'class' => \MediaCloud\Plugin\Tools\Video\Driver\Cloudflare\CloudflareStreamTool::class,
			'config' => '/video-encoding/cloudflare-stream.config.php',
			'help' => [
				[ 'title' => 'Sign Up For Cloudflare Stream', 'url' => 'https://www.cloudflare.com/products/cloudflare-stream/' ],
				[ 'title' => 'Read Documentation', 'url' => 'https://docs.mediacloud.press/articles/documentation/cloud-storage/setting-up-bunny-cdn/' ],
			]
		],
		'api-video' => [
			'name' => 'api.video (Beta)',
			'class' => \MediaCloud\Plugin\Tools\Video\Driver\APIVideo\APIVideoTool::class,
			'config' => '/video-encoding/api-video.config.pro.php',
			'help' => [
				[ 'title' => 'Sign Up For api.video', 'url' => 'https://www.api.video/' ],
				[ 'title' => 'Read Documentation', 'url' => 'https://docs.mediacloud.press/articles/documentation/cloud-storage/setting-up-bunny-cdn/' ],
			]
		],
	],
	"settings" => [
		"options-page" => "media-cloud-mux",
		"options-group" => "media-cloud-mux",
		"groups" => [
			"media-cloud-video-encoding-provider" => [
				"title" => "Video Encoding Provider",
				"description" => "To get video encoding working, select a provider and supply the requested credentials.",
				"help" => [
					'target' => 'footer',
					'watch' => 'media-cloud-video-encoding-provider',
					'data' => 'providerHelp',
				],
				"options" => [
					"media-cloud-video-encoding-provider" => [
						"title" => "Video Encoding Provider",
						"type" => "select",
						"options" => "providerOptions",
					],
				],
				"hide-save" => true
			],
			"media-cloud-video-encoding-credentials" => [
				"title" => "Credentials",
				"dynamic" => true,
				"doc_link" => 'https://docs.mediacloud.press/articles/documentation/video-encoding/credentials',
				"options" => [
				]
			],
			"media-cloud-video-encoding-settings" => [
				"title" => "Encoding Settings",
				"dynamic" => true,
				"doc_link" => 'https://docs.mediacloud.press/articles/documentation/video-encoding/encoding-settings',
				"options" => [
				]
			],
			"media-cloud-video-encoding-integration" => [
				"title" => "WordPress Integration",
				"dynamic" => true,
				"doc_link" => 'https://docs.mediacloud.press/articles/documentation/video-encoding/integration',
				"options" => [
				]
			],
		]
	]
];
