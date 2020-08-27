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

namespace MediaCloud\Plugin\Tools\Storage;

use function MediaCloud\Plugin\Utilities\arrayPath;
use MediaCloud\Plugin\Utilities\Environment;
use MediaCloud\Plugin\Utilities\NoticeManager;

if (!defined('ABSPATH')) { header('Location: /'); die; }

final class StorageToolMigrations {

	//region Migration

	public static function migrateFromOtherPlugin() {
		$beenHereBefore = get_option('mcloud-other-plugins-migrated');
		if (!empty($beenHereBefore)) {
			return;
		}

		$migratedFrom = null;
		$migrated = false;
		$statelessVersion = get_option('wp-stateless-current-version');
		if (!empty($statelessVersion)) {
			$migrated = static::migrateStatelessSettings();
			$migratedFrom = 'WP-Stateless';
		} else {
			$migrated = static::migrateOffloadSettings();
			$migratedFrom = 'WP Offload Media';
		}

		if (!empty($migrated)) {
			Environment::UpdateOption('mcloud-tool-enabled-storage', true);
			NoticeManager::instance()->displayAdminNotice('info', "Media Cloud noticed you were using {$migratedFrom} and has migrated your settings automatically.  Everything should be working as before, but make sure to double check your Cloud Storage settings.", true, 'mcloud-migrated-other-plugin', 'forever');
			update_option('mcloud-other-plugins-did-migrate', $migratedFrom);
		}

		update_option('mcloud-other-plugins-migrated', true);
	}

	//endregion

	//region Stateless Migrations

	private static function migrateStatelessSettings() {
		$jsonConfigData = get_option('sm_key_json');
		if (empty($jsonConfigData)) {
			return false;
		}

		$bucket = get_option('sm_bucket');
		if (empty($bucket)) {
			return false;
		}

		$mode = get_option('sm_mode', 'cdn');
		if ($mode === 'disabled') {
			return false;
		}

		Environment::UpdateOption('mcloud-storage-provider', 'google');
		Environment::UpdateOption('mcloud-storage-google-credentials', $jsonConfigData);
		Environment::UpdateOption('mcloud-storage-google-bucket', $bucket);

		$deleteUploads = ($mode === 'stateless');
		if (!empty($deleteUploads)) {
			Environment::UpdateOption("mcloud-storage-delete-uploads", true);
		}

		$cdn = get_option('sm_custom_domain');
		if (!empty($cdn)) {
			Environment::UpdateOption("mcloud-storage-cdn-base", $cdn);
		}

		$uploadDir = trim(get_option('sm_root_dir'), '/');
		if (!empty($uploadDir)) {
			Environment::UpdateOption("mcloud-storage-prefix", $uploadDir);
		}

		$cacheControl = get_option('sm_cache_control');
		if (!empty($cacheControl)) {
			Environment::UpdateOption("mcloud-storage-cache-control", $cacheControl);
		}

		return true;
	}

	//endregion

	//region Offload Migrations

	private static function migrateOffloadMiscSettings($offloadConfig) {
		$deleteUploads = arrayPath($offloadConfig, 'remove-local-file', false);
		$cdn = arrayPath($offloadConfig, 'cloudfront', null);
		$prefix = arrayPath($offloadConfig, 'object-prefix', null);
		$usePrefix = arrayPath($offloadConfig, 'enable-object-prefix', false);

		if (!empty($deleteUploads)) {
			Environment::UpdateOption("mcloud-storage-delete-uploads", true);
		}

		if (!empty($cdn)) {
			Environment::UpdateOption("mcloud-storage-cdn-base", 'https://'.$cdn);
		}

		if (!empty($prefix) && !empty($usePrefix)) {
			$prefix = rtrim($prefix, '/');

			$useYearMonth = arrayPath($offloadConfig, 'use-yearmonth-folders', false);
			if (!empty($useYearMonth)) {
				$prefix = trailingslashit($prefix).'@{date:Y/m}';
			}

			$useVersioning = arrayPath($offloadConfig, 'object-versioning', false);
			if ($useVersioning) {
				$prefix = trailingslashit($prefix).'@{versioning}';
			}

			Environment::UpdateOption("mcloud-storage-prefix", $prefix);
		}
	}

	private static function migrateOffload($provider, $key, $secret) {
		$offloadConfig = get_option('tantan_wordpress_s3');
		$bucket = arrayPath($offloadConfig, 'bucket', null);
		$region = arrayPath($offloadConfig, 'region', 'auto');
		if (empty($bucket)) {
			return false;
		}

		Environment::UpdateOption('mcloud-storage-provider', $provider);
		Environment::UpdateOption("mcloud-storage-s3-access-key", $key);
		Environment::UpdateOption("mcloud-storage-s3-secret", $secret);
		Environment::UpdateOption("mcloud-storage-s3-bucket", $bucket);

		if ($provider === 'do') {
			Environment::UpdateOption("mcloud-storage-s3-endpoint", "https://{$region}.digitaloceanspaces.com");
		} else {
			Environment::UpdateOption("mcloud-storage-s3-region", $region);
		}

		static::migrateOffloadMiscSettings($offloadConfig);

		return true;
	}

	private static function migrateOffloadFromConfig($data) {
		$provider = arrayPath($data, 'provider', null);
		if (empty($provider) || !in_array($provider, ['aws', 'do', 'gcp'])) {
			return false;
		}

		$providerMap = ['aws' => 's3', 'do' => 'do', 'gcp' => 'google'];
		$provider = $providerMap[$provider];

		if ($provider !== 'google') {
			$key = arrayPath($data, 'access-key-id');
			$secret = arrayPath($data, 'secret-access-key');

			return static::migrateOffload($provider, $key, $secret);
		}

		$keyPath = arrayPath($data, 'key-file-path', null);
		if (!empty($keyPath)  && file_exists($keyPath)) {
			$googleConfig = file_get_contents($keyPath);
		} else {
			$googleConfigData = arrayPath($data, 'key-file', null);
			if (empty($googleConfigData) || !is_array($googleConfigData)) {
				return false;
			}

			$googleConfig = json_encode($googleConfigData, JSON_PRETTY_PRINT);
		}

		if (empty($googleConfig)) {
			return false;
		}

		$offloadConfig = get_option('tantan_wordpress_s3');
		$bucket = arrayPath($offloadConfig, 'bucket', null);
		if (empty($bucket)) {
			return false;
		}

		Environment::UpdateOption('mcloud-storage-provider', 'google');
		Environment::UpdateOption('mcloud-storage-google-credentials', $googleConfig);
		Environment::UpdateOption('mcloud-storage-google-bucket', $bucket);

		static::migrateOffloadMiscSettings($offloadConfig);

		return true;
	}

	public static function migrateOffloadSettings() {
		$migrated = false;
		if (defined('AS3CF_SETTINGS')) {
			$data = unserialize(constant('AS3CF_SETTINGS'));
			if (!empty($data)) {
				$migrated =  static::migrateOffloadFromConfig($data);
			}
		} else {
			$offloadConfig = get_option('tantan_wordpress_s3');
			if (!empty($offloadConfig)) {
				$migrated =  static::migrateOffloadFromConfig($offloadConfig);
			}
		}

		return $migrated;
	}
	//endregion
}
