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

namespace ILAB\MediaCloud\Tools\Mux;

use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use ILAB\MediaCloud\Utilities\Environment;
use ILAB\MediaCloud\Utilities\Logging\Logger;
use MuxPhp\Api\AssetsApi;
use MuxPhp\Api\URLSigningKeysApi;
use MuxPhp\ApiException;
use MuxPhp\Configuration;

class MuxAPI {
	/** @var AssetsApi|null */
	private $assetAPI = null;

	/** @var URLSigningKeysApi|null */
	private $keysApi = null;

	/** @var Configuration|null  */
	private $config = null;

	/** @var MuxToolSettings */
	private $settings;

	public function __construct() {
		$this->settings = MuxToolSettings::instance();

		if (empty($this->settings->tokenID) || empty($this->settings->tokenSecret)) {
			return;
		}

		$this->config = Configuration::getDefaultConfiguration();
		$this->config->setUsername($this->settings->tokenID);
		$this->config->setPassword($this->settings->tokenSecret);
	}

	/** @var MuxAPI */
	protected static $instance = null;

	/**
	 * Returns the singleton instance
	 * @return self
	 */
	public static function instance() {
		if (static::$instance === null) {
			static::$instance = new MuxAPI();
		}

		return static::$instance;
	}

	/**
	 * Returns the Mux asset API instance
	 * @return AssetsApi|null
	 */
	public static function assetAPI() {
		if (static::instance()->config === null) {
			return null;
		}

		if (static::instance()->assetAPI === null) {
			static::instance()->assetAPI = new AssetsApi(new Client(), static::instance()->config);
		}

		return static::instance()->assetAPI;
	}

	/**
	 * Returns the Mux key signing API instance
	 * @return URLSigningKeysApi|null
	 */
	public static function keysAPI() {
		if (static::instance()->config === null) {
			return null;
		}

		if (static::instance()->keysApi === null) {
			static::instance()->keysApi = new URLSigningKeysApi(new Client(), static::instance()->config);
		}

		return static::instance()->keysApi;
	}

	public static function validateSignature($muxSignature, $body, $secret, $tolerance = 300) {
		$parts = explode(',', $muxSignature);
		if (count($parts) !== 2) {
			return false;
		}

		$time = null;
		$signature = null;
		foreach($parts as $part) {
			if (strpos($part, 't=') === 0) {
				$time = substr($part, 2);
			} else if (strpos($part, 'v1=') === 0) {
				$signature = substr($part, 3);
			}
		}

		if (empty($time) || empty($signature) || empty($body)) {
			Logger::info("Mux: Missing time and/or signature.", [], __METHOD__, __LINE__);
			return false;
		}

		$expected = hash_hmac('sha256', "{$time}.{$body}", $secret);

		if ($expected !== $signature) {
			Logger::info("Mux: Signature mismatch.", [], __METHOD__, __LINE__);
			return false;
		}

		$offset = time() - $time;
		if ($offset > $tolerance) {
			Logger::info("Mux: Signature time tolerance exceeded.", [], __METHOD__, __LINE__);
			return false;
		}

		return true;
	}

	/**
	 * @return null|array [
	 *      'keyId' => string,
	 *      'privateKey' => string
	 * ]
	 */
	public static function signingKey() {
		if (static::keysAPI() === null) {
			Logger::info("Mux: Could not create keys API", [], __METHOD__, __LINE__);
			return null;
		}

		$signingKey = Environment::Option('mcloud-mux-signing-key');
		if (!empty($signingKey)) {
			if ($signingKey['expires'] >= time()) {
				return [
					'keyId' => $signingKey['keyId'],
					'privateKey' => $signingKey['privateKey']
				];
			} else {
				Logger::info('Mux: Key expired, creating a new one.', [], __METHOD__, __LINE__);
			}
		}

		try {
			$result = static::keysAPI()->createUrlSigningKey();
			$muxKey = $result->getData();
			if ($muxKey === null) {
				Logger::info("Mux: Error creating new signing key.", [], __METHOD__, __LINE__);
				return null;
			}

			$signingKey = [
				'keyId' => $muxKey->getId(),
				'privateKey' => $muxKey->getPrivateKey(),
				'expires' => time() + (static::instance()->settings->secureKeyRotation * 60 * 60)
			];

			Environment::UpdateOption('mcloud-mux-signing-key', $signingKey);
			return [
				'keyId' => $signingKey['keyId'],
				'privateKey' => $signingKey['privateKey']
			];
		} catch (ApiException $ex) {
			Logger::info("Mux: Error creating signing key: ".$ex->getMessage(), [], __METHOD__, __LINE__);
		}

		return null;
	}

	/**
	 * @param $options
	 * @return string|null
	 *
	 * @throws \MuxPhp\ApiException
	 */
	public static function jwt($options) {
		$signingKey = static::signingKey();
		if ($signingKey === null) {
			return null;
		}

		$options['kid'] = $signingKey['keyId'];
		$privateKey = base64_decode($signingKey['privateKey']);

		return JWT::encode($options, $privateKey, 'RS256');
	}
}