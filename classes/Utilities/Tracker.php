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

namespace ILAB\MediaCloud\Utilities;

use ILAB\MediaCloud\Tools\ToolsManager;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

final class Tracker {
	private const CLOUD_STORAGE_ENABLED = 1;
	private const IMGIX_ENABLED = 2;
	private const DYNAMIC_IMAGES_ENABLED = 3;
	private const DIRECT_UPLOADS_ENABLED = 4;
	private const VISION_ENABLED = 5;
	private const ASSETS_ENABLED = 6;
	private const CROP_ENABLED = 7;
	private const STORAGE_PROVIDER = 8;
	private const VISION_PROVIDER = 9;
	private const ASSETS_MODE = 10;

	private const TOOLS_MAP = [
		'storage' => self::CLOUD_STORAGE_ENABLED,
		'imgix' => self::IMGIX_ENABLED,
		'glide' => self::DYNAMIC_IMAGES_ENABLED,
		'media-upload' => self::DIRECT_UPLOADS_ENABLED,
		'vision' => self::VISION_ENABLED,
		'assets' => self::ASSETS_ENABLED,
		'crop' => self::CROP_ENABLED,
	];


	/**
	 * Creates the Analytics logger class
	 *
	 * @return Analytics
	 */
	private static function createAnalytics() {
		$analytics = new Analytics(true);
		$analytics
			->setProtocolVersion(1)
			->setTrackingId('UA-140883146-5')
			->setDocumentHostName('plugin.mediacloud.press');

		$clid = Environment::Option('mcloud-tracking-id', null, null);
		if (empty($clid)) {
			$clid = gen_uuid(12);
			Environment::UpdateOption('mcloud-tracking-id', $clid);
		}

		$analytics->setUserId($clid);

		return $analytics;
	}

	public static function trackSettings() {
		if (!LicensingManager::OptedIn('mcloud-opt-usage-tracking')) {
			return;
		}

		$analytics = self::createAnalytics();

		$assetsEnabled = false;
		foreach(ToolsManager::instance()->tools as $toolId => $tool) {
			if (isset(self::TOOLS_MAP[$toolId])) {
				$index = self::TOOLS_MAP[$toolId];
				$enabled = $tool->enabled();

				if ($toolId == 'assets') {
					$assetsEnabled = $enabled;
				}

				$analytics->setCustomDimension($enabled, $index);
			}
		}

		$provider = Environment::Option('mcloud-storage-provider','ILAB_CLOUD_STORAGE_PROVIDER', null);
		if (!empty($provider)) {
			$analytics->setCustomDimension($provider, self::STORAGE_PROVIDER);
		}

		$visionProvider = Environment::Option('mcloud-vision-provider','ILAB_VISION_PROVIDER', null);
		if (!empty($visionProvider)) {
			$analytics->setCustomDimension($visionProvider, self::VISION_PROVIDER);
		}

		if ($assetsEnabled) {
			$storeCSS = Environment::Option('mcloud-assets-store-css', null, false);
			$storeJS = Environment::Option('mcloud-assets-store-js', null, false);

			if (!empty($storeCSS) && !empty($storeJS)) {
				$mode = 'Push';
			} else if (empty($storeCSS) && empty($storeJS)) {
				$mode = 'Pull';
			} else {
				$mode = 'Mixed';
			}

			$analytics->setCustomDimension($mode, self::ASSETS_MODE);
		}

		$analytics->setDocumentPath('/settings/update');
		$analytics->setDocumentTitle('Update Settings');
		$analytics->sendPageview();
	}

	public static function trackView($title, $page) {
		if (!LicensingManager::OptedIn('mcloud-opt-usage-tracking')) {
			return;
		}

		$analytics = self::createAnalytics();

		$analytics->setDocumentPath($page);
		$analytics->setDocumentTitle($title);
		$analytics->sendPageview();
	}

}