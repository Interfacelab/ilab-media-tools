<?php

namespace MediaCloud\Plugin\Utilities\Logging\Ray;

use Spatie\WordPressRay\Spatie\Ray\Payloads\Payload;

class RayPayload extends Payload
{
	/** @var string */
	protected $html;

	/** @var string */
	protected $level;
	/**
	 * @var callable|null
	 */
	protected $pathMapper;

	public function __construct(string $level = '', string $html = '', callable $pathMapper = null) {
		$this->html = $html;
		$this->level = $level;
		$this->pathMapper = $pathMapper;
	}

	public function getType() : string {
		return 'custom';
	}

	public function getContent() : array {
		return ['content' => $this->html, 'label' => $this->level];
	}

	public function replaceRemotePathWithLocalPath(string $filePath) : string
	{
		if (is_callable($this->pathMapper)) {
			$localPath = call_user_func($this->pathMapper, $filePath);
			if ($localPath !== $filePath) {
				return $localPath;
			}
		}

		return parent::replaceRemotePathWithLocalPath($filePath);
	}
}
