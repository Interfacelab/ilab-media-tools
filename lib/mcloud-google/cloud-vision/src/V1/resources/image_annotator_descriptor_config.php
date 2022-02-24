<?php

return [
    'interfaces' => [
        'google.cloud.vision.v1.ImageAnnotator' => [
            'AsyncBatchAnnotateFiles' => [
                'longRunning' => [
                    'operationReturnType' => '\MediaCloud\Vendor\Google\Cloud\Vision\V1\AsyncBatchAnnotateFilesResponse',
                    'metadataReturnType' => '\MediaCloud\Vendor\Google\Cloud\Vision\V1\OperationMetadata',
                    'initialPollDelayMillis' => '20000',
                    'pollDelayMultiplier' => '1.5',
                    'maxPollDelayMillis' => '45000',
                    'totalPollTimeoutMillis' => '86400000',
                ],
            ],
            'AsyncBatchAnnotateImages' => [
                'longRunning' => [
                    'operationReturnType' => '\MediaCloud\Vendor\Google\Cloud\Vision\V1\AsyncBatchAnnotateImagesResponse',
                    'metadataReturnType' => '\MediaCloud\Vendor\Google\Cloud\Vision\V1\OperationMetadata',
                    'initialPollDelayMillis' => '20000',
                    'pollDelayMultiplier' => '1.5',
                    'maxPollDelayMillis' => '45000',
                    'totalPollTimeoutMillis' => '86400000',
                ],
            ],
        ],
    ],
];
