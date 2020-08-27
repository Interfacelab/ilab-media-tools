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
namespace MediaCloud\Plugin\Tools\Video\Driver\Mux\Models;

use  MediaCloud\Plugin\Model\Model ;
use  MediaCloud\Plugin\Tools\Video\Driver\Mux\MuxAPI ;
use  MediaCloud\Plugin\Tools\Video\Driver\Mux\MuxToolProSettings ;
use  MediaCloud\Plugin\Tools\Video\Driver\Mux\MuxToolSettings ;
use  MediaCloud\Plugin\Utilities\Logging\Logger ;
use  MediaCloud\Vendor\MuxPhp\Models\CreateTrackRequest ;
use  MediaCloud\Vendor\MuxPhp\Models\CreateTrackResponse ;
use function  MediaCloud\Plugin\Utilities\arrayPath ;
/**
 * Class MuxAsset
 * @package MediaCloud\Plugin\Tools\Video\Driver\Mux\Models
 *
 * @property string $muxId
 * @property string $status
 * @property int $createdAt
 * @property string $title
 * @property int $attachmentId
 * @property float $duration
 * @property float $frameRate
 * @property bool $mp4Support
 * @property int $width
 * @property int $height
 * @property string $aspectRatio
 * @property mixed|null $jsonData
 * @property-read MuxPlaybackID[] $playbackIds
 * @property-read string|null $publicPlaybackID
 * @property-read string|null $securePlaybackID
 * @property-read string|null $filmstripUrl
 * @property-read array|null $muxMetadata
 * @property-read array|null $subtitles
 */
class MuxAsset extends Model
{
    //region Settings
    /** @var MuxToolSettings|MuxToolProSettings */
    protected  $settings ;
    //endregion
    //region Fields
    /**
     * Mux Identifier
     * @var null
     */
    protected  $muxId = null ;
    /**
     * Mux status
     * @var null
     */
    protected  $status = null ;
    /**
     * Epoch time created
     * @var int
     */
    protected  $createdAt = 0 ;
    /**
     * Title of the upload
     * @var string
     */
    protected  $title = null ;
    /**
     * Attachment Id
     * @var int
     */
    protected  $attachmentId = null ;
    /**
     * Duration
     * @var float
     */
    protected  $duration = 0.0 ;
    /**
     * Frame rate
     * @var float
     */
    protected  $frameRate = 0.0 ;
    /**
     * Mux MP4 support
     * @var bool
     */
    protected  $mp4Support = false ;
    /**
     * Width
     * @var int
     */
    protected  $width = 0 ;
    /**
     * Height
     * @var int
     */
    protected  $height = 0 ;
    /**
     * Aspect ratio
     * @var null
     */
    protected  $aspectRatio = null ;
    /**
     * Mux JSON data
     * @var null|mixed
     */
    protected  $jsonData = null ;
    /** @var bool|MuxPlaybackID[]  */
    protected  $_playbackIds = false ;
    /** @var array[]  */
    protected  $_subtitles = false ;
    protected  $modelProperties = array(
        'muxId'        => '%s',
        'status'       => '%s',
        'createdAt'    => '%d',
        'title'        => '%s',
        'attachmentId' => '%d',
        'duration'     => '%f',
        'frameRate'    => '%f',
        'mp4Support'   => '%d',
        'width'        => '%d',
        'height'       => '%d',
        'aspectRatio'  => '%s',
        'jsonData'     => '%s',
    ) ;
    protected  $jsonProperties = array( 'jsonData' ) ;
    //endregion
    //region Static
    public static function table()
    {
        global  $wpdb ;
        return "{$wpdb->base_prefix}mcloud_mux_assets";
    }
    
    //endregion
    //region Constructor
    public function __construct( $data = null )
    {
        $this->settings = MuxToolSettings::instance();
        parent::__construct( $data );
    }
    
