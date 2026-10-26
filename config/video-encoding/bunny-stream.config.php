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
	"media-cloud-video-encoding-credentials" => [
		"title" => "Credentials",
		"doc_link" => 'https://docs.mediacloud.press/articles/documentation/video-encoding/credentials',
		"options" => [
			"media-cloud-bunny-stream-library-id" => [
				"title" => "Library ID",
				"description" => "The Library ID you will be uploading to.  You can find this on the <a href='https://dash.bunny.net/stream' target='_blank'>Bunny CDN Stream dashboard</a>.  Click on the library you want to use and you'll find it in the API section.",
				"type" => "text-field",
			],
			"media-cloud-bunny-stream-api-key" => [
				"title" => "API Key",
				"description" => "Your Bunny stream api key.  You can find this on the <a href='https://dash.bunny.net/stream' target='_blank'>Bunny CDN Stream dashboard</a>.  Click on the library you are using and you'll find it in the API section.",
				"type" => "password",
			],
			"media-cloud-bunny-stream-cdn-url" => [
				"title" => "CDN URL",
				"description" => "The CDN URL connected to your stream library.  You must include the scheme (https:// or http://).  You can find this on the <a href='https://dash.bunny.net/stream' target='_blank'>Bunny CDN Stream dashboard</a>.  Click on the library you are using and you'll find it in the API section.",
				"type" => "url",
			],
			"media-cloud-bunny-stream-webhook" => [
				"title" => "Web Hook URL",
				"description" => "This is the URL to use when configuring web hooks in Bunny Stream.  Note that your website must be publicly accessible before video encoding will work as Bunny Stream needs to send events about the encoding process to your site.  If you are working on a development site that is not publicly viewable, use something like <a href='https://ngrok.io' target='_blank'>ngrok.io</a> so that Bunny Stream can connect to it to post events about encoding.  For more information, read the <a href='https://docs.bunny.net/docs/stream-webhook' target='_blank'>Bunny Steam documentation.</a>.",
				"type" => "mux-webhook",
				"webhook-path" => "/__bunnycdn/webhook/"
			],
		]
	],
	"media-cloud-video-encoding-player-options" => [
		"conditions" => [ "media-cloud-video-encoding-provider" => [ "!bunny-stream" ] ]
	],
	"media-cloud-video-encoding-settings" => [
		"conditions" => [ "media-cloud-video-encoding-provider" => [ "!bunny-stream" ] ]
	],
	"media-cloud-video-encoding-security" => [
		"conditions" => [ "media-cloud-video-encoding-provider" => [ "!bunny-stream" ] ]
	],
	"media-cloud-video-encoding-watermark" => [
		"conditions" => [ "media-cloud-video-encoding-provider" => [ "!bunny-stream" ] ]
	],
	"media-cloud-video-encoding-integration" => [
		"title" => "WordPress Integration",
		"doc_link" => 'https://docs.mediacloud.press/articles/documentation/video-encoding/integration',
		"options" => [
			"media-cloud-bunny-stream-process-uploads" => [
				"title" => "Import Uploaded Videos",
				"description" => "When enabled, after a video is uploaded to cloud storage it will be imported it to Bunny Stream.  If disabled, no videos will be processed.",
				"type" => "checkbox",
				"default" => true,
			],
			"media-cloud-bunny-stream-delete-from-stream" => [
				"title" => "Delete Videos From Bunny Stream",
				"description" => "When enabled, when you delete a video from the media library, the associated encoded video on Bunny Stream will be deleted too.",
				"type" => "checkbox",
				"default" => false,
			],
		]
	],
	"media-cloud-video-encoding-transfer" => [
		"conditions" => [ "media-cloud-video-encoding-provider" => [ "!bunny-stream" ] ]
	],
];