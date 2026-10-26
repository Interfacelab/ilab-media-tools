<?php

namespace MediaCloud\Plugin\Tools\VideoPlayer;

interface VideoPlayerInterface {
	/**
	 * Enqueues any scripts or styles needed
	 *
	 * @param bool $admin
	 *
	 * @return void
	 */
	public function enqueueScripts($admin);

	/**
	 * Renders video player using supplied data
	 *
	 * @param int|null $attachmentId
	 * @param VideoPlayerData $playerData
	 * @param array $videoAttributes
	 * @param array{ allow: string, original: string, } $downloads
	 * @param string|null $posterUrl
	 * @param string|null $videoId
	 * @param string[] $videoClasses
	 * @param string[] $videoStyles
	 * @param string|null $wrapperEle
	 * @param string[] $wrapperClasses
	 * @param string[] $wrapperStyles
	 *
	 * @return string|null
	 */
	public function renderPlayer(
		$attachmentId,
		$playerData,
		$videoAttributes = ['inline' => true, 'controls' => true],
		$downloads = ['allow' =>  'inherit', 'original' => 'inherit'],
		$posterUrl = null,
		$videoId = null,
		$videoClasses = [],
		$videoStyles = [],
		$wrapperEle = null,
		$wrapperClasses = [],
		$wrapperStyles = []
	);

	/**
	 * Registers any additional settings widgets specific to the player.
	 *
	 * @param string $optionsPage
	 *
	 * @return mixed
	 */
	static function registerSettings($optionsPage);
}