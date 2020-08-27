<?php

namespace MediaCloud\Vendor\ILAB\B2;
use MediaCloud\Vendor\ILAB\B2\Exceptions\NotFoundException;
use MediaCloud\Vendor\ILAB\B2\Exceptions\ValidationException;
use MediaCloud\Vendor\ILAB\B2\Http\Client as HttpClient;

class Client
{
    protected $accountId;
    protected $applicationKeyId;
    protected $applicationKey;

    protected $authToken;
    protected $apiUrl;
    protected $downloadUrl;

    protected $client;

    /**
     * Client constructor. Accepts the account ID, application key and an optional array of options.
     *
     * @param string $applicationKeyId
     * @param string $applicationKey
     * @param array $options
     * @param AuthCacheInterface $authCache
     *
     * @throws \Exception
     */
    public function __construct($applicationKeyId, $applicationKey, array $options = [], $authCache = null)
    {
        $this->applicationKeyId = $applicationKeyId;
        $this->applicationKey = $applicationKey;

        if (isset($options['client'])) {
            $this->client = $options['client'];
        } else {
            $this->client = new HttpClient(['exceptions' => false]);
        }

        $this->authorizeAccount($authCache);
    }

    /**
     * The base download URL for the client
     * @return string|null
     */
    public function downloadUrl() {
        return $this->downloadUrl;
    }

    /**
     * Create a bucket with the given name and type.
     *
     * @param array $options
     * @return Bucket
     * @throws ValidationException
     */
    public function createBucket(array $options)
    {
        if (!in_array($options['BucketType'], [Bucket::TYPE_PUBLIC, Bucket::TYPE_PRIVATE])) {
            throw new ValidationException(
                sprintf('Bucket type must be %s or %s', Bucket::TYPE_PRIVATE, Bucket::TYPE_PUBLIC)
            );
        }

        $response = $this->client->request('POST', $this->apiUrl.'/b2_create_bucket', [
            'headers' => [
                'Authorization' => $this->authToken,
            ],
            'json' => [
                'accountId' => $this->accountId,
                'bucketName' => $options['BucketName'],
                'bucketType' => $options['BucketType']
            ]
        ]);

        return new Bucket($response['bucketId'], $response['bucketName'], $response['bucketType']);
    }

    /**
     * Updates the type attribute of a bucket by the given ID.
     *
     * @param array $options
     * @return Bucket
     * @throws ValidationException
     */
    public function updateBucket(array $options)
    {
        if (!in_array($options['BucketType'], [Bucket::TYPE_PUBLIC, Bucket::TYPE_PRIVATE])) {
            throw new ValidationException(
                sprintf('Bucket type must be %s or %s', Bucket::TYPE_PRIVATE, Bucket::TYPE_PUBLIC)
            );
        }

        if (!isset($options['BucketId']) && isset($options['BucketName'])) {
            $options['BucketId'] = $this->getBucketIdFromName($options['BucketName']);
        }

        $response = $this->client->request('POST', $this->apiUrl.'/b2_update_bucket', [
            'headers' => [
                'Authorization' => $this->authToken,
            ],
            'json' => [
                'accountId' => $this->accountId,
                'bucketId' => $options['BucketId'],
                'bucketType' => $options['BucketType']
            ]
        ]);

        return new Bucket($response['bucketId'], $response['bucketName'], $response['bucketType']);
    }

    /**
     * Returns a list of bucket objects representing the buckets on the account.
     *
     * @return array
     */
    public function listBuckets()
    {
        $buckets = [];

        $response = $this->client->request('POST', $this->apiUrl.'/b2_list_buckets', [
            'headers' => [
                'Authorization' => $this->authToken,
            ],
            'json' => [
                'accountId' => $this->accountId
            ]
        ]);

        foreach ($response['buckets'] as $bucket) {
            $buckets[] = new Bucket($bucket['bucketId'], $bucket['bucketName'], $bucket['bucketType']);
        }

        return $buckets;
    }

    /**
     * Deletes the bucket identified by its ID.
     *
     * @param array $options
     * @return bool
     */
    public function deleteBucket(array $options)
    {
        if (!isset($options['BucketId']) && isset($options['BucketName'])) {
            $options['BucketId'] = $this->getBucketIdFromName($options['BucketName']);
        }

        $this->client->request('POST', $this->apiUrl.'/b2_delete_bucket', [
            'headers' => [
                'Authorization' => $this->authToken
            ],
            'json' => [
                'accountId' => $this->accountId,
                'bucketId' => $options['BucketId']
            ]
        ]);

        return true;
    }

