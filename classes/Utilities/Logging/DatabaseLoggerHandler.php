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

namespace ILAB\MediaCloud\Utilities\Logging;

use Monolog\Handler\AbstractProcessingHandler;

class DatabaseLoggerHandler extends AbstractProcessingHandler {
    /** @var DatabaseLogger|null Database logger */
    private $logger = null;

    public function __construct($level = 100, $bubble = true) {
        parent::__construct($level, $bubble);

        $this->logger = new DatabaseLogger();
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     * @return void
     */
    protected function write(array $record) {
        $context = '';
        if (isset($record['context']) && is_array($record['context']) && (count($record['context']) > 0)) {
            $flattenedContext = [];
            foreach($record['context'] as $key => $value) {
                if (is_array($value)) {
                    continue;
                }

                if ($value instanceof \WP_Error) {
                	$value = $value->get_error_message();
                }

                $flattenedContext[] = "$key = $value";
            }

            $context = implode(", ", $flattenedContext);
        }

        $this->logger->log($record['channel'], $record['level_name'], $record['message'], $context);
    }
}