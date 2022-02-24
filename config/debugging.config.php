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
    "id" => "debugging",
    "name" => "Debugging",
	"description" => "Enables logging for the plugin to track down issues.",
	"class" => "MediaCloud\\Plugin\\Tools\\Debugging\\DebuggingTool",
	"dependencies" => [],
	"env" => "ILAB_MEDIA_DEBUGGING_ENABLED",
	"settings" => [
		"options-page" => "media-tools-debugging",
		"options-group" => "ilab-media-debugging",
		"groups" => [
			"ilab-media-s3-debug-settings" => [
				"title" => "Debug Settings",
				"options" => [
					"mcloud-debug-logging-level" => [
						"title" => "Logging Level",
						"description" => "The logging level to use. To disable logging for Media Cloud, set it to <code>None</code>.",
						"type" => "select",
						"default" => "info",
						"options" => [
							"none" => "None",
							"info" => "Info",
							"warning" => "Warning",
							"error" => "Error"
						]
					],
					"mcloud-debug-content-filtering" => [
						"title" => "Debug Content Filtering",
						"description" => "If you are seeing issues with URLs not being updated correctly, enable this to troubleshoot.  When enabled, it will log content filtering as well as generate a report in <code style='white-space: nowrap'>".\MediaCloud\Plugin\Tasks\TaskReporter::reporterDirectory()."</code>.  <strong>DO NOT LEAVE THIS RUNNING.</strong>  Only use to troubleshoot specific pages and then make sure to turn it off.",
						"type" => "checkbox",
						"default" => false,
					],
					"mcloud-debug-max-database-entries" => [
						"title" => "Maximum Database Entries",
						"description" => "The maximum number of log entries to keep in the database.  The default is 1000.",
						"type" => "number",
						"step" => 1,
						"min" => 100,
						"max" => 1000000,
                        "default" => 1000
					],
					"mcloud-debug-use-ray" => [
						"title" => "Use Spatie Ray",
						"description" => "If enabled, and <a href='https://spatie.be/products/ray' target='_blank'>Spatie's Ray</a> is installed in your project either through their WordPress plugin or via composer, Media Cloud's logger will send log entries to their Ray app.",
						"type" => "checkbox",
						"default" => true,
					],
					"mcloud-debug-remote-url" => [
						"title" => "Log Target Host",
						"description" => "The remote log target to send logs to, this will be supplied by Media Cloud support.",
						"type" => "text-field",
					],
					"mcloud-debug-remote-url-port" => [
						"title" => "Log Target Port",
						"description" => "The port number for the remote log target to send logs to, this will be supplied by Media Cloud support.",
						"type" => "text-field",
					],
					"mcloud-debug-ignored-regex" => [
						"title" => "Ignored Regex Filters",
						"description" => "One regex per line that will filter out debug messages.",
						"type" => "text-area",
					],
				]
			]
		]
	]
];