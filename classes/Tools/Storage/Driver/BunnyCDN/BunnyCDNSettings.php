<?php

namespace MediaCloud\Plugin\Tools\Storage\Driver\BunnyCDN;

use MediaCloud\Plugin\Tools\ToolSettings;

/**
 * Class BunnyCDNSettings
 * @package MediaCloud\Plugin\Tools\Storage\Driver\BunnyCDN
 *
 * @property string apiKey
 * @property string storageZone
 * @property string pullZone
 * @property string region
 * @property string tokenAuthKey
 * @property string signedMatches
 * @property bool settingsError
 *
 */
class BunnyCDNSettings extends ToolSettings {
	protected $settingsMap = [
		'apiKey' => ['mcloud-storage-bunnycdn-apikey', 'ILAB_BUNNYCDN_APIKEY', null],
		'storageZone' => ['mcloud-storage-bunnycdn-storage-zone', 'ILAB_BUNNYCDN_STORAGE_ZONE', null],
		'pullZone' => ['mcloud-storage-bunnycdn-pull-zone', 'ILAB_BUNNYCDN_PULL_ZONE', null],
		'region' => ['mcloud-storage-bunnycdn-region',  ['ILAB_BUNNYCDN_REGION'], null],
		'tokenAuthKey' => ['mcloud-storage-bunnycdn-token-auth-key',  ['ILAB_BUNNYCDN_TOKEN_AUTH_KEY'], null],
		'signedMatches' => ['mcloud-storage-bunnycdn-signed-matches',  ['ILAB_BUNNYCDN_SIGNED_MATCHES'], null],
		'settingsError' => ['ilab-bunnycdn-settings-error',  null, false],
	];
}