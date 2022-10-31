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

namespace MediaCloud\Plugin\Tools\Storage\Driver\Supabase;

use MediaCloud\Plugin\Tools\Storage\StorageFile;
use MediaCloud\Vendor\FasterImage\FasterImage;
use MediaCloud\Plugin\Tools\Storage\FileInfo;
use MediaCloud\Plugin\Tools\Storage\StorageException;
use MediaCloud\Plugin\Tools\Storage\StorageInterface;
use MediaCloud\Vendor\GuzzleHttp\Client;
use MediaCloud\Vendor\GuzzleHttp\Handler\CurlHandler;
use MediaCloud\Vendor\GuzzleHttp\HandlerStack;
use MediaCloud\Vendor\GuzzleHttp\Middleware;
use function MediaCloud\Plugin\Utilities\arrayPath;
use MediaCloud\Plugin\Utilities\Logging\ErrorCollector;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use MediaCloud\Plugin\Utilities\NoticeManager;

if(!defined('ABSPATH')) {
	header('Location: /');
	die;
}

class SupabaseStorage implements StorageInterface {

	//region Properties
	/** @var SupabaseStorageSettings|null  */
	protected $settings = null;

	/** @var null */
	protected $client = null;
	//endregion

	//region Constructor
	public function __construct() {
		$this->settings = new SupabaseStorageSettings();
	}
	//endregion

	//region Static Information Methods
	public static function identifier() {
		return 'supabase';
	}

	public static function name() {
		return 'Supabase Storage (Beta)';
	}

	public static function endpoint() {
		return null;
	}

	public static function pathStyleEndpoint() {
		return null;
	}

	public static function defaultRegion() {
		return null;
	}

	public static function forcedRegion() {
		return null;
	}

	public static function bucketLink($bucket) {
		return null;
	}

	public function pathLink($bucket, $key) {
		return null;
	}
	//endregion

	//region Enabled/Options
	public function usesSignedURLs($type = null) {
		return false;
	}

	public function supportsDirectUploads() {
		return false;
	}

	public function supportsWildcardDirectUploads() {
		return false;
	}

	public function supportsBrowser() {
		return true;
	}
	//endregion

	//region API Requests

	/**
	 * @param $method
	 * @param $path
	 * @param $data
	 * @param $contentType
	 *
	 * @return \MediaCloud\Vendor\Psr\Http\Message\ResponseInterface
	 * @throws \MediaCloud\Vendor\GuzzleHttp\Exception\GuzzleException
	 */
	protected function request($method, $path, $data = [], $contentType = 'application/json') {
		$handler = new CurlHandler();
		$stack = HandlerStack::create($handler);

		$c = new Client([
			'base_uri' => $this->settings->storageUrl.'/storage/v1/',
			'verify' => false,
			'handler' => $stack
		]);

		$args = [
			'headers' => [
				'Authorization' => "Bearer {$this->settings->key}",
				'apiKey' => "{$this->settings->key}",
				'Content-Type' => $contentType,
			]
		];

		if ($data) {
			if ($contentType === 'application/json') {
				$args['body'] = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
			} else {
				$args['body'] = $data;
			}
		}

		if (MCLOUD_DEBUGGING) {
			$args['curl'] = [
				CURLOPT_PROXY => 'host.docker.internal',
				CURLOPT_PROXYPORT => 8888,
			];

			if ($contentType === 'application/json') {
				$tapMiddleware = Middleware::tap(function($request) {
					Logger::info("Guzzle Debug:\n".$request->getBody(), [], __METHOD__, __LINE__);
				});
				$args['handler'] = $tapMiddleware($handler);
			}
		}

		return $c->request($method, $path, $args);
	}
	//endregion

	//region Settings related functions

	/**
	 * @param ErrorCollector|null $errorCollector
	 * @return bool|void
	 */
	public function validateSettings($errorCollector = null) {
		delete_option('mcloud-storage-supabase-settings-error');
		$this->settings->settingsError = false;

		$valid = false;

		try {
			if($this->enabled()) {
				$res = $this->request('GET', 'bucket/'.$this->settings->bucket);
				if ($res->getStatusCode() !== 200) {
					Logger::error("Error validating supabase storage settings.  ".$res->get_error_message(), [], __METHOD__, __LINE__);
					$valid = false;
				} else {
					$valid = true;
				}

				if(!$valid) {
					$this->settings->settingsError = true;
					update_option('mcloud-storage-supabase-settings-error', true);
				}
			} else {
				if ($errorCollector) {
					$errorCollector->addError("Supabase settings are missing or incorrect.");
				}
			}
		} catch (\Exception $ex) {
		}

		return $valid;
	}