    //endregion
    //region Properties
    public function __get( $name )
    {
        
        if ( $name === 'playbackIds' ) {
            if ( $this->_playbackIds === false ) {
                $this->_playbackIds = MuxPlaybackID::playbackIDsForAsset( $this->muxId );
            }
            return $this->_playbackIds;
        } else {
            
            if ( $name === 'publicPlaybackID' ) {
                $pids = $this->playbackIds;
                if ( !empty($pids) ) {
                    foreach ( $pids as $pid ) {
                        if ( $pid->policy === 'public' ) {
                            return $pid->playbackId;
                        }
                    }
                }
                return null;
            } else {
                
                if ( $name === 'securePlaybackID' ) {
                    $pids = $this->playbackIds;
                    if ( !empty($pids) ) {
                        foreach ( $pids as $pid ) {
                            if ( $pid->policy === 'signed' ) {
                                return $pid->playbackId;
                            }
                        }
                    }
                    return null;
                } else {
                    
                    if ( $name === 'filmstripUrl' ) {
                        
                        if ( !empty($this->attachmentId) ) {
                            $fid = get_post_meta( $this->attachmentId, 'mux_filmstrip', true );
                            
                            if ( !empty($fid) ) {
                                $src = wp_get_attachment_image_src( $fid, 'full' );
                                if ( is_array( $src ) ) {
                                    return $src[0];
                                }
                            }
                        
                        }
                        
                        return null;
                    } else {
                        
                        if ( $name === 'muxMetadata' ) {
                            if ( empty($this->settings->envKey) ) {
                                return null;
                            }
                            return [
                                'env_key'           => null,
                                'video_id'          => $this->attachmentId,
                                'video_duration'    => $this->duration,
                                'video_stream_type' => 'on-demand',
                            ];
                        } else {
                            
                            if ( $name === 'subtitles' ) {
                                if ( $this->_subtitles !== false ) {
                                    return $this->_subtitles;
                                }
                                $this->_subtitles = [];
                                $tracks = arrayPath( $this->jsonData, 'tracks', [] );
                                if ( empty($tracks) ) {
                                    return [];
                                }
                                foreach ( $tracks as $track ) {
                                    $type = arrayPath( $track, 'text_type', null );
                                    if ( !empty($type) && $type === 'subtitles' ) {
                                        $this->_subtitles[] = $track;
                                    }
                                }
                                return $this->_subtitles;
                            }
                        
                        }
                    
                    }
                
                }
            
            }
        
        }
        
        return parent::__get( $name );
    }
    
    public function __isset( $name )
    {
        if ( in_array( $name, [
            'playbackIds',
            'publicPlaybackID',
            'securePlaybackID',
            'filmstripUrl',
            'muxMetadata',
            'subtitles'
        ] ) ) {
            return true;
        }
        return parent::__isset( $name );
    }
    
    //endregion
    //region Overrides
    public function willDelete()
    {
        $playbackIds = $this->__get( 'playbackIds' );
        /** @var MuxPlaybackID $playbackId */
        foreach ( $playbackIds as $playbackId ) {
            $playbackId->delete();
        }
        $renditions = MuxRendition::renditionsForAsset( $this->muxId );
        /** @var MuxRendition $rendition */
        foreach ( $renditions as $rendition ) {
            $rendition->delete();
        }
        parent::willDelete();
    }
    
    //endregion
    //region Captions
    public function addCaptions( $language, $captionsURL, $closedCaptions = false )
    {
        $req = new CreateTrackRequest( [
            'url'             => $captionsURL,
            'type'            => 'text',
            'text_type'       => 'subtitles',
            'closed_captions' => !empty($closedCaptions),
            'language_code'   => $language,
        ] );
        try {
            /** @var CreateTrackResponse $response */
            $response = MuxAPI::assetAPI()->createAssetTrack( $this->muxId, $req );
            return $response->getData()->getId() != null;
        } catch ( \Exception $exception ) {
            Logger::error(
                "Error adding captions: " . $exception->getMessage(),
                [],
                __METHOD__,
                __LINE__
            );
            return false;
        }
    }
    
    public function deleteCaptions( $captionsId )
    {
        try {
            Logger::info(
                "Deleting track {$captionsId} for asset {$this->muxId}",
                [],
                __METHOD__,
                __LINE__
            );
            MuxAPI::assetAPI()->deleteAssetTrack( $this->muxId, $captionsId );
            return true;
        } catch ( \Exception $exception ) {
            Logger::error(
                "Error deleting captions: " . $exception->getMessage(),
                [],
                __METHOD__,
                __LINE__
            );
            return false;
        }
    }
    
    //endregion
    //region Video URLs
    public function hasRendition( $qualityLevel )
    {
        $rendition = MuxRendition::rendition( $this->muxId, $qualityLevel );
        return !empty($rendition);
    }
    
    public function secureVideoUrl()
    {
        return null;
    }
    
    public function publicVideoUrl()
    {
        $pid = $this->__get( 'publicPlaybackID' );
        if ( empty($pid) ) {
            return null;
        }
        return "https://stream.mux.com/{$pid}.m3u8";
    }
    
    public function videoUrl( $preferSecure = true )
    {
        $url = ( $preferSecure ? $this->secureVideoUrl() : $this->publicVideoUrl() );
        if ( !empty($url) ) {
            return $url;
        }
        return ( $preferSecure ? $this->publicVideoUrl() : $this->secureVideoUrl() );
    }
    
