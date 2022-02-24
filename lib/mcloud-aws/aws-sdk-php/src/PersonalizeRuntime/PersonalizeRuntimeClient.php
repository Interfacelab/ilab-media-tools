<?php

namespace MediaCloud\Vendor\Aws\PersonalizeRuntime;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Personalize Runtime** service.
 * @method \MediaCloud\Vendor\Aws\Result getPersonalizedRanking(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getPersonalizedRankingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getRecommendations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getRecommendationsAsync(array $args = [])
 */
class PersonalizeRuntimeClient extends AwsClient {}
