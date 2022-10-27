<?php

return [
    'interfaces' => [
        'google.cloud.vision.v1.ProductSearch' => [
            'ImportProductSets' => [
                'longRunning' => [
                    'operationReturnType' => '\MediaCloud\Vendor\Google\Cloud\Vision\V1\ImportProductSetsResponse',
                    'metadataReturnType' => '\MediaCloud\Vendor\Google\Cloud\Vision\V1\BatchOperationMetadata',
                    'initialPollDelayMillis' => '20000',
                    'pollDelayMultiplier' => '1.5',
                    'maxPollDelayMillis' => '45000',
                    'totalPollTimeoutMillis' => '86400000',
                ],
            ],
            'PurgeProducts' => [
                'longRunning' => [
                    'operationReturnType' => '\MediaCloud\Vendor\Google\Protobuf\GPBEmpty',
                    'metadataReturnType' => '\MediaCloud\Vendor\Google\Cloud\Vision\V1\BatchOperationMetadata',
                    'initialPollDelayMillis' => '20000',
                    'pollDelayMultiplier' => '1.5',
                    'maxPollDelayMillis' => '45000',
                    'totalPollTimeoutMillis' => '86400000',
                ],
            ],
            'ListProductSets' => [
                'pageStreaming' => [
                    'requestPageTokenGetMethod' => 'getPageToken',
                    'requestPageTokenSetMethod' => 'setPageToken',
                    'requestPageSizeGetMethod' => 'getPageSize',
                    'requestPageSizeSetMethod' => 'setPageSize',
                    'responsePageTokenGetMethod' => 'getNextPageToken',
                    'resourcesGetMethod' => 'getProductSets',
                ],
            ],
            'ListProducts' => [
                'pageStreaming' => [
                    'requestPageTokenGetMethod' => 'getPageToken',
                    'requestPageTokenSetMethod' => 'setPageToken',
                    'requestPageSizeGetMethod' => 'getPageSize',
                    'requestPageSizeSetMethod' => 'setPageSize',
                    'responsePageTokenGetMethod' => 'getNextPageToken',
                    'resourcesGetMethod' => 'getProducts',
                ],
            ],
            'ListProductsInProductSet' => [
                'pageStreaming' => [
                    'requestPageTokenGetMethod' => 'getPageToken',
                    'requestPageTokenSetMethod' => 'setPageToken',
                    'requestPageSizeGetMethod' => 'getPageSize',
                    'requestPageSizeSetMethod' => 'setPageSize',
                    'responsePageTokenGetMethod' => 'getNextPageToken',
                    'resourcesGetMethod' => 'getProducts',
                ],
            ],
            'ListReferenceImages' => [
                'pageStreaming' => [
                    'requestPageTokenGetMethod' => 'getPageToken',
                    'requestPageTokenSetMethod' => 'setPageToken',
                    'requestPageSizeGetMethod' => 'getPageSize',
                    'requestPageSizeSetMethod' => 'setPageSize',
                    'responsePageTokenGetMethod' => 'getNextPageToken',
                    'resourcesGetMethod' => 'getReferenceImages',
                ],
            ],
        ],
    ],
];
