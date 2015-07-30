<?php
require_once(ILAB_VENDOR_DIR.'/autoload.php');

use GuzzleHttp\Promise;

class ILabMediaS3CredentialsProvider
{
    public static function ilab()
    {
        return function ()
        {
            // Use credentials from environment variables, if available
            $key = get_option('ilab-media-s3-access-key', getenv('ILAB_AWS_S3_ACCESS_KEY'));
            $secret = get_option('ilab-media-s3-secret', getenv('ILAB_AWS_S3_ACCESS_SECRET'));
            if ($key && $secret)
            {
                return Promise\promise_for(
                    new \Aws\Credentials\Credentials($key, $secret)
                );
            }

            return self::reject('Could not find credentials.');
        };
    }
}