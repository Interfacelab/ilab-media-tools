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
		],
	],
];