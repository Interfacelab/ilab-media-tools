<?php

namespace MediaCloud\Vendor\Aws\Outposts;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Outposts** service.
 * @method \MediaCloud\Vendor\Aws\Result createOutpost(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createOutpostAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteOutpost(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteOutpostAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteSite(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteSiteAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getOutpost(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getOutpostAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getOutpostInstanceTypes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getOutpostInstanceTypesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listOutposts(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listOutpostsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listSites(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listSitesAsync(array $args = [])
 */
class OutpostsClient extends AwsClient {}
