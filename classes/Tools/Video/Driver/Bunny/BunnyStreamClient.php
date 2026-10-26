<?php

namespace MediaCloud\Plugin\Tools\Video\Driver\Bunny;

use MediaCloud\Plugin\Tools\Video\Driver\Bunny\Models\BunnyVideo;
use MediaCloud\Plugin\Tools\Video\VideoEncoderException;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use MediaCloud\Vendor\GuzzleHttp\Client;
use MediaCloud\Vendor\GuzzleHttp\RequestOptions;
use function MediaCloud\Plugin\Utilities\arrayPath;
class BunnyStreamClient {
    /** @var BunnyStreamToolSettings|BunnyStreamToolProSettings */
    private $settings;

    /** @var Client  */
    private $client;

    public function __construct() {
        $this->settings = BunnyStreamToolSettings::instance();
        $this->client = new Client([
            RequestOptions::HEADERS => [
                'AccessKey' => $this->settings->apiKey,
            ],
        ]);
    }

    public function videoInfo( $videoId ) {
        try {
            $res = $this->client->get( "https://video.bunnycdn.com/library/{$this->settings->libraryId}/videos/{$videoId}" );
            $status = $res->getStatusCode();
            if ( $status < 200 || $status > 299 ) {
                Logger::error( "Error fetching video info." );
                return null;
            }
            return json_decode( $res->getBody(), true );
        } catch ( \Exception $ex ) {
            Logger::error( "Error deleting video {$videoId}: " . $ex->getMessage() );
            return null;
        }
    }

    public function deleteVideo( $videoId ) {
        try {
            $this->client->delete( "https://video.bunnycdn.com/library/{$this->settings->libraryId}/videos/{$videoId}" );
        } catch ( \Exception $ex ) {
            Logger::error( "Error deleting video {$videoId}: " . $ex->getMessage() );
        }
    }

    public function importVideo( $url, $attachmentId ) {
        Logger::info( "Fetch video: {$url} {$attachmentId}" );
        $res = $this->client->post( "https://video.bunnycdn.com/library/{$this->settings->libraryId}/videos/fetch", [
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/json',
            ],
            RequestOptions::BODY    => json_encode( [
                'url'     => $url,
                'headers' => [
                    'Referer' => get_home_url(),
                ],
                'title'   => basename( parse_url( $url, PHP_URL_PATH ) ),
            ] ),
        ] );
        $status = $res->getStatusCode();
        if ( $status < 200 || $status > 299 ) {
            Logger::error( "Error importing video for upload: " . $status );
            throw new VideoEncoderException("Error creating video.");
        }
        $body = $res->getBody();
        $json = json_decode( $body, true );
        $videoId = arrayPath( $json, 'id', null );
        if ( !$videoId ) {
            throw new VideoEncoderException('Missing video id after import.');
        }
        $video = BunnyVideo::findOrCreate( $videoId );
        $video->attachment_id = $attachmentId;
        $video->save();
        return $video;
    }

    public function uploadVideo( $sourceFile, $attachmentId ) {
        Logger::info( "Upload video: {$sourceFile} {$attachmentId}" );
        $res = $this->client->post( "https://video.bunnycdn.com/library/{$this->settings->libraryId}/videos", [
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/json',
            ],
            RequestOptions::BODY    => json_encode( [
                'title' => basename( $sourceFile ),
            ] ),
        ] );
        $status = $res->getStatusCode();
        if ( $status < 200 || $status > 299 ) {
            Logger::error( "Error creating video for upload: " . $status );
            throw new VideoEncoderException("Error creating video.");
        }
        $body = $res->getBody()->getContents();
        $data = json_decode( $body, true );
        $videoId = arrayPath( $data, 'guid', null );
        if ( empty( $videoId ) ) {
            Logger::error( "Invalid response data: {$body}" );
            throw new VideoEncoderException("Missing guid in create video response.");
        }
        $video = BunnyVideo::findOrCreate( $videoId );
        $video->attachment_id = $attachmentId;
        $video->save();
        try {
            Logger::info( "Performing upload ..." );
            $fileRes = fopen( $sourceFile, 'r' );
            $res = $this->client->put( "https://video.bunnycdn.com/library/{$this->settings->libraryId}/videos/{$videoId}", [
                RequestOptions::HEADERS => [
                    'Content-Type' => 'application/json',
                ],
                RequestOptions::BODY    => $fileRes,
            ] );
            Logger::info( "Upload complete." );
            $status = $res->getStatusCode();
            if ( $status < 200 || $status > 299 ) {
                throw new VideoEncoderException("Error creating video.");
            }
        } catch ( \Exception $ex ) {
            $video->delete();
            throw $ex;
        }
        return $video;
    }

    public function addSubtitles(
        $videoId,
        $languageCode,
        $name,
        $subtitles
    ) {
        $subtitlesBase64 = base64_encode( $subtitles );
        Logger::info( $subtitlesBase64 );
        try {
            $res = $this->client->post( "https://video.bunnycdn.com/library/{$this->settings->libraryId}/videos/{$videoId}/captions/{$languageCode}", [
                RequestOptions::HEADERS => [
                    'Content-Type' => 'application/json',
                ],
                RequestOptions::BODY    => json_encode( [
                    'srclang'      => $languageCode,
                    'captionsFile' => $subtitlesBase64,
                    'label'        => $name,
                ] ),
            ] );
            $status = $res->getStatusCode();
            if ( $status < 200 || $status > 299 ) {
                Logger::error( "Error importing video for upload: " . $status );
                throw new VideoEncoderException("Error creating video.");
            }
        } catch ( \Exception $ex ) {
            Logger::error( "Error uploading subtitles {$videoId}: " . $ex->getMessage() );
            throw $ex;
        }
    }

    public function deleteSubtitles( $videoId, $languageCode ) {
        try {
            $this->client->delete( "https://video.bunnycdn.com/library/{$this->settings->libraryId}/videos/{$videoId}/captions/{$languageCode}" );
        } catch ( \Exception $ex ) {
            Logger::error( "Error uploading subtitles {$videoId}: " . $ex->getMessage() );
        }
    }

}
