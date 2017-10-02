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

namespace ILAB\MediaCloud\Tools\Rekognition;

use ILAB\MediaCloud\Cloud\Storage\StorageManager;
use ILAB\MediaCloud\Tools\ToolBase;
use function ILAB\MediaCloud\Utilities\json_response;
use ILAB\MediaCloud\Utilities\View;
use ILAB\MediaCloud\Tasks\RekognizerProcess;
use ILAB\MediaCloud\Utilities\Logger;
use ILAB_Aws\Exception\AwsException;
use ILAB_Aws\Rekognition\RekognitionClient;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * Class ILabMediaRekognitionTool
 *
 * Debugging tool.
 */
class RekognitionTool extends ToolBase {
	//region Class Variables
	/** @var string|null */
	protected $key = null;

	/** @var string|null */
	protected $secret = null;

	/** @var string|null */
	protected $region;

	/** @var bool */
	protected $detectLabels = false;

	/** @var string|null */
	protected $detectLabelsTax = 'post_tag';

	/** @var int */
	protected $detectLabelsConfidence = 50;

	/** @var bool */
	protected $detectExplicit = false;

	/** @var bool */
	protected $detectExplicitTax = 'post_tag';

	/** @var int */
	protected $detectExplicitConfidence = 50;

	/** @var bool */
	protected $detectCelebrities = false;

	/** @var string|null */
	protected $detectCelebritiesTax = 'post_tag';

	/** @var bool */
	protected $detectFaces = false;

	/** @var array */
	protected $ignoredTags = [];
	//endregion

	//region Constructor
	public function __construct($toolName, $toolInfo, $toolManager) {
		parent::__construct($toolName, $toolInfo, $toolManager);

		new RekognizerProcess();

		$this->key = $this->getOption('ilab-media-s3-access-key', 'ILAB_AWS_S3_ACCESS_KEY');
		$this->secret = $this->getOption('ilab-media-s3-secret', 'ILAB_AWS_S3_ACCESS_SECRET');
		$this->region = $this->getOption('ilab-media-s3-rekognition-region', 'ILAB_AWS_REKOGNITION_REGION', false);

		$this->detectLabels = $this->getOption('ilab-media-s3-rekognition-detect-labels', 'ILAB_AWS_REKOGNITION_DETECT_LABELS', false);
		$this->detectLabelsTax = $this->getOption('ilab-media-s3-rekognition-detect-labels-tax', 'ILAB_AWS_REKOGNITION_DETECT_LABELS_TAX', 'post_tag');
		$this->detectLabelsConfidence = (int)$this->getOption('ilab-media-s3-rekognition-detect-labels-confidence', 'ILAB_AWS_REKOGNITION_DETECT_LABELS_CONFIDENCE', 50);
		$this->detectExplicit = $this->getOption('ilab-media-s3-rekognition-detect-moderation-labels', 'ILAB_AWS_REKOGNITION_MODERATION_LABELS', false);
		$this->detectExplicitTax = $this->getOption('ilab-media-s3-rekognition-detect-moderation-labels-tax', 'ILAB_AWS_REKOGNITION_MODERATION_LABELS_TAX', 'post_tag');
		$this->detectExplicitConfidence = (int)$this->getOption('ilab-media-s3-rekognition-detect-moderation-labels-confidence', 'ILAB_AWS_REKOGNITION_MODERATION_LABELS_CONFIDENCE', 50);
		$this->detectCelebrities = $this->getOption('ilab-media-s3-rekognition-detect-celebrity', 'ILAB_AWS_REKOGNITION_DETECT_CELEBRITY', false);
		$this->detectCelebritiesTax = $this->getOption('ilab-media-s3-rekognition-detect-celebrity-tax', 'ILAB_AWS_REKOGNITION_DETECT_CELEBRITY_TAX', 'post_tag');
		$this->detectFaces = $this->getOption('ilab-media-s3-rekognition-detect-faces', 'ILAB_AWS_REKOGNITION_DETECT_FACES', false);

		$this->detectLabelsConfidence = min(100, max(0, $this->detectLabelsConfidence));
		$this->detectExplicitConfidence = min(100, max(0, $this->detectExplicitConfidence));

		$toIgnoreString = get_option('ilab-media-s3-rekognition-ignored-tags', '');
		if (!empty($toIgnoreString)) {
			$toIgnore = explode(',', $toIgnoreString);
			foreach($toIgnore as $ignoredTag) {
				$this->ignoredTags[] = strtolower(trim($ignoredTag));
			}
		}

		if ($this->detectLabels || $this->detectFaces || $this->detectExplicit || $this->detectCelebrities) {
			$taxes = [];

			if ($this->detectLabels && !in_array($this->detectLabelsTax, $taxes)) {
				$taxes[] = $this->detectLabelsTax;
			}

			if ($this->detectExplicit && !in_array($this->detectExplicitTax, $taxes)) {
				$taxes[] = $this->detectExplicitTax;
			}

			if ($this->detectCelebrities && !in_array($this->detectCelebritiesTax, $taxes)) {
				$taxes[] = $this->detectCelebritiesTax;
			}

			add_action( 'init' , function() use ($taxes) {
				foreach($taxes as $tax) {
					if (in_array($tax, ['post_tag', 'category'])) {
						register_taxonomy_for_object_type($tax, 'attachment');
					}
				}

			});

		}

		if (is_admin()) {
			$this->setupAdmin();
		}

		add_filter('ilab_rekognition_enabled', function($enabled){
			return $this->enabled();
		});

		add_filter('ilab_rekognition_detects_faces', function($enabled){
			return $this->detectFaces || $this->detectCelebrities;
		});
	}
	//endregion

