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
			"media-cloud-player-media-chrome-config" => [
				"title" => "Player Configuration",
				"description" => "The color of the load progress bar.",
				"type" => "media-chrome-controls-editor",
				"conditions" => [
					"media-cloud-mux-player" => ["media-chrome"]
				],
				"display-order" => 20,
			],
		],
	],
];