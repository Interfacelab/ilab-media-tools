<?php

// Copyright (c) 2016 Interfacelab LLC. All rights reserved.
//
// Released under the GPLv3 license
// http://www.gnu.org/licenses/gpl-3.0.html
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************
namespace MediaCloud\Plugin\Tools\Video\Driver\Mux;

use  MediaCloud\Plugin\Tools\Storage\StorageToolSettings ;
use  MediaCloud\Plugin\Tools\Video\Driver\Mux\Models\MuxAsset ;
use  MediaCloud\Plugin\Tools\Video\Driver\Mux\Models\MuxPlaybackID ;
use  MediaCloud\Plugin\Tools\Video\Driver\Mux\Models\MuxRendition ;
use  MediaCloud\Plugin\Tools\Storage\StorageTool ;
use  MediaCloud\Plugin\Tools\ToolsManager ;
use  MediaCloud\Plugin\Utilities\Logging\Logger ;
use  MediaCloud\Plugin\Utilities\Prefixer ;
use  MediaCloud\Vendor\MuxPhp\Models\CreateAssetRequest ;
use  MediaCloud\Vendor\MuxPhp\Models\InputSettings ;
use  MediaCloud\Vendor\MuxPhp\Models\PlaybackPolicy ;
use function  MediaCloud\Plugin\Utilities\arrayPath ;
use function  MediaCloud\Plugin\Utilities\gen_uuid ;
class MuxHooks
{
    /** @var MuxToolSettings|MuxToolProSettings  */
    private  $settings ;
    public function __construct()
    {
        $this->settings = MuxToolSettings::instance();
        
        if ( ToolsManager::instance()->toolEnabled( 'storage' ) ) {
            
            if ( $this->settings->processUploads ) {
                add_action(
                    'media-cloud/storage/uploaded-attachment',
                    [ $this, 'handleUpload' ],
                    1000,
                    3
                );
                add_action(
                    'media-cloud/storage/direct-uploaded-attachment',
                    [ $this, 'handleDirectUpload' ],
                    1000,
                    2
                );
            }
        
        } else {
            add_filter(
                'wp_update_attachment_metadata',
                [ $this, 'handleUpdateAttachmentMetadata' ],
                1000,
                2
            );
        }
        
        add_filter( 'template_include', [ $this, 'handleWebhook' ] );
    }
    
    //endregion
    //region Asset Events
    /**
     * Generates a string timecode from a float duration
     *
     * @param float $duration
     * @return string
     */
    private function timecode( $duration )
    {
        $hours = floor( $duration / (60.0 * 60.0) );
        $duration -= $hours * 60 * 60;
        $minutes = floor( $duration / 60.0 );
        $duration -= $minutes * 60;
        $seconds = round( $duration );
        
        if ( $hours > 0 ) {
            return sprintf(
                '%02d:%02d:%02d',
                $hours,
                $minutes,
                $seconds
            );
        } else {
            return sprintf( '%02d:%02d', $minutes, $seconds );
        }
    
    }
    
    protected function updateAttachmentMeta( $asset )
    {
        
        if ( empty($asset->attachmentId) ) {
            Logger::error(
                "Mux: Missing attachment id, cannot update meta.",
                [],
                __METHOD__,
                __LINE__
            );
            return;
        }
        
        Logger::info(
            "Mux: Updating meta for attachment {$asset->attachmentId}",
            [],
            __METHOD__,
            __LINE__
        );
        $meta = get_post_meta( $asset->attachmentId, '_wp_attachment_metadata', true );
        
        if ( empty($meta) ) {
            Logger::error(
                "Mux: Attachment {$asset->attachmentId} meta is missing or empty, cannot update.",
                [],
                __METHOD__,
                __LINE__
            );
            return;
        }
        
        $meta['mux'] = [
            'muxId'  => $asset->muxId,
            'id'     => $asset->id(),
            'status' => $asset->status,
        ];
        update_post_meta( $asset->attachmentId, '_wp_attachment_metadata', $meta );
        Logger::info(
            "Mux: Updated meta for attachment {$asset->attachmentId}",
            [],
            __METHOD__,
            __LINE__
        );
    }
    
