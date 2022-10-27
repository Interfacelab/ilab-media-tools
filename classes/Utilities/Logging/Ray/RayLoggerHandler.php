<?php


namespace MediaCloud\Plugin\Utilities\Logging\Ray;

use MediaCloud\Vendor\Monolog\Handler\AbstractProcessingHandler;
use MediaCloud\Vendor\Monolog\Logger;
use Spatie\WordPressRay\Spatie\Ray\Payloads\Payload;
use function MediaCloud\Plugin\Utilities\arrayPath;


class RayLoggerHandler extends AbstractProcessingHandler {
	public function __construct($level = Logger::DEBUG, $bubble = true) {
		RayMacros::init();
		Payload::$originFactoryClass = RayOriginFactory::class;
		parent::__construct($level, $bubble);

		ray()->showWordPressErrors();
	}

	/**
	 * @inheritDoc
	 */
	protected function write(array $record):void {
		$message = htmlentities2($record['message']);

		$level = arrayPath($record, 'level_name', 'INFO');

		if ($level === 'INFO') {
			$message = "<span style='color:#0069b4'>{$level}</span> " .$message;
		} else if ($level === 'WARNING') {
			$message = "<span style='color:#ba5800'>{$level}</span> " .$message;
		} else if ($level === 'ERROR') {
			$message = "<span style='color:#970000'>{$level}</span> " .$message;
		} else {
			$message = $level.' '.$message;
		}

		$class = arrayPath($record, 'context/__class');
		if (!empty($class)) {
			$method = arrayPath($record, 'context/__method');
			$line = intval(arrayPath($record, 'context/__line', 0));

			$message .= "<br><div style='font-size:0.85rem !important; color:#a5a5a5'>$class::$method, line $line</div>";
		}

		$ray = ray()->mediacloud($level, $message);

		if ($level === 'INFO') {
			$ray->blue();
		} else if ($level === 'WARNING') {
			$ray->orange();
		} else if ($level === 'ERROR') {
			$ray->red();
		}
	}
}