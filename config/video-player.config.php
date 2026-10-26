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
	"id" => "video-player",
	"name" => "Video Player",
	"description" => "<a href='https://videojs.com' target='_blank'>Video.js</a> based video player that can handle <a href='https://bunny.net/stream/?ref=33lsyjqfr3' target='_blank'>Bunny CDN Stream</a>, <a href='https://www.cloudflare.com/products/cloudflare-stream/' target='_blank'>Cloudflare Stream</a>, <a href='https://api.video' target='_blank'>api.video</a> or <a href='https://mux.com/' target='_blank'>Mux</a> encoded HLS video and standard video uploads.",
	"class" => \MediaCloud\Plugin\Tools\VideoPlayer\VideoPlayerTool::class,
	"dependencies" => [
	],
	"dynamic-config-option" => "media-cloud-mux-player",
	"watch" => true,
	"env" => "MCLOUD_PLAYER_ENABLED",
	"videoPlayers" => [
		'hlsjs' => [
			'name' => 'Native player with HLS.js',
			'class' => \MediaCloud\Plugin\Tools\VideoPlayer\Players\Native\NativeVideoPlayer::class,
			'config' => '/video-player/native.config.php',
			'help' => [
				[ 'title' => 'Read Documentation', 'url' => 'https://support.mediacloud.press/articles/documentation/video-encoding/about-video-encoding' ],
			]
		],
	],
	"settings" => [
		"options-page" => "media-cloud-player",
		"options-group" => "media-cloud-player",
		"groups" => [
			"media-cloud-mux-player" => [
				"title" => "Player",
				"doc_link" => 'https://docs.mediacloud.press/articles/documentation/video-encoding/player-settings',
				"description" => "Control which player library to use (if any) and the options for the chosen video player",
				"options" => [
					"media-cloud-mux-player" => [
						"title" => "Player",
						"description" => "Choose which player to use when displaying videos.",
						"default" => "media-chrome",
						"type" => "select",
						"options" => "providerOptions",
					],
				],
			],
			"media-cloud-mux-player-options" => [
				"title" => "Player Settings",
				"doc_link" => 'https://docs.mediacloud.press/articles/documentation/video-encoding/player-settings',
				"description" => "Control which player library to use (if any) and the options for the chosen video player",
				"dynamic" => true,
				"options" => [
					"media-cloud-replace-admin-player" => [
						"title" => "Replace WordPress Admin Video Player",
						"description" => "Replace the video player WordPress admin uses with a native player using HLS.js.  This allows you to preview the encoded video without having to create a post.",
						"type" => "checkbox",
						"default" => true,
					],
					"media-cloud-override-player-shortcode" => [
						"title" => "Override WordPress Video Shortcode",
						"description" => "When enabled, any videos embedded in posts with WordPress's <code>[video]</code> shortcode will be replaced with Media Cloud's selected video player.",
						"type" => "checkbox",
						"default" => true,
					],
					"media-cloud-mux-player-include-js" => [
						"title" => "Always Include Video Player JavaScript",
						"description" => "When enabled, the javascript file for the video.js or HLS player will be included on every page.  You may need to do this if you dynamically display videos.",
						"type" => "checkbox",
						"default" => false
					],
				]
			],
		]
	]
];
