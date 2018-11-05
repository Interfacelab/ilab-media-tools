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
use ILAB\MediaCloud\Tasks\BatchManager;
use ILAB\MediaCloud\Tools\Storage\DefaultProgressDelegate;
use ILAB\MediaCloud\Tools\Storage\ImportProgressDelegate;
use ILAB\MediaCloud\Tools\Storage\StorageTool;
use ILAB\MediaCloud\Tools\ToolsManager;
use ILAB\MediaCloud\Utilities\Logging\Logger;

if (!defined('ABSPATH')) { header('Location: /'); die; }

/**
 * Import to Cloud Storage, rebuild thumbnails, etc.
 * @package ILAB\MediaCloud\CLI\Storage
 */
class StorageCommands extends Command {
    private $debugMode = false;

	/**
	 * Imports the media library to the cloud.
	 *
	 * @when after_wp_load
	 *
	 * @param $args
	 * @param $assoc_args
	 */
	public function import($args, $assoc_args) {
	    $this->debugMode = (\WP_CLI::get_config('debug') == 'mediacloud');

	    // Force the logger to initialize
	    Logger::instance();

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
		    BatchManager::instance()->reset('storage');

            BatchManager::instance()->setStatus('storage', true);
            BatchManager::instance()->setTotalCount('storage', $query->post_count);
            BatchManager::instance()->setCurrent('storage', 1);
            BatchManager::instance()->setShouldCancel('storage', false);

			Command::Info("Total posts found: %Y{$query->post_count}.", true);

			$pd = new DefaultProgressDelegate();

			for($i = 1; $i <= $query->post_count; $i++) {
				$postId = $query->posts[$i - 1];
				$upload_file = get_attached_file($postId);
				$fileName = basename($upload_file);

                BatchManager::instance()->setCurrentFile('storage', $fileName);
                BatchManager::instance()->setCurrent('storage', $i);

				Command::Info("%w[%C{$i}%w of %C{$query->post_count}%w] %NImporting %Y$fileName%N %w(Post ID %N$postId%w)%N ... ", $this->debugMode);
				$storageTool->processImport($i - 1, $postId, $pd);
				if (!$this->debugMode) {
                    Command::Info("%YDone%N.", true);
                }
			}

			BatchManager::instance()->reset('storage');
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
            BatchManager::instance()->reset('thumbnails');

            BatchManager::instance()->setStatus('thumbnails', true);
            BatchManager::instance()->setTotalCount('thumbnails', $query->post_count);
            BatchManager::instance()->setCurrent('thumbnails', 1);
            BatchManager::instance()->setShouldCancel('thumbnails', false);

			Command::Info("Total posts found: %Y{$query->post_count}.", true);

			$pd = new DefaultProgressDelegate();

			for($i = 1; $i <= $query->post_count; $i++) {
				$postId = $query->posts[$i - 1];
				$upload_file = get_attached_file($postId);
				$fileName = basename($upload_file);

                BatchManager::instance()->setCurrentFile('thumbnails', $fileName);
                BatchManager::instance()->setCurrent('thumbnails', $i);

				Command::Info("%w[%C{$i}%w of %C{$query->post_count}%w] %NRegenerating thumbnails for %Y$fileName%N %w(%N$postId%w)%N ... ");
				$storageTool->regenerateFile($postId);
				Command::Info("%YDone%N.", true);
			}

            BatchManager::instance()->reset('thumbnails');
		}

	}

	public static function Register() {
		\WP_CLI::add_command('mediacloud', __CLASS__);
	}

}