	public function settings() {
		return $this->settings;
	}

	public function enabled() {
		if(!($this->settings->key && $this->settings->storageUrl && $this->settings->bucket)) {
			if (current_user_can('manage_options')) {
				$adminUrl = admin_url('admin.php?page=media-cloud-settings&tab=storage');
				NoticeManager::instance()->displayAdminNotice('error', "To start using Cloud Storage, you will need to <a href='$adminUrl'>supply your Supabase credentials.</a>.", true, 'ilab-cloud-storage-setup-warning', 'forever');
			}

			return false;
		}

		if($this->settings->settingsError) {
			if (current_user_can('manage_options')) {
				NoticeManager::instance()->displayAdminNotice('error', 'Your Supabase settings are incorrect or the bucket does not exist.  Please verify your settings and update them.');
			}

			return false;
		}

		return true;
	}

	public function settingsError() {
		return $this->settings->settingsError;
	}
	//endregion

	//region File Functions

	public function bucket() {
		return $this->settings->bucket;
	}

	public function region() {
		return null;
	}

	public function isUsingPathStyleEndPoint() {
		return false;
	}

	public function acl($key) {
		return null;
	}

	public function insureACL($key, $acl) {
	}

	public function updateACL($key, $acl) {
	}

	public function canUpdateACL() {
		return false;
	}

	public function exists($key) {
		try {
			$res = $this->request('HEAD', 'object/public/'.trailingslashit($this->settings->bucket).$key);
			return $res->getStatusCode() < 400;
		} catch (\Exception $ex) {
			return false;
		}
	}

	public function copy($sourceKey, $destKey, $acl, $mime = false, $cacheControl = false, $expires = false) {
		try {
			$this->request('POST', 'object/copy', [
				'bucketId' => $this->settings->bucket,
				'sourceKey' => $sourceKey,
				'destKey' => $destKey,
			]);
		} catch (\Exception $ex) {
			Logger::error("Error copying files.  ".$ex->getMessage(), [], __METHOD__, __LINE__);
		}
	}

	public function upload($key, $fileName, $acl, $cacheControl=null, $expires=null, $contentType=null, $contentEncoding=null, $contentLength=null, $tries = 1) {
		try {
			Logger::startTiming("Start Upload", [], __METHOD__, __LINE__);
			if (empty($contentType)) {
				$mime = wp_check_filetype_and_ext($fileName, pathinfo($fileName, PATHINFO_BASENAME));
				$contentType = !empty($mime['type']) ? $mime['type'] : 'application/octet-stream';
			}
			$res = $this->request('POST', 'object/'.trailingslashit($this->settings->bucket).$key, fopen($fileName, 'r'), $contentType);
			Logger::endTiming("End Upload", [], __METHOD__, __LINE__);
		} catch (\Exception $ex) {
			Logger::error("Error uploading file to Supabase.  ".$ex->getMessage(), [], __METHOD__, __LINE__);
		}
	}

	public function delete($key) {
		try {
			$this->request('DELETE', 'object/'.untrailingslashit($this->settings->bucket), [
				'prefixes' => [ltrim($key, '/')],
			]);
		} catch (\Exception $ex) {
			Logger::error("Error deleting file from Supabase.  ".$ex->getMessage(), [], __METHOD__, __LINE__);
		}
	}

	public function createDirectory($key) {
	}

	public function deleteDirectory($key) {
		$files = $this->ls($key, '/', -1, null, true);
		foreach($files['files'] as $file) {
			$this->delete($file);
		}
	}