    /**
     * @param MuxAsset $asset
     * @throws \Exception
     */
    protected function createAttachmentForAsset( $asset )
    {
        Logger::info(
            "Mux: Creating attachment for asset",
            [],
            __METHOD__,
            __LINE__
        );
        
        if ( MuxAPI::assetAPI() === null ) {
            Logger::error(
                "Mux: Unable to create API client",
                [],
                __METHOD__,
                __LINE__
            );
            return;
        }
        
        try {
            $result = MuxAPI::assetAPI()->getAssetInputInfo( $asset->muxId );
            
            if ( $result === null ) {
                Logger::error(
                    "Mux: Could not get asset input info for {$asset->muxId}",
                    [],
                    __METHOD__,
                    __LINE__
                );
                return;
            }
        
        } catch ( \Exception $ex ) {
            Logger::error(
                "Mux: Mux error fetching input info: " . $ex->getMessage(),
                [],
                __METHOD__,
                __LINE__
            );
            return;
        }
        $inputInfos = $result->getData();
        
        if ( empty($inputInfos) ) {
            Logger::error(
                "Mux: Could not find asset inputs for {$asset->muxId}",
                [],
                __METHOD__,
                __LINE__
            );
            return;
        }
        
        $inputInfo = $inputInfos[0];
        $url = $inputInfo->getSettings()->getUrl();
        $meta = [
            'mime_type'         => 'video/quicktime',
            'fileformat'        => 'mp4',
            'dataformat'        => 'quicktime',
            'created_timestamp' => $asset->createdAt,
            'length'            => $asset->duration,
            'length_formatted'  => $this->timecode( $asset->duration ),
            'width'             => $asset->width,
            'height'            => $asset->height,
        ];
        $path = parse_url( $url, PHP_URL_PATH );
        if ( strpos( $path, '?' ) !== false ) {
            $path = substr( $path, 0, strpos( $path, '?' ) );
        }
        $filename = pathinfo( $path, PATHINFO_FILENAME );
        $attachmentId = wp_insert_attachment( [
            'post_mime_type' => 'video/mp4',
            'post_name'      => $filename,
            'post_title'     => $filename,
            'guid'           => $asset->muxId,
            'post_content'   => '',
            'post_status'    => 'inherit',
        ] );
        Logger::info(
            "Mux: Created attachment {$attachmentId}",
            [],
            __METHOD__,
            __LINE__
        );
        $meta['mux'] = [
            'muxId'  => $asset->muxId,
            'id'     => $asset->id(),
            'status' => $asset->status,
        ];
        update_post_meta( $attachmentId, '_wp_attachment_metadata', $meta );
        update_post_meta( $attachmentId, '_wp_attached_file', $filename );
        if ( empty($asset->title) ) {
            $asset->title = $filename;
        }
        $asset->attachmentId = $attachmentId;
        $asset->save();
        $this->assignThumbnailForAsset( $asset );
    }
    
    /**
     * @param MuxAsset $asset
     */
    protected function assignThumbnailForAsset( $asset )
    {
        if ( empty($asset->attachmentId) ) {
            return;
        }
        
        if ( has_post_thumbnail( $asset->attachmentId ) ) {
            Logger::warning(
                "Mux: Thumbnail already exists",
                [],
                __METHOD__,
                __LINE__
            );
            $this->generateFilmstripForAttachment( $asset );
            return;
        }
        
        $url = $asset->thumbnailUrl();
        
        if ( empty($url) ) {
            Logger::error(
                "Mux: Could not generate URL for thumbnail?",
                [],
                __METHOD__,
                __LINE__
            );
            return;
        }
        
        $uploadDirInfo = wp_get_upload_dir();
        
        if ( ToolsManager::instance()->toolEnabled( 'storage' ) ) {
            Prefixer::setType( 'image/jpeg' );
            $prefix = StorageToolSettings::prefix();
            $uploadDir = trailingslashit( $uploadDirInfo['basedir'] ) . $prefix;
            @mkdir( $uploadDir, 0777, true );
        } else {
            $uploadDir = trailingslashit( $uploadDirInfo['basedir'] );
        }
        
        $filePath = trailingslashit( $uploadDir ) . $asset->attachmentId . '-' . sanitize_title( $asset->title ) . '-thumb.jpg';
        file_put_contents( $filePath, ilab_file_get_contents( $url ) );
        
        if ( !file_exists( $filePath ) ) {
            Logger::error(
                "Mux: Could not download image {$url}.",
                [],
                __METHOD__,
                __LINE__
            );
            return;
        }
        
        $thumbId = wp_insert_attachment( [
            'post_mime_type' => 'image/jpeg',
            'post_title'     => $asset->title . ' Poster',
            'post_content'   => '',
            'post_status'    => 'inherit',
        ] );
        Logger::info(
            "Mux: Created thumbnail attachment {$thumbId}.",
            [],
            __METHOD__,
            __LINE__
        );
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $thumbAttachmentMeta = wp_generate_attachment_metadata( $thumbId, $filePath );
        update_post_meta( $thumbId, '_wp_attached_file', $thumbAttachmentMeta['file'] );
        update_post_meta( $asset->attachmentId, '_thumbnail_id', $thumbId );
        wp_update_attachment_metadata( $thumbId, $thumbAttachmentMeta );
        $this->generateFilmstripForAttachment( $asset );
    }
    
