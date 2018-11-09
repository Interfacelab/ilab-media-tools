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

if(!defined('ABSPATH')) {
	header('Location: /');
	die;
}

function ilab_get_image_sizes($size = null) {
	global $_wp_additional_image_sizes;

	$sizes = [];
	$get_intermediate_image_sizes = get_intermediate_image_sizes();

	// Create the full array with sizes and crop info
	foreach($get_intermediate_image_sizes as $_size) {
		if(in_array($_size, ['thumbnail', 'medium', 'medium_large', 'large'])) {
			$sizes[$_size]['width'] = get_option($_size.'_size_w');
			$sizes[$_size]['height'] = get_option($_size.'_size_h');
			$sizes[$_size]['crop'] = (bool) get_option($_size.'_crop');
		} else if(isset($_wp_additional_image_sizes[$_size])) {
			$sizes[$_size] = [
				'width' => $_wp_additional_image_sizes[$_size]['width'],
				'height' => $_wp_additional_image_sizes[$_size]['height'],
                'crop' => $_wp_additional_image_sizes[$_size]['crop'],
                'imgix' => (!empty($_wp_additional_image_sizes[$_size]['imgix'])) ? $_wp_additional_image_sizes[$_size]['imgix'] : null
			];
		}
	}

	if($size != null) {
		if(isset($sizes[$size])) {
			return $sizes[$size];
		}

		return null;
	}

	return $sizes;
}

function ilab_size_is_cropped($size) {
	if (is_array($size)) {
	    return !empty($size['crop']);
	}

	$sizeInfo = ilab_get_image_sizes($size);
	if (empty($sizeInfo)) {
		return false;
	}

    return !empty($sizeInfo['crop']);
}

function relative_admin_url($path = '', $scheme = 'admin') {
	$url = get_admin_url(null, $path, $scheme);
	$site = home_url('', 'admin');

	return str_replace($site, '', $url);
}
