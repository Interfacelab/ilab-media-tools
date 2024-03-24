<?php

namespace MediaCloud\Plugin\Tools\Storage\Driver\BunnyCDN;

use MediaCloud\Plugin\Tools\Storage\FileInfo;
use MediaCloud\Plugin\Tools\Storage\StorageException;
use MediaCloud\Plugin\Tools\Storage\StorageFile;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use MediaCloud\Vendor\GuzzleHttp\Client;
use MediaCloud\Vendor\GuzzleHttp\RequestOptions;

class BunnyCDNClient {
	protected $apiKey;
	protected $storageZone;
	protected $region;
	protected $pullZone;

	protected $client;

	public function __construct($apiKey, $storageZone, $region, $pullZone) {
		if (!function_exists('ftp_connect')) {
			throw new StorageException("FTP Extension is required.");
		}

		$this->apiKey = $apiKey;
		$this->storageZone = $storageZone;
		$this->region = empty($region) ? '' : "{$region}.";
		$this->pullZone = $pullZone;

		$this->client = new Client();
	}

	public function upload($sourceFile, $destPath) {
		$res = fopen($sourceFile, 'r');

		$res = $this->client->put("https://{$this->region}storage.bunnycdn.com/{$this->storageZone}/{$destPath}", [
			RequestOptions::HEADERS => [
				'Content-Type' => 'application/octet-stream',
				'AccessKey' => $this->apiKey
			],
			RequestOptions::BODY => $res
		]);

		$status = $res->getStatusCode();
		return $status >= 200 && $status <= 299;
	}

	public function mkdir($path) {
		if (!function_exists('ftp_connect')) {
			return false;
		}

		$ftpId = ftp_connect("{$this->region}storage.bunnycdn.com");
		$login = ftp_login($ftpId, $this->storageZone, $this->apiKey);
		if (!$login) {
			throw new StorageException("Invalid settings");
		}

		ftp_pasv($ftpId, true);
		$result = ftp_mkdir($ftpId, $path);
		ftp_close($ftpId);

		return $result;
	}

	public function listFiles($path) {
		$res = $this->client->get("https://{$this->region}storage.bunnycdn.com/{$this->storageZone}/{$path}/", [
			RequestOptions::HEADERS => [
				'AccessKey' => $this->apiKey
			],
		]);

		$status = $res->getStatusCode();
		if ($status >= 200 && $status <= 299) {
			$listText = $res->getBody();
			$list = json_decode($listText, true);

			$files = [];
			foreach($list as $file) {
				if ($file['IsDirectory']) {
					$key = ltrim(str_replace("/{$this->storageZone}/", "", $file['Path']) .'/'.$file['ObjectName'], '/');
					$files[] = new StorageFile('DIR', $key);
				} else {
					$key = ltrim(str_replace("/{$this->storageZone}/", "", $file['Path']) .'/'.$file['ObjectName'], '/');
					$files[] = new StorageFile('FILE', $key, null, $file['LastChanged'], $file['Length'], $this->pullZone.'/'.$key);
				}

			}

			return $files;
		}

		return false;
	}

	public function deleteFile($file) {

		$res = $this->client->delete("https://{$this->region}storage.bunnycdn.com/{$this->storageZone}/{$file}", [
			RequestOptions::HEADERS => [
				'AccessKey' => $this->apiKey
			],
		]);

		$status = $res->getStatusCode();
		return $status >= 200 && $status <= 299;
	}

	public function exists($file) {
		if (!function_exists('ftp_connect')) {
			return false;
		}

		$ftpId = ftp_connect("{$this->region}storage.bunnycdn.com");
		$login = ftp_login($ftpId, $this->storageZone, $this->apiKey);
		if (!$login) {
			throw new StorageException("Invalid settings");
		}

		ftp_pasv($ftpId, true);
		$result = ftp_size($ftpId, $file) >= 0;
		ftp_close($ftpId);

		return $result;
	}

	public function info($file) {
		$url = "{$this->pullZone}/{$file}";
		$res = $this->client->head($url);

		$status = $res->getStatusCode();
		if ($status >= 200 && $status <= 299) {
			$headers = $res->getHeaders();
			$size = !empty($headers['Content-Length']) ? $headers['Content-Length'][0] : 0;
			$type = !empty($headers['Content-Type']) ? $headers['Content-Type'][0] : null;
			return new FileInfo($file, $url, null, $size, $type, $size);
		}

		throw new StorageException("File does not exist.");
	}

	public function signUrl($token, $hostname, $path, $expirationTime, $isDirectory=false) {
		$path = '/' . ltrim($path, '/');
		$url = sprintf('%s%s', $hostname, $path);

		$urlScheme = parse_url($url, PHP_URL_SCHEME);
		$urlHost = parse_url($url, PHP_URL_HOST);
		$urlPath = parse_url($url, PHP_URL_PATH);
		$urlQuery = parse_url($url, PHP_URL_QUERY) ?? '';

		$parameters = [];
		parse_str($urlQuery, $parameters);

		$signaturePath = $urlPath;
		if ($isDirectory) {
			$parameters['token_path'] = $signaturePath;
		}

		ksort($parameters);
		$parameterData = '';
		$parameterDataUrl = '';
		if (sizeof($parameters) > 0) {
			foreach ($parameters as $key => $value) {
				if (strlen($parameterData) > 0) {
					$parameterData .= '&';
				}

				$parameterDataUrl .= '&';
				$parameterData .= sprintf('%s=%s', $key, $value);
				$parameterDataUrl .= sprintf('%s=%s', $key, urlencode($value));
			}
		}

		$expires = time() + $expirationTime;
		$hashableBase = sprintf('%s%s%s', $token, $signaturePath, $expires);
		$hashableBase .= $parameterData;

		// Generate the token
		$token = hash('sha256', $hashableBase, true);
		$token = base64_encode($token);
		$token = strtr($token, '+/', '-_');
		$token = str_replace('=', '', $token);

		if (true === $isDirectory) {
			return sprintf('%s://%s/bcdn_token=%s&expires=%d%s%s', $urlScheme, $urlHost, $token, $expires, $parameterDataUrl, $urlPath);
		}

		return sprintf('%s://%s%s?token=%s%s&expires=%d', $urlScheme, $urlHost, $urlPath, $token, $parameterDataUrl, $expires);
	}

}