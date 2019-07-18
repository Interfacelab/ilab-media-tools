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

namespace ILAB\MediaCloud\Tools\Vision\Batch;

use ILAB\MediaCloud\Tasks\BatchManager;
use ILAB\MediaCloud\Tools\BatchTool;
use function ILAB\MediaCloud\Utilities\json_response;

class ImportVisionBatchTool extends BatchTool {
    //region Properties
    /**
     * Name/ID of the batch
     * @return string
     */
    public static function BatchIdentifier() {
        return 'vision';
    }

    /**
     * Title of the batch
     * @return string
     */
    public function title() {
        return "Process With Vision";
    }

    /**
     * The prefix to use for action names
     * @return string
     */
    public function batchPrefix() {
        return 'ilab_vision_importer';
    }

    /**
     * Fully qualified class name for the BatchProcess class
     * @return string
     */
    public static function BatchProcessClassName() {
        return "\\ILAB\\MediaCloud\\Tools\\Vision\\Batch\\ImportVisionBatchProcess";
    }

    /**
     * The view to render for instructions
     * @return string
     */
    public function instructionView() {
        return 'importer/instructions/vision';
    }

    /**
     * The menu slug for the tool
     * @return string
     */
    function menuSlug() {
        return 'media-tools-vision-importer';
    }
    //endregion

    //region Bulk Actions
    /**
     * Registers any bulk actions for integeration into the media list
     * @param $actions array
     * @return array
     */
    public function registerBulkActions($actions) {
        $actions['ilab_vision_process'] = 'Process with Vision';
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
        if('ilab_vision_process' === $action_name) {
            $posts_to_import = [];
            if (count($post_ids) > 0) {
                foreach($post_ids as $post_id) {
                    $meta = wp_get_attachment_metadata($post_id);
                    if (!empty($meta) && !isset($meta['s3'])) {
                        continue;
                    }

                    $mime = get_post_mime_type($post_id);
                    if (!in_array($mime, ['image/jpeg', 'image/jpg', 'image/png'])) {
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
    /**
     * Allows subclasses to filter the data used to render the tool
     * @param $data
     * @return array
     */
    protected function filterRenderData($data) {
        $data['disabledText'] = 'enable Vision';
        $data['commandLine'] = 'wp vision process';
        $data['commandTitle'] = 'Process Images';
        $data['cancelCommandTitle'] = 'Cancel Processing';

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

        $data = wp_get_attachment_metadata($pid);
        if (!empty($data) && isset($data['s3'])) {
            $data = $this->owner->processImageMeta($data, $pid);
            wp_update_attachment_metadata($pid, $data);
        }

        json_response(["status" => 'ok']);
    }
    //endregion

    //region BatchToolInterface
    public function toolInfo() {
        return [
            'title' => 'Vision Importer',
            'link' => admin_url('admin.php?page='.$this->menuSlug()),
            'description' => 'Processes all of the media in your library through Amazon Rekognition or Google Cloud Vision to automatically tag and classify your media.'
        ];
    }
    //endregion
}