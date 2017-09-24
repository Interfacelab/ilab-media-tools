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

/**
 * Configuration for ILab Media Tools
 */
return [
	/** Crop Tool */
	"crop" => include ILAB_CONFIG_DIR.'/crop.config.php',
	"storage" => include ILAB_CONFIG_DIR.'/storage.config.php',
	"imgix" => include ILAB_CONFIG_DIR.'/imgix.config.php',
	"media-upload" => include ILAB_CONFIG_DIR.'/media-upload.config.php',
	"rekognition" => include ILAB_CONFIG_DIR.'/rekognition.config.php',
	"debugging" => include ILAB_CONFIG_DIR.'/debugging.config.php'
];
