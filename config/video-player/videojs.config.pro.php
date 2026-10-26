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
	"media-cloud-mux-player-options" => [
		"title" => "Player Settings",
		"doc_link" => 'https://docs.mediacloud.press/articles/documentation/video-encoding/player-settings',
		"description" => "Control which player library to use (if any) and the options for the chosen video player",
		"options" => [
			"media-cloud-mux-player-mp4-fallback" => [
				"title" => "Use MP4 Fallback",
				"description" => "When enabled, the player will prefer the HLS streaming source first but use an MP4 fallback if one exists.",
				"type" => "checkbox",
				"default" => true,
				"display-order" => 20,
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
				"display-order" => 21,
			],
			"media-cloud-player-css-classes" => [
				"title" => "Additional Player CSS Classes",
				"description" => "Any additional CSS classes to add to the player's &lt;video&gt; tag.",
				"type" => "text-field",
				"display-order" => 22,
			],
			"media-cloud-mux-player-air-play" => [
				"title" => "Air Play",
				"description" => "Allow the video to be played back on Air Play devices on supports browsers/OS.",
				"type" => "checkbox",
				"default" => true,
				"display-order" => 23,
			],
			"media-cloud-mux-player-quality-select" => [
				"title" => "Quality Selector",
				"description" => "Allow playback quality to be selected for variable bit-rate videos",
				"type" => "checkbox",
				"default" => true,
				"display-order" => 24,
			],
			"media-cloud-mux-player-override-native" => [
				"title" => "Override Native",
				"description" => "When enabled, the player will override the web browser's native handling of HLS.  This really only effects Safari.",
				"type" => "checkbox",
				"default" => true,
				"display-order" => 25,
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
				"display-order" => 26,
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
				"display-order" => 27,
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
				"display-order" => 28,
			],
			"media-cloud-mux-player-customize-colors" => [
				"title" => "Customize Player Colors",
				"description" => "Set the colors for the video.js player.",
				"type" => "checkbox",
				"default" => false,
				"display-order" => 29,
			],
			"media-cloud-player-color-primary" => [
				"title" => "Player Primary Color",
				"default" => "#000000",
				"description" => "The primary color of the player, aka the background color.",
				"type" => "color",
				"display-order" => 30,
			],
			"media-cloud-player-color-buttons" => [
				"title" => "Player Button Color",
				"default" => "#FFFFFF",
				"description" => "The color of the player's buttons.",
				"type" => "color",
				"display-order" => 31,
			],
			"media-cloud-player-color-play-position" => [
				"title" => "Playback Bar Color",
				"default" => "#FFFFFF",
				"description" => "The color of the playback bar.",
				"type" => "color",
				"display-order" => 32,
			],
			"media-cloud-player-color-play-position-ball" => [
				"title" => "Playback Bar Ball Color",
				"default" => "#FFFFFF",
				"description" => "The color of the ball at the end of the playback bar.",
				"type" => "color",
				"display-order" => 33,
			],
			"media-cloud-player-color-load-progress" => [
				"title" => "Load Progress Bar Color",
				"default" => "#545454",
				"description" => "The color of the load progress bar.",
				"type" => "color",
				"display-order" => 34,
			],
		],
	],
];