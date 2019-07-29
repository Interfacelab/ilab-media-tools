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

namespace ILAB\MediaCloud\Tools;

use ILAB\MediaCloud\Storage\StorageSettings;
use ILAB\MediaCloud\Tasks\BatchManager;
use ILAB\MediaCloud\Utilities\Environment;
use ILAB\MediaCloud\Utilities\Logging\Logger;
use ILAB\MediaCloud\Utilities\View;
use function ILAB\MediaCloud\Utilities\json_response;


if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

abstract class BatchTool implements BatchToolInterface {
    /** @var null|Tool */
    protected $owner = null;

    /** @var bool  */
    protected $mediaListIntegration = true;

    //region Constructor/Setup

    /**
     * BatchTool constructor.
     * @param $ownerTool Tool The tool that owns this batch tool
     */
    public function __construct($ownerTool) {
        $this->owner = $ownerTool;

        $this->mediaListIntegration = Environment::Option('mcloud-storage-display-media-list', null, true);

        if(is_admin()) {

            add_action('wp_ajax_'.$this->startActionName(), [$this, 'startAction']);
            add_action('wp_ajax_'.$this->progressActionName(), [$this, 'progressAction']);
            add_action('wp_ajax_'.$this->nextBatchActionName(), [$this, 'nextBatchAction']);
            add_action('wp_ajax_'.$this->manualActionName(), [$this, 'manualAction']);
            add_action('wp_ajax_'.$this->cancelActionName(), [$this, 'cancelAction']);
        }
    }

    /**
     * Performs any additional setup
     */
    public function setup () {
        if ($this->enabled()) {
            BatchManager::instance()->displayAnyErrors(static::BatchIdentifier());

            if ($this->mediaListIntegration) {
                add_action('admin_init', function() {
                    add_filter('bulk_actions-upload', function($actions) {
                        return $this->registerBulkActions($actions);
                    });

                    add_filter('handle_bulk_actions-upload', function($redirect_to, $action_name, $post_ids) {
                        return $this->handleBulkActions($redirect_to, $action_name, $post_ids);
                    }, 1000, 3);
                });
            }
        }
    }

    //endregion

    //region Properties

    /**
     * Determines if this tool is enabled
     * @return bool
     */
    public function enabled() {
        return $this->owner->enabled();
    }

    /**
     * Name/ID of the batch
     * @return string|null
     */
    static public function BatchIdentifier() {
    	return null;
    }

    /**
     * Title of the batch
     * @return string
     */
    abstract public function title();

    /**
     * The prefix to use for action names
     * @return string
     */
    abstract public function batchPrefix();

    /**
     * Fully qualified class name for the BatchProcess class
     * @return string|null
     */
    static public function BatchProcessClassName() {
    	return null;
    }

    /**
     * The view to render for instructions
     * @return string
     */
    abstract public function instructionView();

    /**
     * The page title for the importer page
     * @return string
     */
    function pageTitle() {
        return $this->title();
    }

    /**
     * The menu title for the importer page
     * @return string
     */
    function menuTitle() {
        return $this->title();
    }

    /**
     * The user's required capabilities to use the importer
     * @return string
     */
    function capabilityRequirement() {
        return 'manage_options';
    }

    /**
     * The menu slug for the tool
     * @return string
     */
    abstract function menuSlug();

    /**
     * The name identifier for the start action
     * @return string
     */
    public function startActionName() {
        return $this->batchPrefix().'_start';
    }

    /**
     * The name identifier for the progress action
     * @return string
     */
    public function progressActionName() {
        return $this->batchPrefix().'_progress';
    }

    /**
     * The name identifier for the next batch fetch action
     * @return string
     */
    public function nextBatchActionName() {
        return $this->batchPrefix().'_next-batch';
    }

    /**
     * The name identifier for the manual (client side batch processing) action
     * @return string
     */
    public function manualActionName() {
        return $this->batchPrefix().'_manual';
    }

    /**
     * The name identifier for the cancel action
     * @return string
     */
    public function cancelActionName() {
        return $this->batchPrefix().'_cancel';
    }

    //endregion

    //region Bulk Actions
    /**
     * Registers any bulk actions for integeration into the media list
     * @param $actions array
     * @return array
     */
    public function registerBulkActions($actions) {
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
        return $redirect_to;
    }
    //endregion

    //region Batch Actions
    /**
     * Gets the post data to process for this batch.  Data is paged to minimize memory usage.
     * @param $page
     * @param bool $forceImages
	 * @param bool $allInfo
	 *
	 * @return array
	 */
    protected function getImportBatch($page, $forceImages = false, $allInfo = false) {
        $total = 0;
        $pages = 1;
        $shouldRun = false;
        $fromSelection = false;

        if (isset($_POST['selection'])) {
            $postIds = $_POST['selection'];
            $total = count($postIds);
            $shouldRun = true;
        } else {
            $postIds = get_site_transient($this->batchPrefix().'_post_selection');
            if (!empty($postIds)) {
                delete_site_transient($this->batchPrefix().'_post_selection');
                $total = count($postIds);
                $shouldRun = true;
                $fromSelection = true;
            } else {
                $args = [
                    'post_type' => 'attachment',
                    'post_status' => 'inherit',
                    'posts_per_page' => 100,
                    'fields' => 'ids',
                    'paged' => $page
                ];

                if ($page == -1) {
                    unset($args['posts_per_page']);
                    unset($args['paged']);
                    $args['nopaging'] = true;
                }

                $args = $this->filterPostArgs($args);

                if($forceImages || !StorageSettings::uploadDocuments()) {
                    $args['post_mime_type'] = 'image';
                }

                $query = new \WP_Query($args);

                $postIds = $query->posts;

                $total = (int)$query->found_posts;
                $pages = $query->max_num_pages;
            }
        }


        $posts = [];
        $first = true;
        foreach($postIds as $post) {
        	if ($first || $allInfo) {
		        $thumb = wp_get_attachment_image_src($post, 'thumbnail', true);

		        $thumbUrl = null;
		        $icon = false;
		        if (!empty($thumb)) {
			        $thumbUrl = $thumb[0];
			        $icon = (($thumb[1] != 150) && ($thumb[2] != 150));
		        }

		        $posts[] = [
			        'id' => $post,
			        'title' => pathinfo(get_attached_file($post), PATHINFO_BASENAME),
			        'thumb' => $thumbUrl,
			        'icon' => $icon
		        ];

        		$first = false;
	        } else {
		        $posts[] = [
			        'id' => $post,
//			        'title' => pathinfo(get_attached_file($post), PATHINFO_BASENAME),
		        ];
	        }
        }

        return [
            'posts' =>$posts,
            'total' => $total,
            'pages' => $pages,
	        'options' => [],
            'shouldRun' => $shouldRun,
            'fromSelection' => $fromSelection
        ];
    }