    /**
     * Uploads a file to a bucket and returns a File object.
     *
     * @param array $options
     * @return File
     */
    public function upload(array $options)
    {
        // Clean the path if it starts with /.
        if (substr($options['FileName'], 0, 1) === '/') {
            $options['FileName'] = ltrim($options['FileName'], '/');
        }

        if (!isset($options['BucketId']) && isset($options['BucketName'])) {
            $options['BucketId'] = $this->getBucketIdFromName($options['BucketName']);
        }

        // Retrieve the URL that we should be uploading to.
        $response = $this->client->request('POST', $this->apiUrl.'/b2_get_upload_url', [
            'headers' => [
                'Authorization' => $this->authToken
            ],
            'json' => [
                'bucketId' => $options['BucketId']
            ]
        ]);

        $uploadEndpoint = $response['uploadUrl'];
        $uploadAuthToken = $response['authorizationToken'];

        if (is_resource($options['Body'])) {
            // We need to calculate the file's hash incrementally from the stream.
            $context = hash_init('sha1');
            hash_update_stream($context, $options['Body']);
            $hash = hash_final($context);

            // Similarly, we have to use fstat to get the size of the stream.
            $size = fstat($options['Body'])['size'];
            
            // Rewind the stream before passing it to the HTTP client.
            rewind($options['Body']);
        } else {
            // We've been given a simple string body, it's super simple to calculate the hash and size.
            $hash = sha1($options['Body']);
            $size = mb_strlen($options['Body']);
        }

        if (!isset($options['FileLastModified'])) {
            $options['FileLastModified'] = round(microtime(true) * 1000);
        }

        if (!isset($options['FileContentType'])) {
            $options['FileContentType'] = 'b2/x-auto';
        }

        $response = $this->client->request('POST', $uploadEndpoint, [
            'headers' => [
                'Authorization' => $uploadAuthToken,
                'Content-Type' => $options['FileContentType'],
                'Content-Length' => $size,
                'X-Bz-File-Name' => $options['FileName'],
                'X-Bz-Content-Sha1' => $hash,
                'X-Bz-Info-src_last_modified_millis' => $options['FileLastModified']
            ],
            'body' => $options['Body']
        ]);

        return new File(
            $response['fileId'],
            $response['fileName'],
            $response['contentSha1'],
            $response['contentLength'],
            $response['contentType'],
            $response['fileInfo']
        );
    }

    /**
     * Download a file from a B2 bucket.
     *
     * @param array $options
     * @return bool|mixed|string
     */
    public function download(array $options)
    {
        $requestUrl = null;
        $requestOptions = [
            'headers' => [
                'Authorization' => $this->authToken
            ],
            'sink' => isset($options['SaveAs']) ? $options['SaveAs'] : null
        ];

        if (isset($options['FileId'])) {
            $requestOptions['query'] = ['fileId' => $options['FileId']];
            $requestUrl = $this->downloadUrl.'/b2api/v2/b2_download_file_by_id';
        } else {
            if (!isset($options['BucketName']) && isset($options['BucketId'])) {
                $options['BucketName'] = $this->getBucketNameFromId($options['BucketId']);
            }

            $requestUrl = sprintf('%s/file/%s/%s', $this->downloadUrl, $options['BucketName'], $options['FileName']);
        }

        $response = $this->client->request('GET', $requestUrl, $requestOptions, false);

        return isset($options['SaveAs']) ? true : $response;
    }

    /**
     * Retrieve a collection of File objects representing the files stored inside a bucket.
     *
     * @param array $options
     * @return array
     */
    public function listFiles(array $options)
    {
        // if FileName is set, we only attempt to retrieve information about that single file.
        $fileName = !empty($options['FileName']) ? $options['FileName'] : null;

        $nextFileName = null;
        $maxFileCount = 1000;
        $files = [];

        if (!isset($options['BucketId']) && isset($options['BucketName'])) {
            $options['BucketId'] = $this->getBucketIdFromName($options['BucketName']);
        }

        if ($fileName) {
            $nextFileName = $fileName;
            $maxFileCount = 1;
        }

        // B2 returns, at most, 1000 files per "page". Loop through the pages and compile an array of File objects.
        while (true) {
        	$json = [
		        'bucketId' => $options['BucketId'],
		        'startFileName' => $nextFileName,
		        'maxFileCount' => $maxFileCount,
	        ];

        	if (isset($options['prefix'])) {
        		$json['prefix'] = $options['prefix'];
	        }

	        if (isset($options['delimiter'])) {
	        	$json['delimiter'] = $options['delimiter'];
	        }

            $response = $this->client->request('POST', $this->apiUrl.'/b2_list_file_names', [
                'headers' => [
                    'Authorization' => $this->authToken
                ],
                'json' => $json
            ]);

            foreach ($response['files'] as $file) {
                // if we have a file name set, only retrieve information if the file name matches
                if (!$fileName || ($fileName === $file['fileName'])) {
                    $files[] = new File($file['fileId'], $file['fileName'], null, $file['contentLength']);
                }
            }

            if ($fileName || $response['nextFileName'] === null) {
                // We've got all the files - break out of loop.
                break;
            }

            $nextFileName = $response['nextFileName'];
        }

        return $files;
    }

