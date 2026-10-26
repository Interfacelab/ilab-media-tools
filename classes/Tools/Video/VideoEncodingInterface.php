<?php

namespace MediaCloud\Plugin\Tools\Video;

interface VideoEncodingInterface {
	/**
	 * Validates settings
	 *
	 * @return bool
	 */
	function validateSettings();

	/**
	 * Signifies settings changed
	 *
	 * @return void
	 */
	function settingsChanged();

	/**
	 * Determines if this driver is enabled
	 *
	 * @return bool
	 */
	function enabled();

	/**
	 * Performs setup
	 */
	function setup();

	/**
	 * Imports an attachment to be encoded
	 *
	 * @param int $attachmentId
	 * @param array $meta
	 *
	 * @return bool
	 */
	function importUrl($attachmentId, $meta);

	/**
	 * Adds a subtitle track
	 *
	 * @param int $attachmentId
	 * @param string $language_code
	 * @param string $name
	 * @param bool $cc
	 * @param string $url
	 *
	 * @return array{ error: bool, status:int, message:string }
	 */
	function addSubtitles($attachmentId, $language_code, $name, $cc, $url, $subtitleContents, $subtitlesFile);

	/**
	 * Deletes a subtitle track
	 *
	 * @param $attachmentId
	 * @param $language_code
	 *
	 * @return array{ error: bool, status:int, message:string|null }
	 */
	function deleteSubtitles($attachmentId, $language_code);

	/**
	 * Registers the info panel on the attachment details page.
	 */
	function registerMetabox();

	/**
	 * Integrates into WP-Rest
	 */
	function integrateREST();

	/**
	 * Resets existing watermark
	 *
	 * @returns bool
	 */
	function resetWatermark();
}