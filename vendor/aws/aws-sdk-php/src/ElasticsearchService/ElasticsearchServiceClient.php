<?php
namespace ILAB_Aws\ElasticsearchService;

use ILAB_Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Elasticsearch Service** service.
 *
 * @method \ILAB_Aws\Result addTags(array $args = [])
 * @method \GuzzleHttp\Promise\Promise addTagsAsync(array $args = [])
 * @method \ILAB_Aws\Result createElasticsearchDomain(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createElasticsearchDomainAsync(array $args = [])
 * @method \ILAB_Aws\Result deleteElasticsearchDomain(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteElasticsearchDomainAsync(array $args = [])
 * @method \ILAB_Aws\Result describeElasticsearchDomain(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeElasticsearchDomainAsync(array $args = [])
 * @method \ILAB_Aws\Result describeElasticsearchDomainConfig(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeElasticsearchDomainConfigAsync(array $args = [])
 * @method \ILAB_Aws\Result describeElasticsearchDomains(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeElasticsearchDomainsAsync(array $args = [])
 * @method \ILAB_Aws\Result listDomainNames(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listDomainNamesAsync(array $args = [])
 * @method \ILAB_Aws\Result listTags(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsAsync(array $args = [])
 * @method \ILAB_Aws\Result removeTags(array $args = [])
 * @method \GuzzleHttp\Promise\Promise removeTagsAsync(array $args = [])
 * @method \ILAB_Aws\Result updateElasticsearchDomainConfig(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateElasticsearchDomainConfigAsync(array $args = [])
 */
class ElasticsearchServiceClient extends AwsClient {}
