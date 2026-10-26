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
			"media-cloud-cloudflare-stream-account-id" => [
				"title" => "Account ID",
				"description" => "Your Cloudflare Account ID.  You can find this on the Cloudflare Stream dashboard in the upper right.",
				"type" => "text-field",
			],
			"media-cloud-cloudflare-stream-api-key" => [
				"title" => "API Key",
				"description" => "A CloudFlare API token.  You can create one on <a href='https://dash.cloudflare.com/profile/api-tokens' target='_blank'>User API Tokens dashboard</a>.  Make sure the <strong>Permissions</strong> are set to <strong>Account</strong>, <strong>Stream</strong> and <strong>Edit</strong>.",
				"type" => "password",
			],
			"media-cloud-cloudflare-stream-cdn-url" => [
				"title" => "Customer Subdomain",
				"description" => "The subdomain serving your encoded video.  You can find this on the CloudflareStream dashboard in the upper right.  <strong>You must include the scheme (https:// or http://).</strong>",
				"type" => "url",
			],
			"media-cloud-cloudflare-stream-webhook-host" => [
				"title" => "Webhook Host",
				"description" => "The host Cloudflare will send webhook requests to.  <strong>Leave blank if your website is publicly accessible.</strong>  If you are in development mode and your site is NOT publicly accessible, use <a href='https://ngrok.io' target='_blank'>ngrok</a> or similar and enter the proxy URL here.",
				"type" => "url",
			],
		]
	],
	"media-cloud-video-encoding-player-options" => [
		"conditions" => [ "media-cloud-video-encoding-provider" => [ "!cloudflare-stream" ] ]
	],
	"media-cloud-video-encoding-settings" => [
		"conditions" => [ "media-cloud-video-encoding-provider" => [ "!cloudflare-stream" ] ]
	],
	"media-cloud-video-encoding-security" => [
		"conditions" => [ "media-cloud-video-encoding-provider" => [ "!cloudflare-stream" ] ]
	],
	"media-cloud-video-encoding-watermark" => [
		"conditions" => [ "media-cloud-video-encoding-provider" => [ "!cloudflare-stream" ] ]
	],
	"media-cloud-video-encoding-integration" => [
		"title" => "WordPress Integration",
		"doc_link" => 'https://docs.mediacloud.press/articles/documentation/video-encoding/integration',
		"options" => [
			"media-cloud-cloudflare-stream-process-uploads" => [
				"title" => "Import Uploaded Videos",
				"description" => "When enabled, after a video is uploaded to cloud storage it will be imported it to Cloudflare Stream.  If disabled, no videos will be processed.",
				"type" => "checkbox",
				"default" => true,
			],
			"media-cloud-cloudflare-stream-delete-from-stream" => [
				"title" => "Delete Videos From Cloudflare Stream",
				"description" => "When enabled, when you delete a video from the media library, the associated encoded video on Cloudflare Stream will be deleted too.",
				"type" => "checkbox",
				"default" => false,
			],
		]
	],
	"media-cloud-video-encoding-transfer" => [
		"conditions" => [ "media-cloud-video-encoding-provider" => [ "!cloudflare-stream" ] ]
	],
];