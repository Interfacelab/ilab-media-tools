<?php
namespace ILAB_Aws\CloudSearchDomain;

use ILAB_Aws\AwsClient;
use GuzzleHttp\Psr7\Uri;

/**
 * This client is used to search and upload documents to an **Amazon CloudSearch** Domain.
 *
 * @method \ILAB_Aws\Result search(array $args = [])
 * @method \GuzzleHttp\Promise\Promise searchAsync(array $args = [])
 * @method \ILAB_Aws\Result suggest(array $args = [])
 * @method \GuzzleHttp\Promise\Promise suggestAsync(array $args = [])
 * @method \ILAB_Aws\Result uploadDocuments(array $args = [])
 * @method \GuzzleHttp\Promise\Promise uploadDocumentsAsync(array $args = [])
 */
class CloudSearchDomainClient extends AwsClient
{
    public static function getArguments()
    {
        $args = parent::getArguments();
        $args['endpoint']['required'] = true;
        $args['region']['default'] = function (array $args) {
            // Determine the region from the provided endpoint.
            // (e.g. http://search-blah.{region}.cloudsearch.amazonaws.com)
            return explode('.', new Uri($args['endpoint']))[1];
        };

        return $args;
    }
}