    /**
     * @param MuxAsset $asset
     *
     * @throws \Freemius_Exception
     */
    protected function generateFilmstripForAttachment( $asset )
    {
        Logger::info(
            'Mux: generateFilmstripForAttachment',
            [],
            __METHOD__,
            __LINE__
        );
        Logger::warning(
            'Mux: generateFilmstripForAttachment could not be run, not premium',
            [],
            __METHOD__,
            __LINE__
        );
    }
    
    public function handleStaticRenditionsReady( $jsonData )
    {
        $muxId = arrayPath( $jsonData, 'data/id', null );
        Logger::info(
            "Mux: Asset ready {$muxId}",
            [],
            __METHOD__,
            __LINE__
        );
        /** @var MuxAsset $asset */
        $asset = MuxAsset::findOrCreate( $muxId );
        
        if ( $asset === null ) {
            Logger::error(
                'Mux: Asset could not be created.',
                [],
                __METHOD__,
                __LINE__
            );
            return;
        }
        
        $renditions = arrayPath( $jsonData, 'data/static_renditions/files', [] );
        Logger::info(
            "Mux: Found " . count( $renditions ) . " renditions for {$muxId}",
            [],
            __METHOD__,
            __LINE__
        );
        foreach ( $renditions as $renditionData ) {
            $renditionName = arrayPath( $renditionData, 'name', null );
            
            if ( !empty($renditionName) ) {
                $rendition = MuxRendition::findOrCreate( $muxId, $renditionName );
                $rendition->width = arrayPath( $renditionData, 'width', 0 );
                $rendition->height = arrayPath( $renditionData, 'height', 0 );
                $rendition->bitrate = arrayPath( $renditionData, 'bitrate', 0 );
                $rendition->filesize = arrayPath( $renditionData, 'filesize', 0 );
                $rendition->save();
                Logger::info(
                    "Mux: Added {$renditionName} rendition for {$muxId}",
                    [],
                    __METHOD__,
                    __LINE__
                );
            }
        
        }
    }
    
    public function handleAssetReady( $jsonData )
    {
        $muxId = arrayPath( $jsonData, 'data/id', null );
        Logger::info(
            "Mux: Asset ready {$muxId}",
            [],
            __METHOD__,
            __LINE__
        );
        /** @var MuxAsset $asset */
        $asset = MuxAsset::findOrCreate( $muxId );
        
        if ( $asset === null ) {
            Logger::error(
                'Mux: Asset could not be created.',
                [],
                __METHOD__,
                __LINE__
            );
            return;
        }
        
        $asset->createdAt = arrayPath( $jsonData, 'data/created_at', time() );
        $asset->status = arrayPath( $jsonData, 'data/status', $asset->status );
        $asset->duration = arrayPath( $jsonData, 'data/duration', 0.0 );
        $asset->frameRate = arrayPath( $jsonData, 'data/max_stored_frame_rate', 0.0 );
        $asset->aspectRatio = arrayPath( $jsonData, 'data/aspect_ratio', null );
        $asset->jsonData = arrayPath( $jsonData, 'data', null );
        $mp4Support = arrayPath( $jsonData, 'data/mp4_support', 'none' );
        $asset->mp4Support = $mp4Support !== 'none';
        $tracks = arrayPath( $jsonData, 'data/tracks', [] );
        foreach ( $tracks as $track ) {
            
            if ( $track['type'] === 'video' ) {
                $asset->width = arrayPath( $track, 'max_width', 0 );
                $asset->height = arrayPath( $track, 'max_height', 0 );
            }
        
        }
        $asset->save();
        Logger::info(
            'Mux: Asset saved to database.',
            [],
            __METHOD__,
            __LINE__
        );
        $playbackIds = arrayPath( $jsonData, 'data/playback_ids', [] );
        foreach ( $playbackIds as $playbackId ) {
            $pid = $playbackId['id'];
            /** @var MuxPlaybackID $playback */
            $playback = MuxPlaybackID::findOrCreate( $muxId, $pid );
            $playback->policy = $playbackId['policy'];
            $playback->save();
        }
        $renditions = arrayPath( $jsonData, 'data/static_renditions/files', [] );
        foreach ( $renditions as $renditionData ) {
            $renditionName = arrayPath( $renditionData, 'name', null );
            
            if ( !empty($renditionName) ) {
                $rendition = MuxRendition::findOrCreate( $muxId, $renditionName );
                $rendition->width = arrayPath( $renditionData, 'width', 0 );
                $rendition->height = arrayPath( $renditionData, 'height', 0 );
                $rendition->bitrate = arrayPath( $renditionData, 'bitrate', 0 );
                $rendition->filesize = arrayPath( $renditionData, 'filesize', 0 );
                $rendition->save();
            }
        
        }
        
        if ( empty($asset->attachmentId) ) {
            $this->createAttachmentForAsset( $asset );
        } else {
            $this->updateAttachmentMeta( $asset );
            $this->assignThumbnailForAsset( $asset );
        }
    
    }
    
