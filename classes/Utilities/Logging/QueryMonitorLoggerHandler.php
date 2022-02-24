<?php


namespace MediaCloud\Plugin\Utilities\Logging;

use MediaCloud\Vendor\Monolog\Handler\AbstractProcessingHandler;


class QueryMonitorLoggerHandler extends AbstractProcessingHandler {

	/**
	 * @inheritDoc
	 */
	protected function write(array $record):void {
		$level = strtolower($record['level_name']);
		$message = htmlentities2($record['message']);
		do_action("qm/$level", "[{$record['channel']}] $message");
	}
}