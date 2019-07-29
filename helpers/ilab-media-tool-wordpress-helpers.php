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

function ilab_file_get_contents($path) {
	if (filter_var($path, FILTER_VALIDATE_URL) && !ini_get('allow_url_fopen')) {
		$response = wp_remote_get($path, ['timeout' => 5]);
		if (is_wp_error($response)) {
			return null;
		}

		return $response['body'];
	}

	return file_get_contents($path);
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

function ilab_find_nearest_size($attachmentId, $width, $height) {
	if (!empty($attachmentId)) {
		$meta = wp_get_attachment_metadata($attachmentId);
		if (isset($meta['sizes'])) {
			foreach($meta['sizes'] as $msize => $mdata) {
				if (strpos($mdata['file'], "{$width}x{$height}.") !== false) {
					return $msize;
				}
			}
		}
	}


	return null;
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

function ilab_admin_url($path = '', $scheme = 'admin') {
	if (is_multisite() && \ILAB\MediaCloud\Utilities\Environment::NetworkMode()) {
		return network_admin_url($path, $scheme);
	} else {
		return admin_url($path, $scheme);
	}
}