    public function handleAssetUpdated( $jsonData )
    {
        $muxId = arrayPath( $jsonData, 'data/id', null );
        Logger::info(
            "Mux: Asset updated {$muxId}",
            [],
            __METHOD__,
            __LINE__
        );
        /** @var MuxAsset $asset */
        $asset = MuxAsset::asset( $muxId );
        if ( empty($asset) ) {
            return;
        }
        $asset->jsonData = arrayPath( $jsonData, 'data', null );
        $asset->save();
        Logger::info(
            'Mux: Asset update saved to database.',
            [],
            __METHOD__,
            __LINE__
        );
    }
    
    public function handleAssetDeleted( $jsonData )
    {
        $muxId = arrayPath( $jsonData, 'data/id', null );
        /** @var MuxAsset $asset */
        $asset = MuxAsset::asset( $muxId );
        if ( empty($asset) ) {
            return;
        }
        $asset->delete();
    }
    
    public function handleAssetErrored( $jsonData )
    {
    }
    
    //endregion
    //region Webhook
    private function saveDebugOutput( $muxId, $data )
    {
        $uploadDirInfo = wp_upload_dir();
        $debugPath = trailingslashit( $uploadDirInfo['basedir'] ) . 'webhook/mux/';
        if ( !file_exists( $debugPath ) ) {
            mkdir( $debugPath, 0777, true );
        }
        $path = $debugPath . time() . '-' . $muxId . '.json';
        file_put_contents( $path, json_encode( $data, JSON_PRETTY_PRINT ) );
    }
    
    public function handleWebhook( $template )
    {
        
        if ( strpos( $_SERVER['REQUEST_URI'], '/__mux/webhook' ) === 0 ) {
            
            if ( !isset( $_SERVER['HTTP_MUX_SIGNATURE'] ) ) {
                Logger::error(
                    "Mux: Missing Mux Signature",
                    [],
                    __METHOD__,
                    __LINE__
                );
                wp_send_json( [
                    'status' => 'error',
                ], 400 );
            }
            
            $body = file_get_contents( 'php://input' );
            
            if ( !MuxAPI::validateSignature( $_SERVER['HTTP_MUX_SIGNATURE'], $body, $this->settings->webhookSecret ) ) {
                Logger::error(
                    "Mux: Invalid Mux Signature",
                    [],
                    __METHOD__,
                    __LINE__
                );
                wp_send_json( [
                    'status' => 'invalid signature',
                ], 400 );
            }
            
            $data = json_decode( $body, true );
            $type = arrayPath( $data, 'type', null );
            if ( empty($type) ) {
                Logger::error(
                    "Mux: Webhook missing type.  Exiting.",
                    [],
                    __METHOD__,
                    __LINE__
                );
            }
            
            if ( defined( 'MEDIACLOUD_DEV_MODE' ) && !empty(constant( 'MEDIACLOUD_DEV_MODE' )) ) {
                $muxId = arrayPath( $data, 'data/id', null );
                $this->saveDebugOutput( $muxId, $data );
            }
            
            Logger::info(
                "Mux: Webhook event: {$type}",
                [],
                __METHOD__,
                __LINE__
            );
            
            if ( $type === 'video.asset.ready' ) {
                $this->handleAssetReady( $data );
            } else {
                
                if ( $type === 'video.asset.static_renditions.ready' ) {
                    $this->handleStaticRenditionsReady( $data );
                } else {
                    
                    if ( $type === 'video.asset.errored' ) {
                        $this->handleAssetErrored( $data );
                    } else {
                        
                        if ( $type === 'video.asset.deleted' ) {
                            $this->handleAssetDeleted( $data );
                        } else {
                            if ( $type === 'video.asset.updated' ) {
                                $this->handleAssetUpdated( $data );
                            }
                        }
                    
                    }
                
                }
            
            }
            
            wp_send_json( [
                'status' => 'ok',
            ], 200 );
        }
        
        return $template;
    }
    
