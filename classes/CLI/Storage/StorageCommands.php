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

namespace ILAB\MediaCloud\CLI\Storage;

use ILAB\MediaCloud\CLI\Command;
use ILAB\MediaCloud\Cloud\Storage\StorageSettings;
use ILAB\MediaCloud\Tools\Storage\DefaultProgressDelegate;
use ILAB\MediaCloud\Tools\Storage\ImportProgressDelegate;
use ILAB\MediaCloud\Tools\Storage\StorageTool;
use ILAB\MediaCloud\Tools\ToolsManager;

if (!defined('ABSPATH')) { header('Location: /'); die; }

/**
 * Import to Cloud Storage, rebuild thumbnails, etc.
 * @package ILAB\MediaCloud\CLI\Storage
 */
class StorageCommands extends Command {
	/**
	 * Imports the media library to the cloud.
	 *
	 * @when after_wp_load
	 *
	 * @param $args
	 * @param $assoc_args
	 */
	public function import($args, $assoc_args) {
		/** @var StorageTool $storageTool */
		$storageTool = ToolsManager::instance()->tools['storage'];

		if (!$storageTool || !$storageTool->enabled()) {
			Command::Error('Storage tool is not enabled in Media Cloud or the settings are incorrect.');
			return;
		}

		$postArgs = [
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'nopaging' => true,
			'fields' => 'ids',
		];

		if(!StorageSettings::uploadDocuments()) {
			$args['post_mime_type'] = 'image';
		}

		$query = new \WP_Query($postArgs);

		if($query->post_count > 0) {
			update_option('ilab_s3_import_status', true);
			update_option('ilab_s3_import_total_count', $query->post_count);
			update_option('ilab_s3_import_current', 1);
			update_option('ilab_s3_import_should_cancel', false);

			Command::Info("Total posts found: %Y{$query->post_count}.", true);

			$pd = new DefaultProgressDelegate();

			for($i = 1; $i <= $query->post_count; $i++) {
				$postId = $query->posts[$i - 1];
				$upload_file = get_attached_file($postId);
				$fileName = basename($upload_file);

				Command::Info("%w[%C{$i}%w of %C{$query->post_count}%w] %NImporting %Y$fileName%N %w(%N$postId%w)%N ... ");
				$storageTool->processImport($i - 1, $postId, $pd);
				Command::Info("%YDone%N.", true);
			}

			delete_option('ilab_s3_import_status');
		}
	}

	/**
	 * Regenerate thumbnails
	 *
	 * @when after_wp_load
	 *
	 * @param $args
	 * @param $assoc_args
	 */
	public function regenerate($args, $assoc_args) {
		/** @var StorageTool $storageTool */
		$storageTool = ToolsManager::instance()->tools['storage'];

		if (!$storageTool || !$storageTool->enabled()) {
			Command::Error('Storage tool is not enabled in Media Cloud or the settings are incorrect.');
			return;
		}

		$postArgs = [
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'nopaging' => true,
			'post_mime_type' => 'image',
			'fields' => 'ids',
		];

		$query = new \WP_Query($postArgs);

		if($query->post_count > 0) {
			update_option('ilab_cloud_regenerate_status', true);
			update_option('ilab_cloud_regenerate_total_count', $query->post_count);
			update_option('ilab_cloud_regenerate_current', 1);
			update_option('ilab_cloud_regenerate_should_cancel', false);

			Command::Info("Total posts found: %Y{$query->post_count}.", true);

			$pd = new DefaultProgressDelegate();

			for($i = 1; $i <= $query->post_count; $i++) {
				$postId = $query->posts[$i - 1];
				$upload_file = get_attached_file($postId);
				$fileName = basename($upload_file);

				update_option('ilab_cloud_regenerate_current_file', $fileName);
				update_option('ilab_cloud_regenerate_current', $i);

				Command::Info("%w[%C{$i}%w of %C{$query->post_count}%w] %NRegenerating thumbnails for %Y$fileName%N %w(%N$postId%w)%N ... ");
				$storageTool->regenerateFile($postId);
				Command::Info("%YDone%N.", true);
			}

			delete_option('ilab_cloud_regenerate_status');
		}

	}

	public static function Register() {
		\WP_CLI::add_command('mediacloud', __CLASS__);
	}

}