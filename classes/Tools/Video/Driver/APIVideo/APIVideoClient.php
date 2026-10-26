<?php

namespace MediaCloud\Plugin\Tools\Video\Driver\APIVideo;

use MediaCloud\Plugin\Tools\Video\Driver\APIVideo\Models\APIVideo;
use MediaCloud\Plugin\Tools\Video\VideoEncoderException;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use MediaCloud\Vendor\GuzzleHttp\Client;
use MediaCloud\Vendor\GuzzleHttp\Exception\GuzzleException;
use MediaCloud\Vendor\GuzzleHttp\Exception\RequestException;
use MediaCloud\Vendor\GuzzleHttp\RequestOptions;
use function MediaCloud\Plugin\Utilities\arrayPath;
class APIVideoClient {
    /** @var APIVideoSettings|APIVideoProSettings */
    private $settings;

    /** @var Client  */
    private $client;

    /** @var string */
    private $apiUrl;

    public function __construct() {
        $this->settings = APIVideoSettings::instance();
        $this->client = new Client([
            RequestOptions::HEADERS => [
                'Authorization' => 'Basic ' . base64_encode( $this->settings->apiKey . ':' ),
            ],
        ]);
        $this->apiUrl = ( $this->settings->sandboxMode ? 'https://sandbox.api.video' : 'https://ws.api.video' );
    }

    public function videoInfo( $videoId ) {
        try {
            $res = $this->client->get( "{$this->apiUrl}/videos/{$videoId}" );
            return json_decode( $res->getBody(), true );
        } catch ( GuzzleException $ex ) {
            $message = $this->guzzleExceptionMessage( $ex );
            Logger::error( "Error deleting video: " . $message );
            return null;
        }
    }

    public function videoEncodingInfo( $videoId ) {
        try {
            $res = $this->client->get( "{$this->apiUrl}/videos/{$videoId}/status" );
            return json_decode( $res->getBody(), true );
        } catch ( GuzzleException $ex ) {
            $message = $this->guzzleExceptionMessage( $ex );
            Logger::error( "Error getting video status: " . $message );
            return null;
        }
    }

    public function importVideo( $url, $attachmentId ) {
        try {
            $title = basename( parse_url( $url, PHP_URL_PATH ) );
            $meta = wp_get_attachment_metadata( $attachmentId, true );
            if ( $meta === false || empty( arrayPath( $meta, 'width', null ) ) ) {
                $attachedFile = get_attached_file( $attachmentId, true );
                if ( file_exists( $attachedFile ) ) {
                    $meta = wp_read_video_metadata( $attachedFile );
                }
            }
            $options = [
                'title'      => $title,
                'source'     => $url,
                'public'     => true,
                'mp4support' => true,
            ];
            $res = $this->client->post( "{$this->apiUrl}/videos", [
                RequestOptions::HEADERS => [
                    'Content-Type' => 'application/json',
                ],
                RequestOptions::BODY    => json_encode( $options ),
            ] );
            $result = json_decode( $res->getBody(), true );
            $videoId = arrayPath( $result, 'videoId' );
            $video = APIVideo::findOrCreate( $videoId );
            $video->attachment_id = $attachmentId;
            $video->status = APIVideo::STATUS_QUEUED;
            $video->save();
            $meta = ( $meta === false ? [] : $meta );
            $meta['api-video'] = [
                'videoId' => $videoId,
                'status'  => APIVideo::STATUS_QUEUED,
                'private' => false,
            ];
            wp_update_attachment_metadata( $attachmentId, $meta );
            return $video;
        } catch ( GuzzleException $ex ) {
            $message = $this->guzzleExceptionMessage( $ex );
            Logger::error( "Error creating video: " . $message );
            throw $ex;
        }
    }

    public function deleteVideo( $videoId ) {
        try {
            $this->client->delete( "{$this->apiUrl}/videos/{$videoId}" );
        } catch ( GuzzleException $ex ) {
            $message = $this->guzzleExceptionMessage( $ex );
            Logger::error( "Error deleting video: " . $message );
        }
    }

    public function registerWebhook( $url ) {
        try {
            $this->client->post( "{$this->apiUrl}/webhooks", [
                RequestOptions::HEADERS => [
                    'Content-Type' => 'application/json',
                ],
                RequestOptions::BODY    => json_encode( [
                    'events' => ["video.encoding.quality.completed"],
                    'url'    => $url,
                ] ),
            ] );
            return true;
        } catch ( GuzzleException $ex ) {
            $message = $this->guzzleExceptionMessage( $ex );
            Logger::error( "Error registering webhook: " . $message );
            return false;
        }
    }

    public function addSubtitles(
        $videoId,
        $languageCode,
        $name,
        $subtitlesFile
    ) {
        if ( !file_exists( $subtitlesFile ) ) {
            throw new VideoEncoderException("Caption file does not exist:" . $subtitlesFile);
        }
        try {
            $r = fopen( $subtitlesFile, 'r' );
            $res = $this->client->post( "{$this->apiUrl}/videos/{$videoId}/captions/{$languageCode}", [
                RequestOptions::MULTIPART => [[
                    'name'     => 'file',
                    'contents' => $r,
                ]],
            ] );
        } catch ( GuzzleException $ex ) {
            $message = $this->guzzleExceptionMessage( $ex );
            Logger::error( "Error uploading subtitles {$videoId}: " . $message );
            throw $ex;
        }
    }

    public function deleteSubtitles( $videoId, $languageCode ) {
        try {
            $this->client->delete( "{$this->apiUrl}/videos/{$videoId}/captions/{$languageCode}" );
        } catch ( GuzzleException $ex ) {
            $message = $this->guzzleExceptionMessage( $ex );
            Logger::error( "Error deleting video: " . $message );
        }
    }

    public function deleteWatermark( $watermarkId ) {
        try {
            $this->client->delete( "{$this->apiUrl}/watermarks/{$watermarkId}" );
        } catch ( GuzzleException $ex ) {
            $message = $this->guzzleExceptionMessage( $ex );
            Logger::error( "Error deleting watermark: " . $message );
        }
    }

    public function updateOrCreateWatermark( $watermarkData ) {
        if ( empty( $watermarkData ) ) {
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

}
