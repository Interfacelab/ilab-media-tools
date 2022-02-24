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

namespace MediaCloud\Plugin\Tools\Vision;

if (!defined('ABSPATH')) { header('Location: /'); die; }

abstract class VisionDriver {
    /** @var null|VisionToolSettings  */
    protected $settings = null;

    /** @var bool  */
    protected $forceTermCount = false;

    public function __construct() {
        $this->settings = new VisionToolSettings();
    }

	/**
	 * Insures that all the configuration settings are valid and that the vision api is enabled.
	 * @return bool
	 */
	abstract public function enabled();

	/**
	 * Insures that a minimum set of options are enabled to use Vision
	 * @return bool
	 */
	abstract public function minimumOptionsEnabled();

    /**
     * If the driver isn't enabled, this returns the error message as to why
     * @return string|null
     */
    abstract public function enabledError();

    /**
     * Processes the image through the driver's vision API
     *
     * @param $postID
     * @param $meta
     *
     * @return array
     */
    abstract public function processImage($postID, $meta);

    /**
     * Process the tags found with the vision api
     *
     * @param array $tags
     * @param string $tax
     * @param int $postID
     * @param array $allTags
     */
    protected function processTags($tags, $tax, $postID, &$tagsList) {
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
                if (!is_wp_error($tagInfo)) {
                    $term = get_term_by('id', $tagInfo['term_id'], $tax);
                }
            }

            if ($term) {
            	if (!in_array($term->name, $tagsList)) {
		            $tagsList[] = $term->name;
	            }

	            $tagsToAdd[] = $term->term_id;
            }
        }

        if (!empty($tagsToAdd)) {
        	if ($this->settings->forceTermCount) {
		        global $wp_taxonomies;
		        if (isset($wp_taxonomies[$tax])) {
			        $wp_taxonomies[$tax]->update_count_callback = '_update_generic_term_count';
		        }
	        }

            wp_set_post_terms($postID, $tagsToAdd, $tax, true);
        }

        if (!empty($tagsList)) {
	        $tagsTextList = implode(', ', $tagsList);
        	$tagsText = rtrim($this->settings->saveTagsPrefix).' '.$tagsTextList;
	        if (!empty($this->settings->saveTagsToAlt)) {
		        update_post_meta($postID, '_wp_attachment_image_alt', $tagsText);
	        }

	        if (!empty($this->settings->saveTagsToCaption) || !empty($this->settings->saveTagsToDescription)) {
	        	$postData = ['ID' => $postID];

	        	if ($this->settings->saveTagsToCaption) {
	        		$postData['post_excerpt'] = $tagsText;
		        }

		        if ($this->settings->saveTagsToDescription) {
			        $postData['post_content'] = $tagsText;
		        }

		        wp_update_post($postData);
	        }
        }
    }
}