    /**
     * Test whether a file exists in B2 for the given bucket.
     *
     * @param array $options
     * @return boolean
     */
    public function fileExists(array $options)
    {
        $files = $this->listFiles($options);

        return !empty($files);
    }


    /**
     * Returns a single File object representing a file stored on B2.
     *
     * @param array $options
     * @throws NotFoundException If no file id was provided and BucketName + FileName does not resolve to a file, a NotFoundException is thrown.
     * @return File
     */
    public function getFile(array $options)
    {
        if (!isset($options['FileId']) && isset($options['BucketName']) && isset($options['FileName'])) {
            $options['FileId'] = $this->getFileIdFromBucketAndFileName($options['BucketName'], $options['FileName']);

            if (!$options['FileId']) {
                throw new NotFoundException();
            }
        }

        $response = $this->client->request('POST', $this->apiUrl.'/b2_get_file_info', [
            'headers' => [
                'Authorization' => $this->authToken
            ],
            'json' => [
                'fileId' => $options['FileId']
            ]
        ]);

        return new File(
            $response['fileId'],
            $response['fileName'],
            $response['contentSha1'],
            $response['contentLength'],
            $response['contentType'],
            $response['fileInfo'],
            $response['bucketId'],
            $response['action'],
            $response['uploadTimestamp']
        );
    }

    /**
     * Deletes the file identified by ID from Backblaze B2.
     *
     * @param array $options
     * @return bool
     */
    public function deleteFile(array $options)
    {
        if (!isset($options['FileName'])) {
            $file = $this->getFile($options);

            $options['FileName'] = $file->getName();
        }

        if (!isset($options['FileId']) && isset($options['BucketName']) && isset($options['FileName'])) {
            $file = $this->getFile($options);

            $options['FileId'] = $file->getId();
        }

        $this->client->request('POST', $this->apiUrl.'/b2_delete_file_version', [
            'headers' => [
                'Authorization' => $this->authToken
            ],
            'json' => [
                'fileName' => $options['FileName'],
                'fileId' => $options['FileId']
            ]
        ]);

        return true;
    }

    /**
     * Authorize the B2 account in order to get an auth token and API/download URLs.
     *
     * @param AuthCacheInterface $authCache
     * @throws \Exception
     */
    protected function authorizeAccount($authCache = null)
    {
        $auth = base64_encode($this->applicationKeyId . ":" . $this->applicationKey);

        $authData = [];
        if (!empty($authCache)) {
            $authData = $authCache->cachedB2Auth($auth);
        }

        if (empty($authData)) {
            $authData = $this->client->request('GET', 'https://api.backblazeb2.com/b2api/v2/b2_authorize_account', [
                'headers' => [
                    'Authorization' => 'Basic ' . $auth
                ]
            ]);

            if (!empty($authData) && !empty($authCache)) {
                $authCache->cacheB2Auth($auth, $authData);
            }
        }

        if (!empty($authData)) {
            $this->accountId = $authData['accountId'];
            $this->authToken = $authData['authorizationToken'];
            $this->apiUrl = $authData['apiUrl'].'/b2api/v2';
            $this->downloadUrl = $authData['downloadUrl'];
        }
    }

    /**
     * Maps the provided bucket name to the appropriate bucket ID.
     *
     * @param $name
     * @return null
     */
    protected function getBucketIdFromName($name)
    {
        $buckets = $this->listBuckets();

        foreach ($buckets as $bucket) {
            if ($bucket->getName() === $name) {
                return $bucket->getId();
            }
        }

        return null;
    }

    /**
     * Maps the provided bucket ID to the appropriate bucket name.
     *
     * @param $id
     * @return null
     */
    protected function getBucketNameFromId($id)
    {
        $buckets = $this->listBuckets();

        foreach ($buckets as $bucket) {
            if ($bucket->getId() === $id) {
                return $bucket->getName();
            }
        }

        return null;
    }

    protected function getFileIdFromBucketAndFileName($bucketName, $fileName)
    {
        $files = $this->listFiles([
            'BucketName' => $bucketName,
            'FileName' => $fileName,
        ]);

        foreach ($files as $file) {
            if ($file->getName() === $fileName) {
                return $file->getId();
            }
        }

        return null;
    }
}
