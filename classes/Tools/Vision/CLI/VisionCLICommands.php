<?php
// Copyright (c) 2016 Interfacelab LLC. All rights reserved.
//
// Released under the GPLv3 license
// http://www.gnu.org/licenses/gpl-3.0.html
//
// Uses code from:
// Persist Admin Notices Dismissal
// by Agbonghama Collins and Andy Fragen
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************

namespace ILAB\MediaCloud\Tools\Vision\CLI;

use ILAB\MediaCloud\CLI\Command;
use ILAB\MediaCloud\Tasks\BatchManager;
use ILAB\MediaCloud\Tools\Rekognition\VisionTool;
use ILAB\MediaCloud\Tools\ToolsManager;
use ILAB\MediaCloud\Vision\VisionManager;

if (!defined('ABSPATH')) { header('Location: /'); die; }

/**
 * Commands related to the Vision Tool
 */
class VisionCLICommands extends Command {
	/**
	 * Processes the media library with the selected vision provider
	 *
	 * ## OPTIONS
	 *
	 * [--limit=<number>]
	 * : The maximum number of items to process, default is infinity.
	 *
	 * [--offset=<number>]
	 * : The starting offset to process.  Cannot be used with page.
	 *
	 * [--page=<number>]
	 * : The starting offset to process.  Page numbers start at 1.  Cannot be used with offset.
	 *
	 * @when after_wp_load
	 *
	 * @param $args
	 * @param $assoc_args
	 */
	public function process($args, $assoc_args) {
		/** @var VisionTool $tool */
		$tool = ToolsManager::instance()->tools['vision'];

		if (!$tool || !$tool->enabled()) {
			Command::Error('Vision tool is not enabled in Media Cloud or the settings are incorrect.');
			return;
		}

		global $wpdb;

		$sql = <<<SQL
			select 
				posts.ID
			from
				{$wpdb->posts} posts
			where
				posts.post_type = 'attachment'
			and
				posts.post_status = 'inherit'
			and
				posts.post_mime_type in ('image/jpeg', 'image/jpg', 'image/png')
			and
				posts.ID in (select post_id from wp_postmeta where meta_key = '_wp_attachment_metadata' and meta_value like '%s:2:"s3";a:%')
SQL;

		if (isset($assoc_args['limit'])) {
			$limit = $assoc_args['limit'];
			$offset = 0;
			if (isset($assoc_args['offset'])) {
				$offset = $assoc_args['offset'];
			} else if (isset($assoc_args['page'])) {
				$offset = max(0,($assoc_args['page'] - 1) * $assoc_args['limit']);
			}

			$sql .= " limit $limit offset $offset";
		}

		$rows = $wpdb->get_results($sql);

		$posts = [];
		foreach($rows as $row) {
			$posts[] = $row->ID;
		}

		$postCount = count($posts);
		if($postCount > 0) {
		    BatchManager::instance()->reset('vision');


            BatchManager::instance()->setStatus('vision', true);
            BatchManager::instance()->setTotalCount('vision', $postCount);
            BatchManager::instance()->setCurrent('vision', 1);
            BatchManager::instance()->setShouldCancel('vision', false);

			Command::Info("Total posts found: %Y{$postCount}.", true);

			for($i = 1; $i <= $postCount; $i++) {
				$postId = $posts[$i - 1];
				$upload_file = get_attached_file($postId);
				$fileName = basename($upload_file);

                BatchManager::instance()->setCurrentFile('vision', $fileName);
                BatchManager::instance()->setCurrent('vision', $i);

				Command::Info("%w[%C{$i}%w of %C{$postCount}%w] %NProcessing %Y$fileName%N %w(%N$postId%w)%N ... ");

				$data = wp_get_attachment_metadata($postId);
				if (empty($data)) {
					Command::info( 'Missing metadata, skipping.', true);
					continue;
				}

				if (!isset($data['s3']) && (VisionManager::driver() == 'rekognition')) {
					Command::info( 'Missing cloud storage metadata, skipping.', true);
					continue;
				}

				$data = $tool->processImageMeta($data, $postId);
				wp_update_attachment_metadata($postId, $data);

				Command::Info("%YDone%N.", true);
			}

            BatchManager::instance()->reset('vision');
		}
	}

	public static function Register() {
		\WP_CLI::add_command('vision', __CLASS__);
	}

}