    //endregion
    //region Upload Hooks
    public function importUrl( $attachmentId, $meta, $doUpdate = true )
    {
        if ( MuxAPI::assetAPI() === null ) {
            return $meta;
        }
        
        if ( ToolsManager::instance()->toolEnabled( 'storage' ) ) {
            /** @var StorageTool $storageTool */
            $storageTool = ToolsManager::instance()->tools['storage'];
            $url = $storageTool->client()->presignedUrl( $meta['s3']['key'], 30 );
        } else {
            $url = wp_get_attachment_url( $attachmentId );
            if ( defined( 'MEDIACLOUD_DEV_MODE' ) && defined( 'MEDIACLOUD_VIDEO_SERVER' ) && !empty(constant( 'MEDIACLOUD_VIDEO_SERVER' )) ) {
                // DEBUG ONLY
                $url = str_replace( home_url(), constant( 'MEDIACLOUD_VIDEO_SERVER' ), $url );
            }
        }
        
        $input = new InputSettings( [
            'url' => $url,
        ] );
        $policy = PlaybackPolicy::PUBLIC_PLAYBACK_POLICY;
        $mp4Support = 'none';
        $req = new CreateAssetRequest( [
            'input'            => $input,
            'playback_policy'  => $policy,
            'mp4_support'      => $mp4Support,
            'normalize_audio'  => ( !empty($this->settings->normalizeAudio) ? true : false ),
            'per_title_encode' => ( !empty($this->settings->perTitleEncoding) ? true : false ),
            'test'             => ( !empty($this->settings->testMode) ? true : false ),
        ] );
        try {
            $result = MuxAPI::assetAPI()->createAsset( $req );
        } catch ( \Exception $exception ) {
            Logger::error(
                "Error creating mux asset: " . $exception->getMessage(),
                [],
                __METHOD__,
                __LINE__
            );
            $meta['mux'] = [
                'error' => true,
            ];
            if ( !empty($doUpdate) ) {
                update_post_meta( $attachmentId, '_wp_attachment_metadata', $meta );
            }
            return $meta;
        }
        $assetData = $result->getData();
        if ( $assetData === null ) {
            return $meta;
        }
        /** @var MuxAsset $asset */
        $asset = MuxAsset::findOrCreate( $assetData->getId() );
        $asset->save();
        $asset->createdAt = time();
        $asset->status = $assetData->getStatus();
        $asset->title = get_post_field( 'post_title', $attachmentId );
        $asset->attachmentId = $attachmentId;
        $asset->save();
        $meta['mux'] = [
            'muxId'  => $asset->muxId,
            'id'     => $asset->id(),
            'status' => $asset->status,
        ];
        if ( !empty($doUpdate) ) {
            update_post_meta( $attachmentId, '_wp_attachment_metadata', $meta );
        }
        return $meta;
    }
    
    public function handleDirectUpload( $attachmentId, $meta )
    {
        $type = arrayPath( $meta, 'type', null );
        
        if ( empty($type) ) {
            $type = arrayPath( $meta, 'mime_type', null );
            
            if ( empty($type) ) {
                $type = arrayPath( $meta, 's3/mime-type', null );
                if ( empty($type) ) {
                    return;
                }
            }
        
        }
        
        if ( strpos( $type, 'video' ) !== 0 || !isset( $meta['s3'] ) ) {
            return;
        }
        $this->importUrl( $attachmentId, $meta );
    }
    
    public function handleUpdateAttachmentMetadata( $data, $id )
    {
        if ( isset( $data['mime_type'] ) && strpos( $data['mime_type'], 'video/' ) === 0 ) {
            return $this->importUrl( $id, $data, false );
        }
        return $data;
    }
    
    public function handleUpload( $attachmentId, $file, $meta )
    {
        $this->handleDirectUpload( $attachmentId, $meta );
    }

}