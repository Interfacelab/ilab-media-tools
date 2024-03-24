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
	"ilab-media-cloud-provider-settings" => [
		"title" => "Provider Settings",
		"dynamic" => true,
		"options" => [
			"mcloud-storage-bunnycdn-apikey" => [
				"title" => "API Key",
				"description" => "The API Key for your Bunny CDN storage zone.",
				"display-order" => 1,
				"type" => "password",
			],
			"mcloud-storage-bunnycdn-storage-zone" => [
				"title" => "Storage Zone",
				"description" => "The name of your storage zone.",
				"display-order" => 2,
				"type" => "text-field",
			],
			"mcloud-storage-bunnycdn-pull-zone" => [
				"title" => "Pull Zone URL",
				"description" => "The full url, including the http/https part, of your pull zone for your storage zone.  Usually something like <code>https://XXXX.b-cdn.net</code>",
				"display-order" => 3,
				"type" => "text-field",
			],
			"mcloud-storage-bunnycdn-region" => [
				"title" => "Region",
				"description" => "The region that your storage zone is in.",
				"display-order" => 11,
				"type" => "select",
				"options" => [
					'' => 'Falkenstein: storage.bunnycdn.com',
                    'ny' => 'New York: ny.storage.bunnycdn.com',
					'la' => 'Los Angeles: la.storage.bunnycdn.com',
					'sg' => 'Singapore: sg.storage.bunnycdn.com',
					'syd' => 'Sydney: syd.storage.bunnycdn.com',
				]
			],
		]
	],
	"ilab-media-cloud-upload-handling" => [
		"title" => "Upload Handling",
		"dynamic" => true,
		"description" => "The following options control how the storage tool handles uploads.",
		"options" => [
		]
	],
	"ilab-media-cloud-signed-urls" => [
		"title" => "Secure URL Settings",
		"description" => "Pre-signed URLs aren't supported with Bunny CDN.",
		"dynamic" => true,
		"options" => [
			"mcloud-storage-bunnycdn-token-auth-key" => [
				"title" => "URL Token Auth Key",
				"description" => "The auth key for signing URLs.",
				"display-order" => 1,
				"type" => "password",
			],
			"mcloud-storage-bunnycdn-signed-matches" => [
				"title" => "Signed URL Patterns",
				"description" => "List of URL wildcard patterns that for the URLs to be signed.  For example, <code>videos/*</code> would mean that any video file that starts with <code>videos/</code> would be signed.  You'll want to be sure that you've set up matching Edge Rules in Bunny CDN dashboard to match.  The rules must <strong>Enable Token Authentication</strong>.",
				"display-order" => 17,
				"type" => "text-area"
			],
		]
	],
];