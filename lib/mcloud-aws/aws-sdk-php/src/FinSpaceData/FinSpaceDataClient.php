<?php

namespace MediaCloud\Vendor\Aws\FinSpaceData;
use MediaCloud\Vendor\Aws\AwsClient;
use MediaCloud\Vendor\Aws\CommandInterface;
use MediaCloud\Vendor\Psr\Http\Message\RequestInterface;

/**
 * This client is used to interact with the **FinSpace Public API** service.
 * @method \MediaCloud\Vendor\Aws\Result createChangeset(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createChangesetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createDataView(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDataViewAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createDataset(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDatasetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteDataset(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteDatasetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getChangeset(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getChangesetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getDataView(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getDataViewAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getDataset(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getDatasetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getProgrammaticAccessCredentials(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getProgrammaticAccessCredentialsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getWorkingLocation(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getWorkingLocationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listChangesets(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listChangesetsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listDataViews(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listDataViewsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listDatasets(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listDatasetsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateChangeset(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateChangesetAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateDataset(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateDatasetAsync(array $args = [])
 */
class FinSpaceDataClient extends AwsClient {}