	public function dir($path = '', $delimiter = '/', $limit = -1, $next = null) {
		try {
			$next = $next ?? intval(0);
			$res = $this->request('POST', 'object/list/'.untrailingslashit($this->settings->bucket), [
				'limit' => $limit,
				'offset' => intval($next),
				'prefix' => ltrim($path, '/'),
			]);

			if ($res->getStatusCode() === 200) {
				$data = json_decode($res->getBody(), true);
				$files = [];
				$rootFileCount = 0;

				foreach($data as $datum) {
					$rootFileCount++;
					if (empty($datum['id'])) {
						if ($datum['name'] === '.emptyFolderPlaceholder') {
							continue;
						}

						$files[] = new StorageFile('DIR', trailingslashit($path).trailingslashit($datum['name']));
					} else {
						if ($datum['name'] === '.emptyFolderPlaceholder') {
							continue;
						}

						$files[] = new StorageFile('FILE', trailingslashit($path).$datum['name'], null, arrayPath($datum, 'created_at', null), arrayPath($datum, 'metadata/size', 0), $this->url(trailingslashit($path).$datum['name']));
					}
				}

				return [
					'next' => $rootFileCount === $limit ? $next + $limit : null,
					'files' => $files,
				];

			}
		} catch (\Exception $ex) {
			Logger::error("Error listing files from Supabase.  ".$ex->getMessage(), [], __METHOD__, __LINE__);
		}

		return [
			'next' => null,
			'files' => [],
		];
	}

	public function ls($path = '', $delimiter = '/', $limit = -1, $next = null, $recursive = false) {
		try {
			$next = $next ?? intval(0);
			$res = $this->request('POST', 'object/list/'.untrailingslashit($this->settings->bucket), [
				'limit' => $limit === -1 ? 1000 : $limit,
				'offset' => $next,
				'next' => $next,
				'prefix' => ltrim($path, '/'),
			]);

			if ($res->getStatusCode() === 200) {
				$data = json_decode($res->getBody(), true);
				$files = [];
				$rootFileCount = 0;

				foreach($data as $datum) {
					$rootFileCount++;
					if (empty($datum['id'])) {
						if ($datum['name'] === '.emptyFolderPlaceholder') {
							continue;
						}

//						$files[] = trailingslashit($path).trailingslashit($datum['name']);
						if ($recursive) {
							$ls = $this->ls(trailingslashit($path).trailingslashit($datum['name']), $delimiter, $limit, $next, $recursive);
							$files = array_merge($files, $ls['files']);
							if ($limit === -1) {
								while(!empty($ls['next'])) {
									$ls = $this->ls(trailingslashit($path).trailingslashit($datum['name']), $delimiter, $limit, $ls['next'], $recursive);
									$files = array_merge($files, $ls['files']);
								}
							}
						}
					} else {
						if ($datum['name'] === '.emptyFolderPlaceholder') {
							continue;
						}

						$files[] = trailingslashit($path).$datum['name'];
					}
				}

				return [
					'next' => $rootFileCount === $limit ? $next + $limit : null,
					'files' => $files,
				];

			}
		} catch (\Exception $ex) {
			Logger::error("Error listing files from Supabase.  ".$ex->getMessage(), [], __METHOD__, __LINE__);
		}

		return [
			'next' => null,
			'files' => [],
		];
	}

	public function info($key) {
		$res = $this->request('HEAD', 'object/public/'.trailingslashit($this->settings->bucket).$key);
		if ($res->getStatusCode() !== 200) {
			throw new StorageException("Unable to retrieve file info for $key", 400);
		}

		$length = arrayPath($res, 'headers/Content-Length', 0);
		$type = arrayPath($res, 'headers/Content-Type', 'application/octet-stream');
		$url = $this->url($key);
		$size = null;
		if(strpos($type, 'image/') === 0) {
			$faster = new FasterImage();
			$result = $faster->batch([$url]);
			$result = $result[$url];
			$size = $result['size'];
		}

		$fileInfo = new FileInfo($key, $url, $url, $length, $type, $size);
		return $fileInfo;
	}
	//endregion

	//region URLs
	public function presignedUrl($key, $expiration = 0, $options = []) {
		return $this->url($key);
	}

	public function url($key, $type = null) {
		return trailingslashit($this->settings->storageUrl).'storage/v1/object/public/'.trailingslashit($this->settings->bucket).$key;
	}

	public function signedURLExpirationForType($type = null) {
		return null;
	}
	//endregion

	//region Direct Uploads
	public function uploadUrl($key, $acl, $mimeType = null, $cacheControl = null, $expires = null) {
	}

	public function enqueueUploaderScripts() {
	}
	//endregion


	//region Optimization
	public function prepareOptimizationInfo() {
		return [
		];
	}
	//endregion
	public function client() {
		return $this;
	}
}
