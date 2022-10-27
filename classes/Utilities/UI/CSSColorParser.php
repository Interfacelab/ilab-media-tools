<?php

namespace MediaCloud\Plugin\Utilities\UI;

/**
 * Parser class used to normalize CSS colors them into different formats.
 *
 * @author Cami Mostajo
 * @package TenQuality\Utility\Colors
 * @license MIT
 * @version 1.0.5
 */
class CSSColorParser
{
	/**
	 * Constant used to define a default alpha value.
	 * OPAQUE value.
	 * @since 1.0.5
	 *
	 * @var string
	 */
	const ALPHA_OPAQUE = 'F';
	/**
	 * Constant used to define a default alpha value.
	 * OPAQUE value.
	 * @since 1.0.5
	 *
	 * @var string
	 */
	const ALPHA_TRANSPARENT = '0';
	/**
	 * Default alpha code.
	 * 'F' will generate 'FF' | 255. Commonly meaning opaque.
	 * '0' will generate '00' | 0. Commonly meaning transparent.
	 * @since 1.0.4
	 * @since 1.0.5 Use constant as default.
	 *
	 * @var string
	 */
	protected static $defaultAlpha = self::ALPHA_OPAQUE;
	/**
	 * Raw color being parsed.
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $raw;
	/**
	 * NOMALIZED Color.
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $color;
	/**
	 * Additional color labels.
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $labels;
	/**
	 * Additional color labels' hex codes.
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $hexCodes;
	/**
	 * Default constructor.
	 * @since 1.0.0
	 *
	 * @param string $color    CSS color to parse.
	 * @param array  $labels   Additional CSS labels to parse in `parseColorLabels()`.
	 * @param array  $hexCodes HEX Codes of the additional CSS labels to parse in `parseColorLabels()`.
	 */
	public function __construct($color, $labels = [], $hexCodes = [])
	{
		$this->raw = $color;
		$this->color = $color;
		$this->labels = $labels;
		$this->hexCodes = $hexCodes;
	}
	/**
	 * Returns color as a normalized HEX code.
	 * STATIC CONSTRUCTOR.
	 * @since 1.0.0
	 *
	 * @param string $color    CSS color to parse.
	 * @param array  $labels   Additional CSS labels to parse in `parseColorLabels()`.
	 * @param array  $hexCodes HEX Codes of the additional CSS labels to parse in `parseColorLabels()`.
	 *
	 * @return string
	 */
	public static function hex($color, $labels = [], $hexCodes = [])
	{
		$parser = new self($color, $labels, $hexCodes);
		return $parser->applyFilters()
			->parseColorLabels()
			->normalizeAbbreviations()
			->toHex();
	}
	/**
	 * Returns color as a normalized HEX code (with transparency).
	 * STATIC CONSTRUCTOR.
	 * @since 1.0.0
	 *
	 * @param string $color    CSS color to parse.
	 * @param array  $labels   Additional CSS labels to parse in `parseColorLabels()`.
	 * @param array  $hexCodes HEX Codes of the additional CSS labels to parse in `parseColorLabels()`.
	 *
	 * @return string
	 */
	public static function hexTransparent($color, $labels = [], $hexCodes = [])
	{
		$parser = new self($color, $labels, $hexCodes);
		return $parser->applyFilters()
			->parseColorLabels()
			->normalizeAbbreviations()
			->toHexTransparent();
	}
	/**
	 * Returns color as a normalized ARGB code.
	 * STATIC CONSTRUCTOR.
	 * @since 1.0.0
	 *
	 * @param string $color    CSS color to parse.
	 * @param array  $labels   Additional CSS labels to parse in `parseColorLabels()`.
	 * @param array  $hexCodes HEX Codes of the additional CSS labels to parse in `parseColorLabels()`.
	 *
	 * @return string
	 */
	public static function argb($color, $labels = [], $hexCodes = [])
	{
		$parser = new self($color, $labels, $hexCodes);
		return $parser->applyFilters()
			->parseColorLabels()
			->normalizeAbbreviations()
			->toArgb();
	}
	/**
	 * Returns color as a normalized RGBA code.
	 * STATIC CONSTRUCTOR.
	 * @since 1.0.1
	 *
	 * @param string $color    CSS color to parse.
	 * @param array  $labels   Additional CSS labels to parse in `parseColorLabels()`.
	 * @param array  $hexCodes HEX Codes of the additional CSS labels to parse in `parseColorLabels()`.
	 *
	 * @return string
	 */
	public static function rgba($color, $labels = [], $hexCodes = [])
	{
		$parser = new self($color, $labels, $hexCodes);
		return $parser->applyFilters()
			->parseColorLabels()
			->normalizeAbbreviations()
			->toRgba();
	}
	/**
	 * Returns color array with RGBA codes.
	 * STATIC CONSTRUCTOR.
	 * @since 1.0.1
	 *
	 * @param string $color    CSS color to parse.
	 * @param array  $labels   Additional CSS labels to parse in `parseColorLabels()`.
	 * @param array  $hexCodes HEX Codes of the additional CSS labels to parse in `parseColorLabels()`.
	 *
	 * @return array
	 */
	public static function array($color, $labels = [], $hexCodes = [])
	{
		$parser = new self($color, $labels, $hexCodes);
		return $parser->applyFilters()
			->parseColorLabels()
			->normalizeAbbreviations()
			->toArray();
	}
	/**
	 * Returns color's RGBA codes as a json array.
	 * STATIC CONSTRUCTOR.
	 * @since 1.0.1
	 *
	 * @param string $color    CSS color to parse.
	 * @param array  $labels   Additional CSS labels to parse in `parseColorLabels()`.
	 * @param array  $hexCodes HEX Codes of the additional CSS labels to parse in `parseColorLabels()`.
	 *
	 * @return string
	 */
	public static function string($color, $labels = [], $hexCodes = [])
	{
		$parser = new self($color, $labels, $hexCodes);
		return (string)$parser->applyFilters()
			->parseColorLabels()
			->normalizeAbbreviations();
	}
	/**
	 * Sets static default alpha code.
	 * @since 1.0.4
	 *
	 * @param string $code Alpha code between F and 0.
	 */
	public static function setAlpha($code = self::ALPHA_OPAQUE)
	{
		static::$defaultAlpha = $code;
	}
	/**
	 * Applies basic filters:
	 * - trim.
	 * - upper case conversion.
	 * - hashtag removal.
	 * @since 1.0.0
	 *
	 * @return object|CssParser
	 */
	public function applyFilters()
	{
		$this->color = str_replace('#', '', strtoupper(trim($this->color)));
		return $this;
	}
	/**
	 * Applies normalization to abbreviations.
	 * i.e. #CCC to #CCCCCC
	 * @since 1.0.0
	 *
	 * @return object|CssParser
	 */
	public function normalizeAbbreviations()
	{
		if (strlen($this->color) === 3) {
			$code = [
				substr($this->color, 0, 1),
				substr($this->color, 1, 1),
				substr($this->color, 2, 1)
			];
			$this->color = $code[0].$code[0].$code[1].$code[1].$code[2].$code[2];
		}
		return $this;
	}
	/**
	 * Parses color labels and converts them into HEX codes.
	 * @since 1.0.0
	 *
	 * @return object|CssParser
	 */
	public function parseColorLabels()
	{
		$this->color = preg_replace(
			array_merge([
				'/WHITE/',
				'/BLACK/',
				'/RED/',
				'/GREEN/',
				'/BLUE/',
				'/YELLOW/',
				'/PURPLE/',
				'/MAGENTA/',
				'/BROWN/',
				'/CYAN/',
				'/GREY/',
				'/LIME/',
				'/CORAL/',
			], $this->labels),
			array_merge([
				'FFFFFF',
				'000000',
				'FF0000',
				'008000',
				'0000FF',
				'FFFF00',
				'800080',
				'FF00FF',
				'A52A2A',
				'00FFFF',
				'808080',
				'00FF00',
				'FF7F50',
			], $this->hexCodes),
			$this->color
		);
		return $this;
	}
	/**
	 * Returns color's hex code.
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function toHex()
	{
		return '#'. substr($this->color, 0, 6);
	}
	/**
	 * Returns color's hex code (with transparency).
	 * @since 1.0.0
	 * @since 1.0.4 Supports default alpha.
	 *
	 * @return string
	 */
	public function toHexTransparent()
	{
		return '#'.str_pad($this->color, 8, static::$defaultAlpha);
	}
	/**
	 * Returns color's ARGB code.
	 * @since 1.0.0
	 * @since 1.0.3 Fixes ARGB format.
	 * @since 1.0.4 Supports default alpha.
	 *
	 * @return string
	 */
	public function toArgb()
	{
		return '0x'.str_pad(substr($this->color, 6, 2).substr($this->color, 0, 6), 8, static::$defaultAlpha, STR_PAD_LEFT);
	}
	/**
	 * Returns color's RGBA code.
	 * @since 1.0.1
	 * @since 1.0.4 Supports default alpha.
	 *
	 * @see http://php.net/manual/en/function.hexdec.php
	 *
	 * @return string
	 */
	public function toRgba()
	{
		return sprintf(
			'rgba(%d,%d,%d,%s)',
			hexdec(substr($this->color, 0, 2)),
			hexdec(substr($this->color, 2, 2)),
			hexdec(substr($this->color, 4, 2)),
			round(hexdec(strlen($this->color) > 6 ? substr($this->color, 6, 2) : static::$defaultAlpha.static::$defaultAlpha) / 255, 2)
		);
	}
	/**
	 * Returns color's RGBA codes as array.
	 * @since 1.0.1
	 * @since 1.0.4 Supports default alpha.
	 *
	 * @see http://php.net/manual/en/function.hexdec.php
	 *
	 * @return array
	 */
	public function toArray()
	{
		return [
			hexdec(substr($this->color, 0, 2)),
			hexdec(substr($this->color, 2, 2)),
			hexdec(substr($this->color, 4, 2)),
			hexdec(strlen($this->color) > 6 ? substr($this->color, 6, 2) : static::$defaultAlpha.static::$defaultAlpha)
		];
	}
	/**
	 * Returns color's RGBA codes as a json string.
	 * @since 1.0.1
	 *
	 * @return string
	 */
	public function __toString()
	{
		$color = $this->toArray();
		return json_encode([
			'red'   => $color[0],
			'green' => $color[1],
			'blue'  => $color[2],
			'alpha' => $color[3]
		]);
	}
}