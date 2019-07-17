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

namespace ILAB\MediaCloud\Tools\Storage\Batch;

use ILAB\MediaCloud\Tasks\BatchManager;
use ILAB\MediaCloud\Tools\BatchTool;
use function ILAB\MediaCloud\Utilities\json_response;

class MigrateToStorageBatchTool extends BatchTool {
    //region Properties
    /**
     * Name/ID of the batch
     * @return string
     */
    public static function BatchIdentifier() {
        return 'storage';
    }

    /**
     * Title of the batch
     * @return string
     */
    public function title() {
        return "Migrate To Cloud";
    }

    /**
     * The prefix to use for action names
     * @return string
     */
    public function batchPrefix() {
        return 'ilab_storage_importer';
    }

    /**
     * Fully qualified class name for the BatchProcess class
     * @return string
     */
    public static function BatchProcessClassName() {
        return "\\ILAB\\MediaCloud\\Tools\\Storage\\Batch\\MigrateToStorageBatchProcess";
    }

    /**
     * The view to render for instructions
     * @return string
     */
    public function instructionView() {
        return 'importer/instructions/storage-importer';
    }

    /**
     * The menu slug for the tool
     * @return string
     */
    function menuSlug() {
        return 'media-tools-s3-importer';
    }
    //endregion

    //region Bulk Actions
    /**
     * Registers any bulk actions for integeration into the media list
     * @param $actions array
     * @return array
     */
    public function registerBulkActions($actions) {
        $actions['ilab_s3_import'] = 'Import to Cloud Storage';
        return $actions;
    }

    /**
     * Called to handle a bulk action
     *
     * @param $redirect_to
     * @param $action_name
     * @param $post_ids
     * @return string
     */
    public function handleBulkActions($redirect_to, $action_name, $post_ids) {
        if('ilab_s3_import' === $action_name) {
            $posts_to_import = [];
            if(count($post_ids) > 0) {
                foreach($post_ids as $post_id) {
                    $meta = wp_get_attachment_metadata($post_id);
                    if(!empty($meta) && isset($meta['s3'])) {
                        continue;
                    }

                    $posts_to_import[] = $post_id;
                }
            }

            if(count($posts_to_import) > 0) {
                set_site_transient($this->batchPrefix().'_post_selection', $posts_to_import, 10);
                return 'admin.php?page='.$this->menuSlug();
            }
        }

        return $redirect_to;
    }
    //endregion

    //region Actions
	protected function filterPostArgs($args) {
		$args = parent::filterPostArgs($args);

		if (!empty($_REQUEST['skip-imported']) && ($_REQUEST['skip-imported'] == 'on')) {
			$args['meta_query'] = [
				'relation' => 'AND',
				[
					'key'     => '_wp_attachment_metadata',
					'value'   => '"s3"',
					'compare' => 'NOT LIKE',
					'type'    => 'CHAR',
				],
				[
					'key'     => 'ilab_s3_info',
					'compare' => 'NOT EXISTS',
				],
			];
		}

		return $args;
	}


	/**
     * Allows subclasses to filter the data used to render the tool
     * @param $data
     * @return array
     */
    protected function filterRenderData($data) {
        $data['disabledText'] = 'enable Storage';
        $data['commandLine'] = 'wp mediacloud import [--limit=number] [--offset=number] [--page=number]';
        $data['commandTitle'] = 'Import Uploads';
        $data['cancelCommandTitle'] = 'Cancel Import';

        $data['options'] = [
	        'skip-imported' => [
		        "label" => "Skip items that have already been imported",
		        "default" => true
	        ],
        ];

        return $data;
    }

    /**
     * Process the import manually.  $_POST will contain a field `post_id` for the post to process
     */
    public function manualAction() {
        if (!isset($_POST['post_id'])) {
            BatchManager::instance()->setErrorMessage('storage', 'Missing required post data.');
            json_response(['status' => 'error']);
        }

        $pid = $_POST['post_id'];
        $this->owner->processImport(0, $pid, null);

        json_response(["status" => 'ok']);
    }
    //endregion

    //region BatchToolInterface
    public function toolInfo() {
        return [
          'title' => 'Storage Importer',
          'link' => admin_url('admin.php?page='.$this->menuSlug()),
          'description' => 'Uploads your existing media library to Amazon S3, Google Cloud Storage or any other storage provider that you have configured.'
        ];
    }
    //endregion
}