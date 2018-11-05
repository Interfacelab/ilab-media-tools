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

namespace ILAB\MediaCloud\CLI\Rekognition;

use ILAB\MediaCloud\CLI\Command;
use ILAB\MediaCloud\Tasks\BatchManager;
use ILAB\MediaCloud\Tools\Rekognition\RekognitionTool;
use ILAB\MediaCloud\Tools\ToolsManager;

if (!defined('ABSPATH')) { header('Location: /'); die; }

/**
 * Functions related to Amazon Rekognition
 * @package ILAB\MediaCloud\CLI\Rekognition
 */
class RekognitionCommands extends Command {
	/**
	 * Processes the media library with Rekognition
	 *
	 * @when after_wp_load
	 *
	 * @param $args
	 * @param $assoc_args
	 */
	public function process($args, $assoc_args) {
		/** @var RekognitionTool $tool */
		$tool = ToolsManager::instance()->tools['rekognition'];

		if (!$tool || !$tool->enabled()) {
			Command::Error('Rekognition tool is not enabled in Media Cloud or the settings are incorrect.');
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
				posts.ID in (select post_id from wp_postmeta where meta_key = '_wp_attachment_metadata' and meta_value like '%s:2:"s3";a:%');
SQL;

		$rows = $wpdb->get_results($sql);

		$posts = [];
		foreach($rows as $row) {
			$posts[] = $row->ID;
		}

		$postCount = count($posts);
		if($postCount > 0) {
		    BatchManager::instance()->reset('rekognizer');


            BatchManager::instance()->setStatus('rekognizer', true);
            BatchManager::instance()->setTotalCount('rekognizer', $postCount);
            BatchManager::instance()->setCurrent('rekognizer', 1);
            BatchManager::instance()->setShouldCancel('rekognizer', false);

			Command::Info("Total posts found: %Y{$postCount}.", true);

			for($i = 1; $i <= $postCount; $i++) {
				$postId = $posts[$i - 1];
				$upload_file = get_attached_file($postId);
				$fileName = basename($upload_file);

                BatchManager::instance()->setCurrentFile('rekognizer', $fileName);
                BatchManager::instance()->setCurrent('rekognizer', $i);

				Command::Info("%w[%C{$i}%w of %C{$postCount}%w] %NProcessing %Y$fileName%N %w(%N$postId%w)%N ... ");

				$data = wp_get_attachment_metadata($postId);
				if (empty($data)) {
					Command::info( 'Missing metadata, skipping.', true);
					continue;
				}

				if (!isset($data['s3'])) {
					Command::info( 'Missing cloud storage metadata, skipping.', true);
					continue;
				}

				$data = $tool->processImageMeta($data, $postId);
				wp_update_attachment_metadata($postId, $data);

				Command::Info("%YDone%N.", true);
			}

            BatchManager::instance()->reset('rekognizer');
		}
	}

	public static function Register() {
		\WP_CLI::add_command('rekognition', __CLASS__);
	}

}