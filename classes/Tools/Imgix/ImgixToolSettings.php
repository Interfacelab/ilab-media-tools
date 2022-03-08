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

namespace MediaCloud\Plugin\Tools\Imgix;

use MediaCloud\Plugin\Tools\DynamicImages\DynamicImagesToolSettings;
use MediaCloud\Plugin\Utilities\Environment;

/**
 * Class ImgixToolSettings
 * @package MediaCloud\Plugin\Tools\Imgix
 *
 * @property string[] imgixDomains
 * @property bool autoFormat
 * @property bool autoCompress
 * @property bool enableGifs
 * @property bool skipGifs
 * @property string[] noGifSizes
 * @property bool useHTTPS
 * @property bool enabledAlternativeFormats
 * @property bool renderPDF
 * @property bool renderSVG
 * @property bool detectFaces
 * @property bool generateThumbnails
 * @property bool removeQueryVars
 * @property ?string cropMode
 * @property ?string cropPosition
 * @property bool doNotUrlEncode
 */
class ImgixToolSettings extends DynamicImagesToolSettings {
	private $_imgixDomains = null;
	private $_noGifSizes = null;

	protected $imgixSettingsMap = [
		'autoFormat' => ['mcloud-imgix-auto-format', null, false],
		'signingKey' => ['mcloud-imgix-signing-key', null, null],
		'autoCompress' => ['mcloud-imgix-auto-compress', null, false],
		'enableGifs' => ['mcloud-imgix-enable-gifs', null, false],
		'skipGifs' => ['mcloud-imgix-skip-gifs', null, false],
		'useHTTPS' => ['mcloud-imgix-use-https', null, true],
		'enabledAlternativeFormats' => ['mcloud-imgix-enable-alt-formats', null, false],
		'renderPDF' => ['mcloud-imgix-render-pdf-files', null, false],
		'detectFaces' => ['mcloud-imgix-detect-faces', null, false],
		'generateThumbnails' => ['mcloud-imgix-generate-thumbnails', null, true],
		'imageQuality' => ['mcloud-imgix-default-quality', null, null],
		'renderSVG' => ['mcloud-imgix-render-svg-files', null, false],
		'removeQueryVars' => ['mcloud-imgix-remove-extra-variables', null, false],
		'cropMode' => ['mcloud-imgix-crop-mode', null, null],
		'cropPosition' => ['mcloud-imgix-crop-position', null, 'center'],
		'doNotUrlEncode' => ['mcloud-imgix-do-not-urlencode', null, false],
	];

	public function __construct() {
		$this->settingsMap = array_merge($this->settingsMap, $this->imgixSettingsMap);
	}

	public function __get($name) {
		if ($name === 'imgixDomains') {
			if ($this->_imgixDomains === null) {
				$this->_imgixDomains = [];
				$domains = Environment::Option('mcloud-imgix-domains', null, '');
				if (!empty($domains)) {
					$domain_lines = explode("\n", $domains);

					if(count($domain_lines) <= 1) {
						$domain_lines = explode(',', $domains);
					}

					foreach($domain_lines as $d) {
						if(!empty($d)) {
							$this->_imgixDomains[] = trim($d);
						}
					}
				}
			}

			return $this->_imgixDomains;
		}

		if ($name === 'noGifSizes') {
			if ($this->_noGifSizes === null) {
				$this->_noGifSizes = [];
				$noGifSizes = Environment::Option('mcloud-imgix-no-gif-sizes', null, '');
				$noGifSizesArray = explode("\n", $noGifSizes);
				if(count($noGifSizesArray) <= 1) {
					$noGifSizesArray = explode(',', $noGifSizes);
				}

				foreach($noGifSizesArray as $gs) {
					if(!empty($gs)) {
						$this->_noGifSizes[] = trim($gs);
					}
				}
			}

			return $this->_noGifSizes;
		}

		return parent::__get($name);
	}

	public function __set($name, $value) {
		if ($name === 'imgixDomains') {
			if (empty($value)) {
				Environment::UpdateOption('mcloud-imgix-domains', '');
			} else if (is_array($value)) {
				Environment::UpdateOption('mcloud-imgix-domains', implode("\n", $value));
			} else {
				Environment::UpdateOption('mcloud-imgix-domains', $value);
			}
		} else if ($name === 'noGifSizes') {
			if (empty($value)) {
				Environment::UpdateOption('mcloud-imgix-no-gif-sizes', '');
			} else if (is_array($value)) {
				Environment::UpdateOption('mcloud-imgix-no-gif-sizes', implode("\n", $value));
			} else {
				Environment::UpdateOption('mcloud-imgix-no-gif-sizes', $value);
			}
		} else {
			parent::__set($name, $value);
		}
	}

	public function __isset($name) {
		if (in_array($name, ['imgixDomains', 'noGifSizes', 'keepThumbnails', 'doNotUrlEncode'])) {
			return true;
		}

		return parent::__isset($name);
	}

}