    /**
     * Subclass to filter the args used to query posts.
     * @param $args
     * @return array
     */
    protected function filterPostArgs($args) {
        return $args;
    }

    /**
     * Renders the batch tool
     */
    public function renderBatchTool() {
        $data = BatchManager::instance()->stats(static::BatchIdentifier());

        $postData = $this->getImportBatch(1, false, true);
        $data['posts'] = $postData['posts'];
        $data['pages'] = $postData['pages'];

	    $background = Environment::Option('mcloud-storage-batch-background-processing', null, true);
	    $commandLine = Environment::Option('mcloud-storage-batch-command-line-processing', null, false);

        if (!$background) {
            $data['total'] = $postData['total'];
        } else {
            if($data['total'] == 0) {
                $data['total'] = $postData['total'];
            }
        }

        if ($postData['shouldRun']) {
            $data['status'] = 'running';
        } else {
            $data['status'] =  ($data['running']) ? 'running' : 'idle';
        }
        $data['shouldRun'] = $postData['shouldRun'];
        $data['enabled'] = $this->enabled();
        $data['title'] = $this->title();
        $data['instructions'] = View::render_view($this->instructionView(), ['background' => $background]);
        $data['fromSelection'] = $postData['fromSelection'];
        $data['disabledText'] = 'enable Storage';
        $data['commandLine'] = null;
        $data['commandTitle'] = 'Run Tool';
        $data['cancelCommandTitle'] = 'Cancel Tool';

        $data['cancelAction'] = $this->cancelActionName();
        $data['startAction'] = $this->startActionName();
        $data['manualAction'] = $this->manualActionName();
        $data['progressAction'] = $this->progressActionName();
        $data['nextBatchAction'] = $this->nextBatchActionName();
        $data['background'] = $background || $commandLine;

        $data = $this->filterRenderData($data);
        echo View::render_view('importer/importer.php', $data);
    }

    /**
     * Allows subclasses to filter the data used to render the tool
     * @param $data
     * @return array
     */
    protected function filterRenderData($data) {
        return $data;
    }

    /**
     * The action that starts a batch in motion
     */
    public function startAction() {
    	Logger::info('Starting batch.');
    	Logger::info('Getting posts for batch.');
        $posts = $this->getImportBatch(-1);
	    Logger::info('Found '.$posts['total'].' posts for batch.');

        if($posts['total'] > 0) {
            try {
                $postIDs = [];
                foreach($posts['posts'] as $post) {
                    $postIDs[] = $post['id'];
                }

                Logger::info('Adding posts to batch to run.');
                BatchManager::instance()->addToBatchAndRun(static::BatchIdentifier(), $postIDs, $posts['options']);
	            Logger::info('Finished adding posts to batch to run.');
            } catch (\Exception $ex) {
                json_response(["status"=>"error", "error" => $ex->getMessage()]);
            }
        } else {
            BatchManager::instance()->reset(static::BatchIdentifier());
            json_response(['status' => 'finished']);
        }

	    Logger::info('Sending JSON response.');
        $data = [
            'status' => 'running',
            'total' => $posts['total'],
            'first' => $posts['posts'][0]
        ];

        wp_send_json($data, 200);
    }

    /**
     * Reports progress on a batch
     */
    public function progressAction() {
        $data = BatchManager::instance()->stats(static::BatchIdentifier());

        $data['status'] =  ($data['running']) ? 'running' : 'idle';
        $data['enabled'] = $this->enabled();

        json_response($data);
    }

    /**
     * Fetches the next group of posts to process
     */
    public function nextBatchAction() {
        $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;

        $postData = $this->getImportBatch($page, false, true);

        json_response($postData);
    }

    /**
     * Process the import manually.  $_POST will contain a field `post_id` for the post to process
     */
    abstract public function manualAction();

    /**
     * Cancels the batch
     */
    public function cancelAction() {
	    $background = Environment::Option('mcloud-storage-batch-background-processing', null, true);
	    if (!$background) {
		    BatchManager::instance()->reset(static::BatchIdentifier());
	    }

        BatchManager::instance()->setShouldCancel(static::BatchIdentifier(), true);

        call_user_func([static::BatchProcessClassName(), 'cancelAll']);

        json_response(['status' => 'ok']);
    }

    //endregion

    //region BatchToolInterface
    public function toolInfo() {
        return [];
    }
    //endregion
}