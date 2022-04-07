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
	"description" => "<a href='https://videojs.com' target='_blank'>Video.js</a> based video player that can handle Mux encoded HLS video and standard video uploads.",
	"class" => "MediaCloud\\Plugin\\Tools\\Video\\Player\\VideoPlayerTool",
	"dependencies" => [
	],
	"env" => "MCLOUD_PLAYER_ENABLED",
	"settings" => [
		"options-page" => "media-cloud-player",
		"options-group" => "media-cloud-player",
		"groups" => [
			"media-cloud-mux-player" => [
				"title" => "Player Settings",
				"doc_link" => 'https://support.mediacloud.press/articles/documentation/video-encoding/mux-player-settings',
				"description" => "Control which player library to use (if any) and the options for the chosen video player",
				"options" => [
					"media-cloud-mux-player" => [
						"title" => "Player",
						"description" => "Choose which player to use when displaying Mux hosted videos.",
						"default" => "videojs",
						"type" => "select",
						"options" => [
							"none" => "None, use the system player (Not recommended)",
							"videojs" => "Video.js Player",
							"hlsjs" => "Native player with HLS.js",
						],
					],
					"media-cloud-mux-player-air-play" => [
						"title" => "Air Play",
						"description" => "Allow the video to be played back on Air Play devices on supports browsers/OS.",
						"type" => "checkbox",
						"default" => true,
						"conditions" => [
							"media-cloud-mux-player" => ["videojs"]
						]
					],
					"media-cloud-mux-player-quality-select" => [
						"title" => "Quality Selector",
						"description" => "Allow playback quality to be selected for variable bit-rate videos",
						"type" => "checkbox",
						"default" => true,
						"conditions" => [
							"media-cloud-mux-player" => ["videojs"]
						]
					],
					"media-cloud-mux-player-override-native" => [
						"title" => "Override Native",
						"description" => "When enabled, the player will override the web browser's native handling of HLS.  This really only effects Safari.",
						"type" => "checkbox",
						"default" => true,
						"conditions" => [
							"media-cloud-mux-player" => ["videojs"]
						]
					],
					"media-cloud-mux-player-allow-download" => [
						"title" => "Allow Video Download",
						"description" => "When enabled, the player will allow users to download the MP4 representation that MUX generates.",
						"default" => 0,
						"type" => "select",
						"options" => [
							0 => "Do not allow download",
							'on' => "Allow download",
							'logged-in' => "Allow download for logged in users only.",
						],
						"conditions" => [
							"media-cloud-mux-player" => ["videojs"]
						]
					],
					"media-cloud-mux-player-allow-download-original" => [
						"title" => "Allow Original Video Download",
						"description" => "When enabled, the player will allow users to download the original source video file.",
						"default" => 0,
						"type" => "select",
						"options" => [
							0 => "Do not allow download",
							'on' => "Allow download",
							'logged-in' => "Allow download for logged in users only.",
						],
						"conditions" => [
							"media-cloud-mux-player" => ["videojs"]
						]
					],
					"media-cloud-mux-player-analytics-mode" => [
						"title" => "Google Analytics Mode",
						"description" => "To track events in Google Analytics, select the mode you are using.",
						"type" => "select",
						"default" => "none",
						"options" => [
							"none" => "None",
							"gtag" => "Global Site Tag (gtag.js)",
							"other" => "Other (analytics.js or ga.js)",
						],
						"conditions" => [
							"media-cloud-mux-player" => ["videojs"]
						]
					],
					"media-cloud-mux-player-mp4-fallback" => [
						"title" => "Use MP4 Fallback",
						"description" => "When enabled, the player will prefer the HLS streaming source first but use an MP4 fallback if one exists.",
						"type" => "checkbox",
						"default" => true
					],
					"media-cloud-mux-player-mp4-preferred-quality" => [
						"title" => "MP4 Fallback Quality",
						"description" => "Mux generates up to 3 levels of quality for most MP4s.  Select the preferred quality to use for the fallback.",
						"type" => "select",
						"default" => "medium.mp4",
						"options" => [
							"low.mp4" => "Low Quality",
							"medium.mp4" => "Medium Quality",
							"high.mp4" => "High Quality",
						],
					],
					"media-cloud-player-css-classes" => [
						"title" => "Additional Player CSS Classes",
						"description" => "Any additional CSS classes to add to the player's &lt;video&gt; tag.",
						"type" => "text-field",
					],
				]
			],
		]
	]
];