	//region ToolBase Overrides
	public function enabled() {
		if (!parent::enabled()) {
			return false;
		}

		if (StorageManager::driver() != 's3') {
			return false;
		}

		if (empty($this->region) || empty($this->key) || empty($this->secret)) {
			return false;
		}

		$client = StorageManager::storageInstance();
		if (!$client->enabled()) {
			return false;
		}

		return ($this->detectLabels || $this->detectFaces || $this->detectExplicit || $this->detectCelebrities);
	}

	public function registerMenu($top_menu_slug) {
		parent::registerMenu($top_menu_slug);

		if ($this->enabled()) {
			add_submenu_page( $top_menu_slug, 'Rekognizer Importer', 'Rekognizer Importer', 'manage_options', 'media-tools-rekognizer-importer', [$this,'renderImporter']);
		}
	}
	//endregion

	//region Settings Helpers
	/**
	 * Returns a list of taxonomies for Attachments, used in the Rekognition settings page.
	 * @return array
	 */
	public function attachmentTaxonomies() {
		$taxonomies = [
			'category' => 'Category',
			'post_tag' => 'Tag'
		];

		$attachTaxes = get_object_taxonomies('attachment');
		if (!empty($attachTaxes)) {
			foreach($attachTaxes as $attachTax) {
				if (!in_array($attachTax, ['post_tag', 'category'])) {
					$taxonomies[$attachTax] = ucwords(str_replace('_', ' ', $attachTax));
				}
			}
		}


		return $taxonomies;
	}
	//endregion

	//region Admin Setup
	/**
	 * Handles the Rekognition bulk import action
	 *
	 * @param string $redirect_to
	 * @param string $action_name
	 * @param array $post_ids
	 *
	 * @return string
	 */
	public function handleBulkAction($redirect_to, $action_name, $post_ids) {
		if ('ilab_rekognizer_process' === $action_name) {
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

			if (count($posts_to_import) > 0) {
				update_option('ilab_rekognizer_status', true);
				update_option('ilab_rekognizer_total_count', count($posts_to_import));
				update_option('ilab_rekognizer_current', 1);
				update_option('ilab_rekognizer_should_cancel', false);

				$process = new RekognizerProcess();

				for($i = 0; $i < count($posts_to_import); ++$i) {
					$process->push_to_queue(['index' => $i, 'post' => $posts_to_import[$i]]);
				}

				$process->save();
				$process->dispatch();

				return 'admin.php?page=media-tools-rekognizer-importer';
			}
		}

		return $redirect_to;
	}

	/**
	 * Admin setup
	 */
	public function setupAdmin() {
		add_filter('ilab_s3_after_upload', [$this, 'processImageMeta'], 1000, 2);

		add_action('wp_ajax_ilab_rekognizer_process_images', [$this,'processImages']);
		add_action('wp_ajax_ilab_rekognizer_process_progress', [$this,'processProgress']);
		add_action('wp_ajax_ilab_rekognizer_cancel_process', [$this,'cancelProcessMedia']);

		add_action('admin_init',function(){
			if ($this->enabled()) {
				add_filter('bulk_actions-upload', function($actions){
					$actions['ilab_rekognizer_process'] = 'Process with Rekognizer';
					return $actions;
				});

				add_filter('handle_bulk_actions-upload', [$this,'handleBulkAction'], 1000, 3);
			}
		});
	}
	//endregion

