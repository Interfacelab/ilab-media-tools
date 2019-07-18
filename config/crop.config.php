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
    "id" => "crop",
    "name" => "Crop Tool",
	"description" => "Provides an advanced and easy to use image crop tool that works with or without cloud storage.",
	"class" => "ILAB\\MediaCloud\\Tools\\Crop\\CropTool",
	"dependencies" => [],
	"env" => "ILAB_MEDIA_CROP_ENABLED",
	"settings" => [
		"options-page" => "media-tools-crop",
		"options-group" => "ilab-media-crop",
		"groups" => [
			"ilab-media-crop-settings" => [
				"title" => "Crop Settings",
				"options" => [
					"mcloud-crop-quality" => [
						"title" => "Crop Quality",
                        "description" => "The jpeg compression quality to use for any images cropped with the tool.",
						"type" => "number",
                        "default" => 100
					]
				]
			]
		]
	]
];
