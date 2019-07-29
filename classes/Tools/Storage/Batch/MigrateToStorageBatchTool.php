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

use ILAB\MediaCloud\Storage\StorageException;
use ILAB\MediaCloud\Storage\StorageSettings;
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

	//region Batch Actions
	/**
	 * Gets the post data to process for this batch.  Data is paged to minimize memory usage.
	 * @param $page
	 * @param bool $forceImages
	 * @param bool $allInfo
	 * @return array
	 */
	protected function getImportBatch($page, $forceImages = false, $allInfo = false) {
		$result = parent::getImportBatch($page, $forceImages, $allInfo);

		$skipThumbnails = (empty($_REQUEST['skip-thumbnails'])) ? false : ($_REQUEST['skip-thumbnails'] == 'on');
		$pathHandling = 'preserve';
		if (!empty($_REQUEST['preserve-upload-paths']) && in_array($_REQUEST['preserve-upload-paths'], ['replace', 'prepend'])) {
			$pathHandling = $_REQUEST['preserve-upload-paths'];
		}

		$result['options'] = [
			'skip-thumbnails' => $skipThumbnails,
			'path-handling' => $pathHandling
		];

		return $result;
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

		if (!empty($_REQUEST['sort-order']) && ($_REQUEST['sort-order'] != 'default')) {
			if (in_array($_REQUEST['sort-order'], ['date-asc', 'date-desc', 'title-asc', 'title-desc'])) {
				$parts = explode('-', $_REQUEST['sort-order']);
				$args['orderby'] = $parts[0];
				$args['order'] = strtoupper($parts[1]);
			} else if (in_array($_REQUEST['sort-order'], ['filename-asc', 'filename-desc'])) {
				$args['meta_key'] = '_wp_attached_file';
				$args['orderby'] = 'meta_value';
				$args['order'] = ($_REQUEST['sort-order'] == 'filename-asc') ? 'ASC' : 'DESC';
			}
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
        $data['commandLine'] = 'wp mediacloud import [--limit=<number>] [--offset=<number>] [--page=<number>] [--paths=preserve|replace|prepend] [--skip-thumbnails] [--order-by=date|title|filename] [--order=asc|desc]';
	    $data['commandTitle'] = 'Import Uploads';
	    $data['commandLink'] = admin_url('admin.php?page=media-cloud-docs&doc-page=advanced/command-line#import');
        $data['cancelCommandTitle'] = 'Cancel Import';

        $data['options'] = [
	        'skip-imported' => [
	        	"title" => "Skip Imported",
		        "description" => "Skip items that have already been imported.",
		        "type" => "checkbox",
		        "default" => true
	        ],
        ];


        $imgix = apply_filters('media-cloud/dynamic-images/enabled', false);
        if ($imgix) {
	        $data['options']['skip-thumbnails'] = [
		        "title" => "Skip Thumbnails",
		        "description" => "This will skip uploading thumbnails and other images sizes, only uploading the original master image.  This requires Imgix or Dynamic Images.",
		        "type" => "checkbox",
		        "default" => false
	        ];
        }

        if (!empty(StorageSettings::prefixFormat())) {
        	$warning = '';
        	if (strpos(StorageSettings::prefixFormat(), '@{date:') !== false) {
	        	$warning = "<p><strong>WARNING:</strong> Your custom upload prefix has a date in it, it will use today's date.  This means that all of your images will be placed in a folder for today's date.  It is recommended to remove the dynamic date from the prefix until after import.</p>";
	        }

        	$prefix = StorageSettings::prefix();

	        $data['options']['preserve-upload-paths'] = [
		        "title" => "Upload Paths",
		        "description" => "Controls where in cloud storage imported files are placed.  <p>Current custom prefix: <code>$prefix</code>.</p>$warning",
		        "type" => "select",
		        "options" => [
		        	'preserve' => 'Keep original upload path',
			        'replace' => "Replace upload path with custom prefix",
			        'prepend' => "Prepend upload path with custom prefix",
		        ],
		        "default" => 'preserve',
	        ];
        }

        $data['options']['sort-order'] = [
	        "title" => "Sort Order",
	        "description" => "Controls the order that items from your media library are migrated to cloud storage.",
	        "type" => "select",
	        "options" => [
		        'default' => 'Default',
		        'date-asc' => "Oldest first",
		        'date-desc' => "Newest first",
		        'title-asc' => "Title, A-Z",
		        'title-desc' => "Title, Z-A",
		        'filename-asc' => "File name, A-Z",
		        'filename-desc' => "File name, Z-A",
	        ],
	        "default" => 'default',
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

	    $skipThumbnails = (empty($_REQUEST['skip-thumbnails'])) ? false : ($_REQUEST['skip-thumbnails'] == 'on');
	    $pathHandling = 'preserve';
	    if (!empty($_REQUEST['preserve-upload-paths']) && in_array($_REQUEST['preserve-upload-paths'], ['replace', 'prepend'])) {
		    $pathHandling = $_REQUEST['preserve-upload-paths'];
	    }

	    $options = [
		    'skip-thumbnails' => $skipThumbnails,
		    'path-handling' => $pathHandling
	    ];

	    $pid = $_POST['post_id'];
        $this->owner->processImport(0, $pid, null, $options);

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