	//region Processing
	/**
	 * Process an image through Rekognition
	 *
	 * @param array $meta
	 * @param int $postID
	 *
	 * @return array
	 */
	public function processImageMeta($meta, $postID) {
		if (!$this->enabled()) {
			return $meta;
		}

		if (!isset($meta['s3'])) {
			Logger::warning( "Post $postID is  missing 's3' metadata.", $meta);
			return $meta;
		}

		$s3 = $meta['s3'];

		if (!isset($s3['mime-type'])) {
			Logger::warning( "Post $postID is  missing 's3/mime-type' metadata.", $meta);
			return $meta;
		}

		$mime_parts = explode('/', $s3['mime-type']);
		if ((count($mime_parts)!=2) || ($mime_parts[0] != 'image') || (!in_array($mime_parts[1],['jpg','jpeg', 'png']))) {
			Logger::warning( "Post $postID is has invalid or missing mime-type.", $meta);
			return $meta;
		}

		Logger::info( "Processing Image Meta: $postID", $meta);

		$config = [
			'version' => 'latest',
			'region' => $this->region,
			'credentials' => [
				'key'    => $this->key,
				'secret' => $this->secret
			]
		];

		$rekt = new RekognitionClient($config);

		if ($this->detectLabels) {
			try {
				$result = $rekt->detectLabels([
					                              'Attributes' => ['ALL'],
					                              'Image' => [
						                              'S3Object' => [
							                              'Bucket' => $s3['bucket'],
							                              'Name' => $s3['key']
						                              ]
					                              ],
					                              'MinConfidence' => $this->detectLabelsConfidence
				                              ]);

				$labels = $result->get('Labels');

				if (!empty($labels)) {
					$tags = [];
					foreach($labels as $label) {
						if (!in_array(strtolower($label['Name']), $this->ignoredTags)) {
							$tags[] = [
								'tag' => $label['Name']
							];
						}
					}

					$this->processTags($tags, $this->detectLabelsTax, $postID);

					Logger::info( 'Detect Labels', $tags);
				}
			} catch (AwsException $ex) {
				Logger::error( 'Detect Labels Error', [ 'exception' =>$ex->getMessage()]);
				return $meta;
			}
		}

		if ($this->detectExplicit) {
			try {
				$result = $rekt->detectModerationLabels([
					                                        'Image' => [
						                                        'S3Object' => [
							                                        'Bucket' => $s3['bucket'],
							                                        'Name' => $s3['key']
						                                        ]
					                                        ],
					                                        //					                                        'MinConfidence' => $this->detectExplicitConfidence
				                                        ]);

				$labels = $result->get('ModerationLabels');
				if (!empty($labels)) {
					$tags = [];
					foreach($labels as $label) {
						if (!in_array(strtolower($label['Name']), $this->ignoredTags)) {
							$tag = [
								'tag' => $label['Name']
							];

							if (!empty($label['ParentName'])) {
								$tag['parent'] = $label['ParentName'];
							}

							$tags[] = $tag;
						}
					}

					$this->processTags($tags, $this->detectExplicitTax, $postID);
				}

				Logger::info( 'Detect Moderation Labels', $result->toArray());
			} catch (AwsException $ex) {
				Logger::error( 'Detect Moderation Error', [ 'exception' =>$ex->getMessage()]);
				return $meta;
			}
		}

		if ($this->detectCelebrities) {
			try {
				$result = $rekt->recognizeCelebrities([
					                                      'Attributes' => ['ALL'],
					                                      'Image' => [
						                                      'S3Object' => [
							                                      'Bucket' => $s3['bucket'],
							                                      'Name' => $s3['key']
						                                      ]
					                                      ]
				                                      ]);

				$allFaces = [];

				$celebs = $result->get('CelebrityFaces');
				if (!empty($celebs)) {
					$tags = [];

					foreach($celebs as $celeb) {
						$ignoreCeleb = in_array(strtolower($celeb['Name']), $this->ignoredTags);

						$face = $celeb['Face'];
						if (!$ignoreCeleb) {
							$face['celeb'] = $celeb['Name'];
							$tags[] = [
								'tag' => $celeb['Name']
							];
						}

						$allFaces[] = $face;
					}

					$this->processTags($tags, $this->detectCelebritiesTax, $postID);
				}

				$otherFaces = $result->get('UnrecognizedFaces');
				if (!empty($otherFaces)) {
					foreach($otherFaces as $face) {
						$allFaces[] = $face;
					}
				}

				if (!empty($allFaces)) {
					$meta['faces'] = $allFaces;
				}

				Logger::info( 'Detect Celebrities', $result->toArray());
			} catch (AwsException $ex) {
				Logger::error( 'Detect Celebrities Error', [ 'exception' =>$ex->getMessage()]);
				return $meta;
			}
		}

		if ($this->detectFaces) {
			try {
				$result = $rekt->detectFaces([
					                             'Attributes' => ['ALL'],
					                             'Image' => [
						                             'S3Object' => [
							                             'Bucket' => $s3['bucket'],
							                             'Name' => $s3['key']
						                             ]
					                             ]
				                             ]);

				$faces = $result->get('FaceDetails');
				if (!empty($faces)) {
					$meta['faces'] = $faces;
				}

				Logger::info( 'Detect Faces', $result->toArray());
			} catch (AwsException $ex) {
				Logger::error( 'Detect Faces Error', [ 'exception' =>$ex->getMessage()]);
				return $meta;
			}
		}

		return $meta;
	}

