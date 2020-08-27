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

namespace MediaCloud\Plugin\Tasks;

use MediaCloud\Plugin\Tools\Storage\StorageToolSettings;
use MediaCloud\Plugin\Utilities\Logging\Logger;

abstract class AttachmentTask extends Task {

	/**
	 * Update the statistics specific to a post ID
	 *
	 * @param $post_id
	 *
	 * @throws \Exception
	 */
	protected function updateCurrentPost($post_id) {
		$this->currentItemID = $post_id;

		$file = get_attached_file($post_id);
		if (empty($file)) {
			$this->currentFile = null;
		} else {
			$this->currentFile = basename($file);
		}

		$this->currentTitle = get_post_field('post_title', $post_id);

		$thumb = wp_get_attachment_image_src($post_id, 'thumbnail', true);
		if (!empty($thumb)) {
			$this->currentThumb = $thumb[0];
			$this->isIcon = (($thumb[1] != 150) && ($thumb[2] != 150));
		} else {
			$this->currentThumb = null;
			$this->isIcon = false;
		}

		$this->save();
	}

	/**
	 * Add any additional \WP_Query post arguments to the query
	 *
	 * @param $args
	 * @param array $args
	 *
	 * @return array
	 */
	protected function filterPostArgs($args) {
		return $args;
	}

	/**
	 * @param array $options
	 * @param array $selectedItems
	 *
	 * @return array|bool
	 */
	public function prepare($options = [], $selectedItems = []) {
		Logger::info("Preparing attachment task", [], __METHOD__, __LINE__);

		if (!empty($options['selected-items'])) {
			$selectedItems = explode(',', $options['selected-items']);
		}

		$this->options = $options;

		if (!empty($selectedItems) && is_array($selectedItems)) {
			foreach($selectedItems as $postId) {
				$this->addItem(['id' => $postId]);
			}
		} else {
			$args = [
				'post_type' => 'attachment',
				'post_status' => 'inherit',
				'posts_per_page' => 100,
				'fields' => 'ids'
			];

			if (!empty($options['sort-order']) && ($options['sort-order'] != 'default')) {
				if (in_array($options['sort-order'], ['date-asc', 'date-desc', 'title-asc', 'title-desc'])) {
					$parts = explode('-', $options['sort-order']);
					$args['orderby'] = $parts[0];
					$args['order'] = strtoupper($parts[1]);
				} else if (in_array($options['sort-order'], ['filename-asc', 'filename-desc'])) {
					$args['meta_key'] = '_wp_attached_file';
					$args['orderby'] = 'meta_value';
					$args['order'] = ($options['sort-order'] == 'filename-asc') ? 'ASC' : 'DESC';
				}
			}



			if (isset($options['limit'])) {
				$args['posts_per_page'] = $options['limit'];
				if (isset($options['offset'])) {
					$args['offset'] = $options['offset'];
				}
			} else {
				$args['nopaging'] = true;
			}

			$args['post_mime_type'] = StorageToolSettings::allowedMimeTypes();

			$args = $this->filterPostArgs($args);

			$query = new \WP_Query($args);
			$postIds = $query->posts;
			if (count($postIds) === 0) {
				return false;
			}

			foreach($postIds as $postId) {
				$this->addItem(['id' => $postId]);
			}
		}

		$this->state = Task::STATE_WAITING;

		Logger::info("Added {$this->totalItems} to the task.", [], __METHOD__, __LINE__);
		return ($this->totalItems > 0);
	}

}
