<?php

namespace MediaCloud\Plugin\Tools\Video\Driver\Cloudflare;

use MediaCloud\Plugin\Tools\Video\VideoEncoderException;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use MediaCloud\Vendor\GuzzleHttp\Client;
use MediaCloud\Vendor\GuzzleHttp\Exception\GuzzleException;
use MediaCloud\Vendor\GuzzleHttp\Exception\RequestException;
use MediaCloud\Vendor\GuzzleHttp\RequestOptions;
use function MediaCloud\Plugin\Utilities\arrayPath;
class CloudflareStreamClient {
    /** @var CloudflareStreamToolSettings|CloudflareStreamToolProSettings */
    private $settings;

    /** @var Client  */
    private $client;

    /** @var array|null */
    private $signingKey = null;

    public function __construct() {
        $this->settings = CloudflareStreamToolSettings::instance();
        $this->client = new Client([
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer ' . $this->settings->apiKey,
            ],
        ]);
    }

    public function videoInfo( $videoId ) {
        try {
            $res = $this->client->get( "https://api.cloudflare.com/client/v4/accounts/{$this->settings->accountId}/stream/{$videoId}" );
            $status = $res->getStatusCode();
            if ( $status < 200 || $status > 299 ) {
                Logger::error(
                    "Error fetching video info.",
                    [],
                    __METHOD__,
                    __LINE__
                );
                return null;
            }
            return json_decode( $res->getBody(), true );
        } catch ( \Exception $ex ) {
            Logger::error(
                "Error fetching video info {$videoId}: " . $ex->getMessage(),
                [],
                __METHOD__,
                __LINE__
            );
            return null;
        }
    }

    public function deleteVideo( $videoId ) {
        try {
            $res = $this->client->delete( "https://api.cloudflare.com/client/v4/accounts/{$this->settings->accountId}/stream/{$videoId}" );
            $status = $res->getStatusCode();
            if ( $status < 200 || $status > 299 ) {
                Logger::error(
                    "Error fetching video info.",
                    [],
                    __METHOD__,
                    __LINE__
                );
                return false;
            }
            return true;
        } catch ( \Exception $ex ) {
            Logger::error(
                "Error deleting video info {$videoId}: " . $ex->getMessage(),
                [],
                __METHOD__,
                __LINE__
            );
            return false;
        }
    }

    public function importVideo( $url, $attachmentId ) {
        try {
            Logger::info(
                "Importing url {$url}:" . json_encode( [
                    "url"  => $url,
                    "meta" => [
                        'attachmentId' => $attachmentId,
                        'name'         => basename( parse_url( $url, PHP_URL_PATH ) ),
                    ],
                ], JSON_PRETTY_PRINT ),
                [],
                __METHOD__,
                __LINE__
            );
            $options = [
                "url"  => $url,
                "meta" => [
                    'aid'  => "{$attachmentId}",
                    'name' => basename( parse_url( $url, PHP_URL_PATH ) ),
                ],
            ];
            $private = false;
            Logger::info(
                json_encode( $options ),
                [],
                __METHOD__,
                __LINE__
            );
            $res = $this->client->post( "https://api.cloudflare.com/client/v4/accounts/{$this->settings->accountId}/stream/copy", [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer ' . $this->settings->apiKey,
                ],
                RequestOptions::BODY    => json_encode( $options ),
            ] );
            $status = $res->getStatusCode();
            if ( $status < 200 || $status > 299 ) {
                Logger::error(
                    "Error importing video.",
                    [],
                    __METHOD__,
                    __LINE__
                );
                return false;
            }
            $data = json_decode( $res->getBody(), true );
            $errors = arrayPath( $data, 'errors', [] );
            if ( !empty( $errors ) ) {
                Logger::error(
                    "Error importing video: " . implode( "\n", $errors ),
                    [],
                    __METHOD__,
                    __LINE__
                );
                return false;
            }
            $videoId = arrayPath( $data, 'result/uid', null );
            $status = arrayPath( $data, 'result/status/state', null );
            if ( empty( $videoId ) ) {
                Logger::error(
                    "Error importing video.  No uid in response.",
                    [],
                    __METHOD__,
                    __LINE__
                );
                return false;
            }
            if ( !empty( $private ) ) {
                update_post_meta( $attachmentId, '_cloudflare_stream_private', true );
            }
            //			$meta = wp_get_attachment_metadata($attachmentId, true);
            //			$meta = $meta === false ? [] : $meta;
            //			$meta['cloudflare-stream'] = [
            //				'videoId' => $videoId,
            //				'status' => $status,
            //				'private' => $private,
            //			];
            //			wp_update_attachment_metadata($attachmentId, $meta);
            return true;
        } catch ( GuzzleException $ex ) {
            $message = $this->guzzleExceptionMessage( $ex );
            Logger::error(
                "Error importing video {$attachmentId}: " . $message,
                [],
                __METHOD__,
                __LINE__
            );
            return false;
        }
    }

    public function registerWebhook( $webhookUrl ) {
        try {
            $res = $this->client->put( "https://api.cloudflare.com/client/v4/accounts/{$this->settings->accountId}/stream/webhook", [
                RequestOptions::HEADERS => [
                    'Content-Type' => 'application/json',
                ],
                RequestOptions::BODY    => json_encode( [
                    "notificationUrl" => $webhookUrl,
                ] ),
            ] );
            $status = $res->getStatusCode();
            if ( $status < 200 || $status > 299 ) {
                Logger::error(
                    "Error fetching video info.",
                    [],
                    __METHOD__,
                    __LINE__
                );
                return false;
            }
            $result = json_decode( $res->getBody(), true );
            $success = arrayPath( $result, 'success', false );
            return $success === true;
        } catch ( \Exception $ex ) {
            Logger::error(
                "Error registering webhook {$webhookUrl}: " . $ex->getMessage(),
                [],
                __METHOD__,
                __LINE__
            );
            return false;
        }
    }

    public function addSubtitles(
        $videoId,
        $languageCode,
        $name,
        $subtitles
    ) {
        if ( !file_exists( $subtitles ) ) {
            throw new VideoEncoderException("Caption file does not exist.");
        }
        try {
            $r = fopen( $subtitles, 'r' );
            $res = $this->client->put( "https://api.cloudflare.com/client/v4/accounts/{$this->settings->accountId}/stream/{$videoId}/captions/{$languageCode}", [
                RequestOptions::MULTIPART => [[
                    'name'     => 'file',
                    'contents' => $r,
                ]],
            ] );
            $status = $res->getStatusCode();
            if ( $status < 200 || $status > 299 ) {
                Logger::error(
                    "Error adding captions for upload: " . $status,
                    [],
                    __METHOD__,
                    __LINE__
                );
                throw new VideoEncoderException("Error adding captions.");
            }
        } catch ( GuzzleException $ex ) {
            $message = $this->guzzleExceptionMessage( $ex );
            Logger::error(
                "Error uploading subtitles {$videoId}: " . $message,
                [],
                __METHOD__,
                __LINE__
            );
            throw $ex;
        }
    }

    public function deleteSubtitles( $videoId, $languageCode ) {
        try {
            $this->client->delete( "https://api.cloudflare.com/client/v4/accounts/{$this->settings->accountId}/stream/{$videoId}/captions/{$languageCode}" );
        } catch ( \Exception $ex ) {
            Logger::error(
                "Error uploading subtitles {$videoId}: " . $ex->getMessage(),
                [],
                __METHOD__,
                __LINE__
            );
        }
    }

    public function deleteWatermark( $watermarkId ) {
        try {
            $this->client->delete( "https://api.cloudflare.com/client/v4/accounts/{$this->settings->accountId}/stream/watermarks/{$watermarkId}" );
        } catch ( \Exception $ex ) {
            Logger::error(
                "Error deleting watermark {$watermarkId}: " . $ex->getMessage(),
                [],
                __METHOD__,
                __LINE__
            );
        }
    }

    public function updateOrCreateWatermark( $watermarkData ) {
        if ( empty( $watermarkData ) ) {
            return false;
        }
        return false;
    }

    private function loadKey() {
        if ( !empty( $this->signingKey ) ) {
            return true;
        }
        $key = get_option( 'mcloud_cloudflare_stream_key' );
        $keyExpired = !empty( $key ) && time() >= $key['expires'];
        $rotateKey = $keyExpired && !empty( $this->settings->rotateKeys );
        if ( !empty( $key ) && (empty( $keyExpired ) || empty( $rotateKey )) ) {
            $this->signingKey = $key['key'];
            return true;
        }
        if ( !empty( $key ) ) {
            $this->client->post( "https://api.cloudflare.com/client/v4/accounts/{$this->settings->accountId}/stream/keys/{$key['key']['id']}", [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer ' . $this->settings->apiKey,
                ],
            ] );
        }
        try {
            $res = $this->client->post( "https://api.cloudflare.com/client/v4/accounts/{$this->settings->accountId}/stream/keys", [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer ' . $this->settings->apiKey,
                ],
            ] );
            $status = $res->getStatusCode();
            if ( $status < 200 || $status > 299 ) {
                Logger::error(
                    "Error importing video.",
                    [],
                    __METHOD__,
                    __LINE__
                );
                return false;
            }
            $data = json_decode( $res->getBody(), true );
            if ( empty( arrayPath( $data, 'result', null ) ) ) {
                return false;
            }
            $this->signingKey = $data['result'];
            update_option( 'mcloud_cloudflare_stream_key', [
                'key'     => $this->signingKey,
                'expires' => time() + 60 * 60 * 24 * $this->settings->keyExpiration,
            ], false );
            return true;
        } catch ( GuzzleException $ex ) {
            Logger::error(
                "Error fetching key: " . $ex->getMessage(),
                [],
                __METHOD__,
                __LINE__
            );
            return false;
        }
    }

    private function guzzleExceptionMessage( GuzzleException $ex ) {
        if ( $ex instanceof RequestException ) {
            $response = $ex->getResponse();
            if ( $response !== null ) {
                return $response->getBody()->getContents();
            }
        }
        return $ex->getMessage();
    }

    private function base64Url( $data ) {
        return str_replace( ['+', '/', '='], ['-', '_', ''], base64_encode( $data ) );
    }

    public function token( $uid, $videoDuration ) {
        if ( !$this->loadKey() ) {
            return null;
        }
        $privateKey = base64_decode( $this->signingKey['pem'] );
        $header = [
            'alg' => 'RS256',
            'kid' => $this->signingKey['id'],
        ];
        $payload = [
            'sub' => $uid,
            'kid' => $this->signingKey['id'],
            'exp' => time() + $videoDuration + $this->settings->secureExpiration * 60,
        ];
        $encodedHeader = $this->base64Url( json_encode( $header ) );
        $encodedPayload = $this->base64Url( json_encode( $payload ) );
        openssl_sign(
            "{$encodedHeader}.{$encodedPayload}",
            $signature,
            $privateKey,
            'RSA-SHA256'
        );
        $encodedSignature = $this->base64Url( $signature );
        return "{$encodedHeader}.{$encodedPayload}.{$encodedSignature}";
    }

}
