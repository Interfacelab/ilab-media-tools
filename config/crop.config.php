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
    "name" => "Image Crop",
	"title" => "Image Crop",
	"description" => "Provides an easy to use tool for manually cropping images for all image sizes.",
	"class" => "ILAB\\MediaCloud\\Tools\\Crop\\CropTool",
	"dependencies" => [],
	"env" => "ILAB_MEDIA_CROP_ENABLED",
	"settings" => [
		"title" => "Crop Settings",
		"menu" => "Crop Settings",
		"options-page" => "media-tools-crop",
		"options-group" => "ilab-media-crop",
		"groups" => [
			"ilab-media-crop-settings" => [
				"title" => "Crop Settings",
				"description" => "Put your crop settings here",
				"options" => [
					"ilab-media-crop-quality" => [
						"title" => "Crop Quality",
						"type" => "number",
                        "default" => 100
					]
				]
			]
		]
	]
];
