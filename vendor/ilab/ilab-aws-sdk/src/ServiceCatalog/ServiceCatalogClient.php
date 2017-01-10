<?php
namespace ILAB_Aws\ServiceCatalog;

use ILAB_Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Service Catalog** service.
 * @method \ILAB_Aws\Result describeProduct(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeProductAsync(array $args = [])
 * @method \ILAB_Aws\Result describeProductView(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeProductViewAsync(array $args = [])
 * @method \ILAB_Aws\Result describeProvisioningParameters(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeProvisioningParametersAsync(array $args = [])
 * @method \ILAB_Aws\Result describeRecord(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeRecordAsync(array $args = [])
 * @method \ILAB_Aws\Result listLaunchPaths(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listLaunchPathsAsync(array $args = [])
 * @method \ILAB_Aws\Result listRecordHistory(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listRecordHistoryAsync(array $args = [])
 * @method \ILAB_Aws\Result provisionProduct(array $args = [])
 * @method \GuzzleHttp\Promise\Promise provisionProductAsync(array $args = [])
 * @method \ILAB_Aws\Result scanProvisionedProducts(array $args = [])
 * @method \GuzzleHttp\Promise\Promise scanProvisionedProductsAsync(array $args = [])
 * @method \ILAB_Aws\Result searchProducts(array $args = [])
 * @method \GuzzleHttp\Promise\Promise searchProductsAsync(array $args = [])
 * @method \ILAB_Aws\Result terminateProvisionedProduct(array $args = [])
 * @method \GuzzleHttp\Promise\Promise terminateProvisionedProductAsync(array $args = [])
 * @method \ILAB_Aws\Result updateProvisionedProduct(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateProvisionedProductAsync(array $args = [])
 */
class ServiceCatalogClient extends AwsClient {}
