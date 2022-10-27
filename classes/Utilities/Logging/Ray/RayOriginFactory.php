<?php

namespace MediaCloud\Plugin\Utilities\Logging\Ray;

use Spatie\WordPressRay\Spatie\Backtrace\Backtrace;
use Spatie\WordPressRay\Spatie\Backtrace\Frame;
use Spatie\WordPressRay\Spatie\Ray\Origin\Hostname;
use Spatie\WordPressRay\Spatie\Ray\Origin\Origin;
use Spatie\WordPressRay\Spatie\Ray\Origin\OriginFactory;
use Spatie\WordPressRay\Spatie\Ray\Ray;

class RayOriginFactory implements OriginFactory {
	public function getOrigin() : Origin {
		$frame = $this->getFrame();
		return new Origin($frame ? $frame->file : null, $frame ? $frame->lineNumber : null, Hostname::get());
	}

	/**
	 * @return \Spatie\Backtrace\Frame|null
	 */
	protected function getFrame() {
		$frames = $this->getAllFrames();
		$index = $this->getIndexOfRealFrame($frames);
		if ($index === -1) {
			$index = $this->getIndexOfRayFrame($frames);
		}
		return $frames[$index] ?? null;
	}

	protected function getAllFrames() : array {
		$frames = Backtrace::create()->frames();
		return \array_reverse($frames, \true);
	}

	/**
	 * @param array $frames
	 *
	 * @return int|null
	 */
	protected function getIndexOfRealFrame(array $frames) {
		$lastIndex = -1;
		/**
		 * @var int $index
		 * @var Frame $frame
		 */
		foreach($frames as $index => $frame) {
			if (($lastIndex !== -1) && (strpos($frame->class, 'MediaCloud\Plugin\Utilities\Logging') === 0)) {
				return $lastIndex;
			}

			$lastIndex = $index;
		}

		return -1;
	}

	/**
	 * @param array $frames
	 *
	 * @return int|null
	 */
	protected function getIndexOfRayFrame(array $frames) {
		$index = $this->search(function (Frame $frame) {
			if ($frame->class === Ray::class) {
				return \true;
			}
			if ($this->startsWith($frame->file, \dirname(__DIR__))) {
				return \true;
			}
			return \false;
		}, $frames);
		return $index + 1;
	}

	public function startsWith(string $hayStack, string $needle) : bool {
		return \strpos($hayStack, $needle) === 0;
	}

	/**
	 * @param callable $callable
	 * @param array $items
	 *
	 * @return int|null
	 */
	protected function search(callable $callable, array $items) {
		foreach ($items as $key => $item) {
			if ($callable($item, $key)) {
				return $key;
			}
		}
		return null;
	}
}