	/**
	 * Process the tags found with Rekognition
	 *
	 * @param array $tags
	 * @param string $tax
	 * @param int $postID
	 */
	private function processTags($tags, $tax, $postID) {
		if (empty($tags)) {
			return;
		}

		$tagsToAdd = [];
		foreach($tags as $tag) {
			$term = false;
			if (term_exists($tag['tag'], $tax)) {
				$term = get_term_by('name', $tag['tag'], $tax);
			} else {
				$parent = false;
				if (isset($tag['parent'])) {
					if (!term_exists($tag['parent'])) {
						$parentTermInfo = wp_insert_term($tag['parent'], $tax);
						$parent = get_term_by('id', $parentTermInfo['term_id'], $tax);
					} else {
						$parent = get_term_by('name', $tag['parent'], $tax);
					}
				}

				$tagInfo = [];

				if ($parent) {
					$tagInfo['parent'] = $parent->term_id;
				}

				$tagInfo = wp_insert_term($tag['tag'], $tax, $tagInfo);
				$term = get_term_by('id', $tagInfo['term_id'], $tax);
			}

			if ($term) {
				$tagsToAdd[] = $term->term_id;
			}
		}

		if (!empty($tagsToAdd)) {
			wp_set_object_terms($postID, $tagsToAdd, $tax, true);
		}
	}
	//endregion

	//region Importer
	/**
	 * Renders the Rekognition Import UI
	 */
	public function renderImporter() {
		$enabled = $this->enabled();

		$shouldCancel = get_option('ilab_rekognizer_should_cancel', false);
		$status = get_option('ilab_rekognizer_status', false);
		$total = get_option('ilab_rekognizer_total_count', 0);
		$current = get_option('ilab_rekognizer_current', 1);
		$currentFile = get_option('ilab_rekognizer_current_file', '');

		if ($total == 0) {
			$attachments = get_posts([
				                         'post_type'=> 'attachment',
				                         'posts_per_page' => -1
			                         ]);

			$total = count($attachments);
		}

		$progress = 0;

		if ($total > 0) {
			$progress = ($current / $total) * 100;
		}

		echo View::render_view( 'rekognizer/ilab-rekognizer-processor.php', [
			'status' => ($status) ? 'running' : 'idle',
			'total' => $total,
			'progress' => $progress,
			'current' => $current,
			'currentFile' => $currentFile,
			'enabled' => $enabled,
			'shouldCancel' => $shouldCancel
		]);
	}

	/**
	 * Ajax callback to start the import process.
	 */
	public function processImages() {
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

		if (count($posts) > 0) {
			update_option('ilab_rekognizer_status', true);
			update_option('ilab_rekognizer_total_count', count($posts));
			update_option('ilab_rekognizer_current', 1);
			update_option('ilab_rekognizer_should_cancel', false);

			$process = new RekognizerProcess();

			for($i = 0; $i < count($posts); ++$i) {
				$process->push_to_queue(['index' => $i, 'post' => $posts[$i]]);
			}

			$process->save();
			$process->dispatch();
		} else {
			delete_option('ilab_rekognizer_status');
		}

		header('Content-type: application/json');
		echo '{"status":"running"}';
		die;
	}

	/**
	 * Ajax endpoint to get progress information
	 */
	public function processProgress() {
		$shouldCancel = get_option('ilab_rekognizer_should_cancel', false);
		$status = get_option('ilab_rekognizer_status', false);
		$total = get_option('ilab_rekognizer_total_count', 0);
		$current = get_option('ilab_rekognizer_current', 0);
		$currentFile = get_option('ilab_rekognizer_current_file', '');

		header('Content-type: application/json');
		echo json_encode([
			                 'status' => ($status) ? 'running' : 'idle',
			                 'total' => (int)$total,
			                 'current' => (int)$current,
			                 'currentFile' => $currentFile,
			                 'shouldCancel' => $shouldCancel
		                 ]);
		die;
	}

	/**
	 * Cancels the Rekognition import process.
	 */
	public function cancelProcessMedia() {
		update_option('ilab_rekognizer_should_cancel', 1);
		RekognizerProcess::cancelAll();

		json_response(['status'=>'ok']);
	}
	//endregion
}