    public function secureRenditionUrl( $qualityLevel )
    {
        return null;
    }
    
    public function publicRenditionUrl( $qualityLevel )
    {
        $pid = $this->__get( 'publicPlaybackID' );
        if ( empty($pid) ) {
            return null;
        }
        return "https://stream.mux.com/{$pid}/{$qualityLevel}";
    }
    
    public function renditionUrl( $qualityLevel, $preferSecure = true )
    {
        $url = ( $preferSecure ? $this->secureRenditionUrl( $qualityLevel ) : $this->publicRenditionUrl( $qualityLevel ) );
        if ( !empty($url) ) {
            return $url;
        }
        return ( $preferSecure ? $this->publicRenditionUrl( $qualityLevel ) : $this->secureRenditionUrl( $qualityLevel ) );
    }
    
    //endregion
    //region Thumbnail URLs
    public function secureThumbnailUrl(
        $width = null,
        $height = null,
        $time = null,
        $cropMode = null
    )
    {
        return null;
    }
    
    public function publicThumbnailUrl(
        $width = null,
        $height = null,
        $time = null,
        $cropMode = null
    )
    {
        $pid = $this->__get( 'publicPlaybackID' );
        if ( empty($pid) ) {
            return null;
        }
        $url = "https://image.mux.com/{$pid}/thumbnail.jpg";
        $args = [];
        if ( !empty($width) ) {
            $args['width'] = $width;
        }
        if ( !empty($height) ) {
            $args['height'] = $height;
        }
        if ( !empty($time) ) {
            $args['time'] = $time;
        }
        if ( !empty($cropMode) ) {
            $args['fit_mode'] = $cropMode;
        }
        
        if ( count( $args ) > 0 ) {
            $queryString = '?';
            foreach ( $args as $key => $val ) {
                $queryString .= "{$key}={$val}&";
            }
            $url .= rtrim( $queryString, '&' );
        }
        
        return $url;
    }
    
    public function thumbnailUrl(
        $preferSecure = true,
        $width = null,
        $height = null,
        $time = null,
        $cropMode = null
    )
    {
        $url = ( $preferSecure ? $this->secureThumbnailUrl( $width, $height, $time ) : $this->publicThumbnailUrl(
            $width,
            $height,
            $time,
            $cropMode
        ) );
        if ( !empty($url) ) {
            return $url;
        }
        return ( $preferSecure ? $this->publicThumbnailUrl( $width, $height, $time ) : $this->secureThumbnailUrl(
            $width,
            $height,
            $time,
            $cropMode
        ) );
    }
    
    //endregion
    //region GIF URLs
    public function secureGIFUrl()
    {
    }
    
    public function publicGIFUrl()
    {
        $pid = $this->__get( 'publicPlaybackID' );
        if ( empty($pid) ) {
            return null;
        }
        return "https://image.mux.com/{$pid}/animated.gif";
    }
    
    public function gifUrl( $preferSecure = true )
    {
        $url = ( $preferSecure ? $this->secureGIFUrl() : $this->publicGIFUrl() );
        if ( !empty($url) ) {
            return $url;
        }
        return ( $preferSecure ? $this->publicGIFUrl() : $this->secureGIFUrl() );
    }
    
    //endregion
    //region Queries
    /**
     * Returns a task with the given ID
     *
     * @param $id
     *
     * @return MuxAsset|null
     * @throws \Exception
     */
    public static function asset( $muxId )
    {
        global  $wpdb ;
        $table = static::table();
        $rows = $wpdb->get_results( $wpdb->prepare( "select * from {$table} where muxId = %s", $muxId ) );
        foreach ( $rows as $row ) {
            return new static( $row );
        }
        return null;
    }
    
    /**
     * Returns a task with the given ID
     *
     * @param $attachmentId
     *
     * @return MuxAsset|null
     * @throws \Exception
     */
    public static function assetForAttachment( $attachmentId )
    {
        global  $wpdb ;
        $table = static::table();
        $rows = $wpdb->get_results( $wpdb->prepare( "select * from {$table} where attachmentId = %s", $attachmentId ) );
        foreach ( $rows as $row ) {
            return new static( $row );
        }
        return null;
    }
    
    /**
     * Returns a task with the given ID, if not found, creates a new one.
     *
     * @param $id
     *
     * @return MuxAsset|null
     * @throws \Exception
     */
    public static function findOrCreate( $muxId )
    {
        $asset = static::asset( $muxId );
        if ( !empty($asset) ) {
            return $asset;
        }
        $asset = new MuxAsset();
        $asset->muxId = $muxId;
        return $asset;
    }

}