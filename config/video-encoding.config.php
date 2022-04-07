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
    "id" => "video-encoding",
    "name" => "Video Encoding",
	"description" => "Video encoding, hosting and live streaming via <a href='https://mux.com/' target='_blank'>Mux</a>.",
	"class" => "MediaCloud\\Plugin\\Tools\\Video\\Driver\\Mux\\MuxTool",
	"dependencies" => [
		"video-player",
	],
	"env" => "MCLOUD_MUX_ENABLED",
	"settings" => [
		"options-page" => "media-cloud-mux",
		"options-group" => "media-cloud-mux",
		"groups" => [
			"media-cloud-video-encoding-provider" => [
				"title" => "Video Encoding Provider",
				"description" => "To get Cloud Storage working, select a provider and supply the requested credentials.",
				"help" => [
					'target' => 'footer',
					'watch' => 'media-cloud-video-encoding-provider',
					'data' => 'providerHelp',
				],
				"options" => [
					"media-cloud-video-encoding-provider" => [
						"title" => "Video Encoding Provider",
						"type" => "select",
						"options" => [
							"mux" => "Mux",
						],
					],
				],
				"hide-save" => true
			],
			"media-cloud-mux-credentials" => [
				"title" => "Credentials",
				"doc_link" => 'https://support.mediacloud.press/articles/documentation/video-encoding/mux-credentials',
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
				]
			],
			"media-cloud-mux-encoding-settings" => [
				"title" => "Encoding Settings",
				"doc_link" => 'https://support.mediacloud.press/articles/documentation/video-encoding/mux-encoding-settings',
				"options" => [
					"media-cloud-mux-normalize-audio" => [
						"title" => "Normalize Audio",
						"description" => "Normalize the audio track loudness level.",
						"type" => "checkbox",
						"default" => false
					],
					"media-cloud-mux-per-title-encoding" => [
						"title" => "Per-Title Encoding",
						"description" => "Per-title encoding analyzes an individual video to determine the ideal encoding ladder. The result is that different videos are streamed at different resolutions and bitrates, and every video looks better - often by up to 20%-30%.",
						"type" => "checkbox",
						"default" => false
					],
					"media-cloud-mux-test-mode" => [
						"title" => "Test Mode",
						"description" => "Enabling test mode will allow you to evaluate the Mux Video APIs without incurring any cost. There is no limit on number of test assets created. Any encoded videos will be watermarked with the Mux logo, limited to 10 seconds and be deleted after 24 hrs",
						"type" => "checkbox",
						"default" => false
					],
				]
			],
			"media-cloud-mux-integration" => [
				"title" => "WordPress Integration",
				"doc_link" => 'https://support.mediacloud.press/articles/documentation/video-encoding/mux-integration',
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
		]
	]
];
