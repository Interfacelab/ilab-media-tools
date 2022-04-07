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
	"description" => "Adds HLS support to the standard video player that can handle Mux encoded video playback.",
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
				"options" => [
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
