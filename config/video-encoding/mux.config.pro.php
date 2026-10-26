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
			"media-cloud-mux-token-id" => [
				"title" => "Token ID",
				"description" => "The Mux API token ID.  You can find, or create new keys, on the <a href='https://dashboard.mux.com/settings/access-tokens' target='_blank'>mux.com dashboard</a>.",
				"type" => "text-field",
			],
			"media-cloud-mux-token-secret" => [
				"title" => "Token Secret",
				"description" => "The Mux API token secret.  You can find, or create new keys, on the <a href='https://dashboard.mux.com/settings/access-tokens' target='_blank'>mux.com dashboard</a>.",
				"type" => "password",
			],
			"media-cloud-mux-webhook" => [
				"title" => "Web Hook URL",
				"description" => "This is the URL to use when configuring web hooks in Mux.  Note that your website must be publicly accessible before video encoding will work as Mux needs to send events about the encoding process to your site.  If you are working on a development site that is not publicly viewable, use something like <a href='https://ngrok.io' target='_blank'>ngrok.io</a> so that Mux can connect to it to post events about encoding.  For more information, read the <a href='https://docs.mux.com/docs/webhooks' target='_blank'>Mux documentation.</a>.",
				"type" => "mux-webhook",
			],
			"media-cloud-mux-webhook-secret" => [
				"title" => "Web Hook Secret",
				"description" => "The web hook signing secret.  You can find that <a href='https://dashboard.mux.com/settings/webhooks' target='_blank'>here</a>.",
				"type" => "password",
			],
			"media-cloud-mux-env-key" => [
				"title" => "Environment Key",
				"description" => "The Mux environment key.  This value is optional, but enables analytics for your videos.  This environment key can be found <a href='https://dashboard.mux.com/environments' target='_blank'>here</a>.",
				"type" => "text-field",
			],
		]
	],
	"media-cloud-video-encoding-settings" => [
		"title" => "Encoding Settings",
		"doc_link" => 'https://docs.mediacloud.press/articles/documentation/video-encoding/encoding-settings',
		"options" => [
			"media-cloud-mux-normalize-audio" => [
				"title" => "Normalize Audio",
				"description" => "Normalize the audio track loudness level.",
				"type" => "checkbox",
				"default" => false,
				"display-order" => 1,
			],
			"media-cloud-mux-per-title-encoding" => [
				"title" => "Per-Title Encoding",
				"description" => "Per-title encoding analyzes an individual video to determine the ideal encoding ladder. The result is that different videos are streamed at different resolutions and bitrates, and every video looks better - often by up to 20%-30%.",
				"type" => "checkbox",
				"default" => false,
				"display-order" => 2,
			],
			"media-cloud-mux-test-mode" => [
				"title" => "Test Mode",
				"description" => "Enabling test mode will allow you to evaluate the Mux Video APIs without incurring any cost. There is no limit on number of test assets created. Any encoded videos will be watermarked with the Mux logo, limited to 10 seconds and be deleted after 24 hrs",
				"type" => "checkbox",
				"default" => false,
				"display-order" => 3,
			],
			"media-cloud-mux-player-filmstrips" => [
				"title" => "Generate Filmstrips",
				"description" => "Generate thumbnail filmstrips for video players.  Only useful for Video.js video player.",
				"type" => "checkbox",
				"default" => false,
				"display-order" => 4,
			],
		]
	],
	"media-cloud-video-encoding-player-options" => [
		"conditions" => [ "media-cloud-video-encoding-provider" => [ "!mux" ] ]
	],
	"media-cloud-video-encoding-security" => [
		"title" => "Security",
		"doc_link" => 'https://docs.mediacloud.press/articles/documentation/video-encoding/security',
		"description" => "Mux allows you to secure your videos with signed URLs that expire after a given amount of time.",
		"watch" => true,
		"options" => [
			"media-cloud-mux-secure-video" => [
				"title" => "Secure Videos",
				"description" => "Enabled to secure all video URLs.",
				"type" => "checkbox",
				"default" => false,
			],
			"media-cloud-mux-secure-video-expiration" => [
				"title" => "Secure Video Expiration",
				"description" => "The amount of time, in minutes, a signed URL is valid for.  The actual expiration will be the duration of the video plus whatever value you specify here.",
				"type" => "number",
				"default" => 0,
				"min" => 0,
				"max" => 10080
			],
			"media-cloud-mux-secure-thumbnails" => [
				"title" => "Secure Thumbnails",
				"description" => "Enabled to secure all video thumbnails.",
				"type" => "checkbox",
				"default" => false,
			],
			"media-cloud-mux-secure-thumbnail-expiration" => [
				"title" => "Secure Thumbnail Expiration",
				"description" => "The amount of time, in minutes, a signed URL is valid for.",
				"type" => "number",
				"default" => 0,
				"min" => 0,
				"max" => 10080
			],
			"media-cloud-mux-secure-gif-expiration" => [
				"title" => "Secure GIF Expiration",
				"description" => "The amount of time, in minutes, a signed GIF URL is valid for.",
				"type" => "number",
				"default" => 0,
				"min" => 0,
				"max" => 10080
			],
			"media-cloud-mux-secure-key-rotation" => [
				"title" => "Key Rotation",
				"description" => "The amount of time, in hours, between key rotations.",
				"type" => "number",
				"default" => 24,
				"min" => 1,
				"max" => 10080
			],
			"media-cloud-mux-allowed-origins" => [
				"title" => "Allowed Origins",
				"description" => "Lists the origins allowed to display the video. Use * for wildcard subdomains. If this list is empty any origin can view the video.",
				"type" => "text-area",
				"watch" => true,
				"display-order" => 100
			],
			"media-cloud-mux-allow-no-referer" => [
				"title" => "Allow No Referer",
				"description" => "If disabled, any requests to view the video that don't send a referer header (for example, an iOS app, a smart TV app, command line app, etc.) will be rejected.",
				"type" => "checkbox",
				"default" => false,
				"watch" => true,
				"display-order" => 101
			],
			"media-cloud-mux-allow-no-user-agent" => [
				"title" => "Allow No User Agent",
				"description" => "If disabled, any requests to view the video that don't send a user agent header (for example, an iOS app, a smart TV app, command line app, etc.) will be rejected.  Please see <a href='https://docs.mux.com/guides/secure-video-playback#using-user-agent-http-header-for-validation' target='_blank'>Using User-Agent HTTP header for validation</a> for more details on this feature.",
				"type" => "checkbox",
				"default" => true,
				"watch" => true,
				"display-order" => 102
			],
			"media-cloud-mux-allow-high-risk-user-agent" => [
				"title" => "Allow High Risk User Agent",
				"description" => "Whether or not to allow high risk user agents. The high risk user agents are defined by Mux.",
				"type" => "checkbox",
				"default" => true,
				"watch" => true,
				"display-order" => 103
			],
		]
	],
	"media-cloud-video-encoding-watermark" => [
		"title" => "Watermark",
		"doc_link" => 'https://docs.mediacloud.press/articles/documentation/video-encoding/watermark',
		"options" => [
			"media-cloud-mux-watermark-enabled" => [
				"title" => "Enable Watermark",
				"description" => "When enabled, the image you select below will be watermarked onto any video uploads.",
				"type" => "checkbox",
				"default" => false,
			],
			"media-cloud-mux-watermark-image" => [
				"title" => "Watermark Image",
				"description" => "The image to use for the watermark.",
				"type" => "image",
				"default" => true,
			],
			"media-cloud-mux-watermark-width" => [
				"title" => "Watermark Width",
				"description" => "The width, in pixels, of the watermark image when applied to a 1920x1080 video.  This value will be scaled accordingly for different size videos.",
				"type" => "number",
				"default" => 120,
				"min" => 16,
				"max" => 1920
			],
			"media-cloud-mux-watermark-vertical-align" => [
				"title" => "Vertical Align",
				"description" => "The vertical alignment of the watermark",
				"type" => "select",
				"default" => "middle",
				"options" => [
					"top" => "Top",
					"middle" => "Middle",
					"bottom" => "Bottom",
				],
			],
			"media-cloud-mux-watermark-vertical-margin" => [
				"title" => "Vertical Margin",
				"description" => "The vertical margin, in pixels, of the watermark image when applied to a 1920x1080 video.  This value will be scaled accordingly for different size videos.",
				"type" => "number",
				"default" => 0,
				"min" => 0,
				"max" => 1920
			],
			"media-cloud-mux-watermark-horizontal-align" => [
				"title" => "Horizontal Align",
				"description" => "The horizontal alignment of the watermark",
				"type" => "select",
				"default" => "center",
				"options" => [
					"left" => "Left",
					"center" => "Center",
					"right" => "Right",
				],
			],
			"media-cloud-mux-watermark-horizontal-margin" => [
				"title" => "Horizontal Margin",
				"description" => "The horizontal margin, in pixels, of the watermark image when applied to a 1920x1080 video.  This value will be scaled accordingly for different size videos.",
				"type" => "number",
				"default" => 0,
				"min" => 0,
				"max" => 1920
			],
			"media-cloud-mux-watermark-opacity" => [
				"title" => "Watermark Opacity",
				"description" => "The opacity of the watermark.",
				"type" => "number",
				"default" => 100,
				"min" => 1,
				"max" => 100
			],
		]
	],
	"media-cloud-video-encoding-integration" => [
		"title" => "WordPress Integration",
		"doc_link" => 'https://docs.mediacloud.press/articles/documentation/video-encoding/mux-integration',
		"options" => [
			"media-cloud-mux-process-uploads" => [
				"title" => "Import Uploaded Videos",
				"description" => "When enabled, after a video is uploaded to cloud storage it will be imported it to Mux Video.  If disabled, you must use the Mux upload tool.",
				"type" => "checkbox",
				"default" => true,
			],
			"media-cloud-mux-delete-uploads" => [
				"title" => "Delete Videos From Mux",
				"description" => "When enabled, when you delete a video from the media library, the associated asset on Mux will be deleted too.",
				"type" => "checkbox",
				"default" => false,
			],
		]
	],
	"media-cloud-video-encoding-transfer" => [
		"title" => "Transfer Options",
		"doc_link" => 'https://docs.mediacloud.press/articles/documentation/video-encoding/mux-integration',
		"options" => [
			"media-cloud-mux-transfer-enabled" => [
				"title" => "Transfer Encoded Videos",
				"description" => "When enabled, after a video is finished being encoded by Mux it will be transferred to cloud storage or to your local web server.  After the transfer, the video will be served from either location when using the Gutenberg Video Player block included with Media Cloud.  <strong>To use this feature background processing must be working.</strong>",
				"type" => "checkbox",
				"default" => false,
			],
			"media-cloud-mux-transfer-local-only" => [
				"title" => "Local Only",
				"description" => "When this option is enabled, the encoded video will be stored locally instead of transferred to cloud storage.",
				"type" => "checkbox",
				"default" => false,
			],
			"media-cloud-mux-transfer-path" => [
				"title" => "Destination Path",
				"description" => "The path to store the encoded videos.  For local storage this is relative to your upload directory.  For cloud storage, this is relative to the root path.",
				"type" => "text-field",
				"default" => "/videos/hls/",
			],
			"media-cloud-mux-delete-transferred" => [
				"title" => "Delete Transferred Videos From Mux",
				"description" => "When enabled, any transferred videos will be deleted from Mux after a successful transfer to local or cloud storage.",
				"type" => "checkbox",
				"default" => false,
			],
		]
	],
];
