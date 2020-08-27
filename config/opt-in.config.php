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
	"id" => "opt-in",
	"name" => "Opt-In Settings",
	"description" => "Manage opt-in settings.",
	"class" => "MediaCloud\\Plugin\\Tools\\Permissions\\OptInTool",
	"exclude" => true,
	"dependencies" => [],
	"env" => "ILAB_MEDIA_PERMISSIONS_ENABLED",  // this is always enabled btw
	"settings" => [
		"options-page" => "media-cloud-opt-in",
		"options-group" => "media-cloud-opt-in-group",
		"groups" => [
			"media-cloud-opt-in-settings" => [
				"title" => "Opt-In Settings",
				"dynamic" => true,
				"description" => "The following options control the various permissions you are granting Media Cloud.  When you first activate the plugin and choose <strong>Accept & Continue</strong> you are granting Media Cloud these various permissions.  You can disable them, or enable them, here.",
				"options" => [
					"mcloud-opt-usage-tracking" => [
						"title" => "Feature Tracking",
						"description" => "When this is selected, we will collect completely anonymous information about which features are enabled, which storage/vision provider is being used and which batch tools are being used.  We do not collect any personally identifiable information, everything is completely anonymous.  Nor do we collect any information such as file names, access keys, etc.  We use this information to focus development efforts on features that matter most to our users.",
						"type" => "checkbox",
						"default" => false
					],
				]
			],
		]
	]
];