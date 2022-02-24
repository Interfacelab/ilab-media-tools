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

namespace MediaCloud\Plugin\Utilities;

use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Vendor\Zumba\Amplitude\Amplitude;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

final class Tracker {
	const TRACKED_TOOLS = [
		'storage',
		'imgix',
		'media-upload',
		'vision',
		'assets',
		'video-encoding',
		'optimizer',
		'crop'
	];

	/**
	 * Creates the Analytics logger class
	 *
	 * @return Amplitude
	 */
	private static function createAnalytics() {
		/** @var \Freemius $media_cloud_licensing */
		global $media_cloud_licensing;

		$clid = Environment::Option('mcloud-tracking-id', null, null);
		if (empty($clid)) {
			$clid = gen_uuid(12);
			Environment::UpdateOption('mcloud-tracking-id', $clid);
		}

		$amplitude =Amplitude::getInstance();
		$amplitude->init('ad858aa2384b66a11734161e4331287a', $clid);

		$userProps = [
			'plan' => $media_cloud_licensing->get_plan_name()
		];

		$assetsEnabled = false;
		foreach(ToolsManager::instance()->tools as $toolId => $tool) {
			if (in_array($toolId, self::TRACKED_TOOLS)) {
				$enabled = $tool->enabled();
				$userProps[$toolId] = $enabled;
				if ($toolId == 'assets') {
					$assetsEnabled = $enabled;
				}
			}
		}

		$provider = Environment::Option('mcloud-storage-provider','ILAB_CLOUD_STORAGE_PROVIDER', null);
		if (!empty($provider)) {
			$userProps['storage-provider'] = $provider;
		}

		$visionProvider = Environment::Option('mcloud-vision-provider','ILAB_VISION_PROVIDER', null);
		if (!empty($visionProvider)) {
			$userProps['vision-provider'] = $provider;
		}

		if ($assetsEnabled) {
			$storeCSS = Environment::Option('mcloud-assets-store-css', null, false);
			$storeJS = Environment::Option('mcloud-assets-store-js', null, false);

			if (!empty($storeCSS) && !empty($storeJS)) {
				$mode = 'push';
			} else if (empty($storeCSS) && empty($storeJS)) {
				$mode = 'pull';
			} else {
				$mode = 'mixed';
			}

			$userProps['asset-mode'] = $mode;
		}

		$amplitude->setUserProperties($userProps);

		return $amplitude;
	}

	public static function trackSettings() {
		self::trackView('Update Settings', '/settings/update');
	}

	public static function trackView($title, $page) {
		if (!LicensingManager::OptedIn('mcloud-opt-usage-tracking')) {
			return;
		}

		$amplitude = self::createAnalytics();
		$amplitude->queueEvent($page, [
			'app_version' => MEDIA_CLOUD_VERSION,
			'ip' => '$remote'
		]);
		$amplitude->logQueuedEvents();
	}

}