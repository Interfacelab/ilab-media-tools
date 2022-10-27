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
use  MediaCloud\Plugin\Tasks\TaskSchedule ;
use  MediaCloud\Plugin\Tools\Storage\StorageConstants ;
use  MediaCloud\Plugin\Tools\Storage\StorageTool ;
use  MediaCloud\Plugin\Tools\Storage\StorageToolSettings ;
use  MediaCloud\Plugin\Tools\ToolsManager ;
use  MediaCloud\Plugin\Tools\Video\Driver\Mux\MuxAPI ;
use  MediaCloud\Plugin\Tools\Video\Driver\Mux\MuxToolProSettings ;
use  MediaCloud\Plugin\Tools\Video\Driver\Mux\MuxToolSettings ;
use  MediaCloud\Plugin\Utilities\Logging\Logger ;
use  MediaCloud\Plugin\Utilities\Prefixer ;
use  MediaCloud\Vendor\MuxPhp\Models\CreateTrackRequest ;
use  MediaCloud\Vendor\MuxPhp\Models\CreateTrackResponse ;
use function  MediaCloud\Plugin\Utilities\arrayPath ;
use  MediaCloud\Vendor\Chrisyue\PhpM3u8\Facade\ParserFacade ;
use  MediaCloud\Vendor\Chrisyue\PhpM3u8\Stream\TextStream ;
use function  MediaCloud\Plugin\Utilities\gen_uuid ;
use function  MediaCloud\Plugin\Utilities\ilab_set_time_limit ;
use function  MediaCloud\Plugin\Utilities\ilab_stream_download ;
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
 * @property int $isTransferred
 * @property int $isDeleted
 * @property mixed|null $transferData
 * @property mixed|null $relatedFiles
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
     * Has the video been transferred to local or cloud storage?
     * @var int
     */
    protected  $isTransferred = 0 ;
    /**
     * Has the video been deleted from Mux?
     * @var int
     */
    protected  $isDeleted = 0 ;
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
    /**
     * Mux JSON data
     * @var null|mixed
     */
    protected  $relatedFiles = null ;
    /**
     * Mux transfer data
     * @var null|mixed
     */
    protected  $transferData = null ;
    /** @var bool|MuxPlaybackID[]  */
    protected  $_playbackIds = false ;
    /** @var array[]  */
    protected  $_subtitles = false ;
    protected  $modelProperties = array(
        'muxId'         => '%s',
        'status'        => '%s',
        'createdAt'     => '%d',
        'title'         => '%s',
        'attachmentId'  => '%d',
        'duration'      => '%f',
        'frameRate'     => '%f',
        'mp4Support'    => '%d',
        'width'         => '%d',
        'height'        => '%d',
        'aspectRatio'   => '%s',
        'jsonData'      => '%s',
        'transferData'  => '%s',
        'isTransferred' => '%d',
        'isDeleted'     => '%d',
        'relatedFiles'  => '%s',
    ) ;
    protected  $jsonProperties = array( 'jsonData', 'transferData', 'relatedFiles' ) ;
    //endregion
    //region Static
    public static  $subtitleLanguages = array(
        'af'         => 'Afrikaans',
        'af-ZA'      => 'Afrikaans - South Africa',
        'ar'         => 'Arabic',
        'ar-AE'      => 'Arabic - United Arab Emirates',
        'ar-BH'      => 'Arabic - Bahrain',
        'ar-DZ'      => 'Arabic - Algeria',
        'ar-EG'      => 'Arabic - Egypt',
        'ar-IQ'      => 'Arabic - Iraq',
        'ar-JO'      => 'Arabic - Jordan',
        'ar-KW'      => 'Arabic - Kuwait',
        'ar-LB'      => 'Arabic - Lebanon',
        'ar-LY'      => 'Arabic - Libya',
        'ar-MA'      => 'Arabic - Morocco',
        'ar-OM'      => 'Arabic - Oman',
        'ar-QA'      => 'Arabic - Qatar',
        'ar-SA'      => 'Arabic - Saudi Arabia',
        'ar-SY'      => 'Arabic - Syria',
        'ar-TN'      => 'Arabic - Tunisia',
        'ar-YE'      => 'Arabic - Yemen',
        'az'         => 'Azeri',
        'az-AZ'      => 'Cyrl Azeri (Cyrillic) - Azerbaijan',
        'az-AZ-Latn' => 'Azeri (Latin) - Azerbaijan',
        'be'         => 'Belarusian',
        'be-BY'      => 'Belarusian - Belarus',
        'bg'         => 'Bulgarian',
        'bg-BG'      => 'Bulgarian - Bulgaria',
        'ca'         => 'Catalan',
        'ca-ES'      => 'Catalan - Catalan',
        'cs'         => 'Czech',
        'cs-CZ'      => 'Czech - Czech Republic',
        'da'         => 'Danish',
        'da-DK'      => 'Danish - Denmark',
        'de'         => 'German',
        'de-AT'      => 'German - Austria',
        'de-CH'      => 'German - Switzerland',
        'de-DE'      => 'German - Germany',
        'de-LI'      => 'German - Liechtenstein',
        'de-LU'      => 'German - Luxembourg',
        'div'        => 'Dhivehi',
        'div-MV'     => 'Dhivehi - Maldives',
        'el'         => 'Greek',
        'el-GR'      => 'Greek - Greece',
        'en'         => 'English',
        'en-AU'      => 'English - Australia',
        'en-BZ'      => 'English - Belize',
        'en-CA'      => 'English - Canada',
        'en-CB'      => 'English - Caribbean',
        'en-GB'      => 'English - United Kingdom',
        'en-IE'      => 'English - Ireland',
        'en-JM'      => 'English - Jamaica',
        'en-NZ'      => 'English - New Zealand',
        'en-PH'      => 'English - Philippines',
        'en-TT'      => 'English - Trinidad and Tobago',
        'en-US'      => 'English - United States',
        'en-ZA'      => 'English - South Africa',
        'en-ZW'      => 'English - Zimbabwe',
        'es'         => 'Spanish',
        'es-AR'      => 'Spanish - Argentina',
        'es-BO'      => 'Spanish - Bolivia',
        'es-CLe'     => 'Spanish - Chile',
        'es-CO'      => 'Spanish - Colombia',
        'es-CR'      => 'Spanish - Costa Rica',
        'es-DO'      => 'Spanish - Dominican Republic',
        'es-EC'      => 'Spanish - Ecuador',
        'es-ES'      => 'Spanish - Spain',
        'es-GT'      => 'Spanish - Guatemala',
        'es-HN'      => 'Spanish - Honduras',
        'es-MX'      => 'Spanish - Mexico',
        'es-NI'      => 'Spanish - Nicaragua',
        'es-PA'      => 'Spanish - Panama',
        'es-PE'      => 'Spanish - Peru',
        'es-PR'      => 'Spanish - Puerto Rico',
        'es-PY'      => 'Spanish - Paraguay',
        'es-SV'      => 'Spanish - El Salvador',
        'es-UY'      => 'Spanish - Uruguay',
        'es-VE'      => 'Spanish - Venezuela',
        'et'         => 'Estonian',
        'et-EE'      => 'Estonian - Estonia',
        'eu'         => 'Basque',
        'eu-ES'      => 'Basque - Basque',
        'fa'         => 'Farsi',
        'fa-IR'      => 'Farsi - Iran',
        'fi'         => 'Finnish',
        'fi-FI'      => 'Finnish - Finland',
        'fo'         => 'Faroese',
        'fo-FO'      => 'Faroese - Faroe Islands',
        'fr'         => 'French',
        'fr-BE'      => 'French - Belgium',
        'fr-CA'      => 'French - Canada',
        'fr-CH'      => 'French - Switzerland',
        'fr-FR'      => 'French - France',
        'fr-LU'      => 'French - Luxembourg',
        'fr-MC'      => 'French - Monaco',
        'gl'         => 'Galician',
        'gl-ES'      => 'Galician - Galician',
        'gu'         => 'Gujarati',
        'gu-IN'      => 'Gujarati - India',
        'he'         => 'Hebrew',
        'he-IL'      => 'Hebrew - Israel',
        'hi'         => 'Hindi',
        'hi-IN'      => 'Hindi - India',
        'hr'         => 'Croatian',
        'hr-HR'      => 'Croatian - Croatia',
        'hu'         => 'Hungarian',
        'hu-HU'      => 'Hungarian - Hungary',
        'hy'         => 'Armenian',
        'hy-AM'      => 'Armenian - Armenia',
        'id'         => 'Indonesian',
        'id-ID'      => 'Indonesian - Indonesia',
        'is'         => 'Icelandic',
        'is-IS'      => 'Icelandic - Iceland',
        'it'         => 'Italian',
        'it-CH'      => 'Italian	Italian - Switzerland',
        'it-IT'      => 'Italian - Italy',
        'ja'         => 'Japanese',
        'ja-JP'      => 'Japanese - Japan',
        'ka'         => 'Georgian',
        'ka-GE'      => 'Georgian - Georgia',
        'kk'         => 'Kazakh',
        'kk-KZ'      => 'Kazakh - Kazakhstan',
        'kn'         => 'Kannada',
        'kn-IN'      => 'Kannada - India',
        'ko'         => 'Korean',
        'kok'        => 'Konkani',
        'kok-IN'     => 'Konkani - India',
        'ko-KR'      => 'Korean - Korea',
        'ky'         => 'Kyrgyz',
        'ky-KG'      => 'Kyrgyz - Kyrgyzstan',
        'lt'         => 'Lithuanian',
        'lt-LT'      => 'Lithuanian - Lithuania',
        'lv'         => 'Latvian',
        'lv-LV'      => 'Latvian - Latvia',
        'mk'         => 'Macedonian',
        'mk-MK'      => 'Macedonian - Former Yugoslav Republic of Macedonia',
        'mn'         => 'Mongolian',
        'mn-MN'      => 'Mongolian - Mongolia',
        'mr'         => 'Marathi',
        'mr-IN'      => 'Marathi - India',
        'ms'         => 'Malay',
        'ms-BN'      => 'Malay - Brunei',
        'ms-MY'      => 'Malay - Malaysia',
        'nb-NO'      => 'Norwegian (Bokm?l) - Norway',
        'nl'         => 'Dutch',
        'nl-BE'      => 'Dutch - Belgium',
        'nl-NL'      => 'Dutch - The Netherlands',
        'nn-NO'      => 'Norwegian (Nynorsk) - Norway',
        'no'         => 'Norwegian',
        'pa'         => 'Punjabi',
        'pa-IN'      => 'Punjabi - India',
        'pl'         => 'Polish',
        'pl-PL'      => 'Polish - Poland',
        'pt'         => 'Portuguese',
        'pt-BR'      => 'Portuguese - Brazil',
        'pt-PT'      => 'Portuguese - Portugal',
        'ro'         => 'Romanian',
        'ro-RO'      => 'Romanian - Romania',
        'ru'         => 'Russian',
        'ru-RU'      => 'Russian - Russia',
        'sa'         => 'Sanskrit',
        'sa-IN'      => 'Sanskrit - India',
        'sk'         => 'Slovak',
        'sk-SK'      => 'Slovak - Slovakia',
        'sl'         => 'Slovenian',
        'sl-SI'      => 'Slovenian - Slovenia',
        'sq'         => 'Albanian',
        'sq-AL'      => 'Albanian - Albania',
        'sr-SP-Cyrl' => 'Serbian (Cyrillic) - Serbia',
        'sr-SP-Latn' => 'Serbian (Latin) - Serbia',
        'sv'         => 'Swedish',
        'sv-FI'      => 'Swedish - Finland',
        'sv-SE'      => 'Swedish - Sweden',
        'sw'         => 'Swahili',
        'sw-KE'      => 'Swahili - Kenya',
        'syr'        => 'Syriac',
        'syr-SY'     => 'Syriac - Syria',
        'ta'         => 'Tamil',
        'ta-IN'      => 'Tamil - India',
        'te'         => 'Telugu',
        'te-IN'      => 'Telugu - India',
        'th'         => 'Thai',
        'th-TH'      => 'Thai - Thailand',
        'tr'         => 'Turkish',
        'tr-TR'      => 'Turkish - Turkey',
        'tt'         => 'Tatar',
        'tt-RU'      => 'Tatar - Russia',
        'uk'         => 'Ukrainian',
        'uk-UA'      => 'Ukrainian - Ukraine',
        'ur'         => 'Urdu',
        'ur-PK'      => 'Urdu - Pakistan',
        'uz'         => 'Uzbek',
        'uz-UZ-Cyrl' => 'Uzbek (Cyrillic) - Uzbekistan',
        'uz-UZ-Latn' => 'Uzbek (Latin) - Uzbekistan',
        'vi'         => 'Vietnamese',
        'zh-CHT'     => 'Chinese (Traditional)',
        'zh-CHS'     => 'Chinese (Simplified)',
        'zh-CN'      => 'Chinese - China',
        'zh-HK'      => 'Chinese - Hong Kong SAR',
        'zh-MO'      => 'Chinese - Macao SAR',
        'zh-SG'      => 'Chinese - Singapore',
        'zh-TW'      => 'Chinese - Taiwan',
    ) ;
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
                                
                                if ( !empty($tracks) ) {
                                    foreach ( $tracks as $track ) {
                                        $type = arrayPath( $track, 'text_type', null );
                                        if ( !empty($type) && $type === 'subtitles' ) {
                                            $this->_subtitles[] = $track;
                                        }
                                    }
                                    foreach ( $this->_subtitles as &$subtitle ) {
                                        $subtitle['local'] = false;
                                    }
                                }
                                
                                $captions = get_post_meta( $this->attachmentId, '_captions', true );
                                if ( $captions && is_array( $captions ) ) {
                                    $this->_subtitles = array_merge( $this->_subtitles, $captions );
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
    public function addLocalCaptions( $language, $captionsURL, $closedCaptions = false )
    {
        $captions = get_post_meta( $this->attachmentId, '_captions', true );
        if ( !is_array( $captions ) ) {
            $captions = [];
        }
        if ( !isset( static::$subtitleLanguages[$language] ) ) {
            return false;
        }
        $captions[] = [
            'id'            => gen_uuid(),
            'name'          => static::$subtitleLanguages[$language],
            'language_code' => $language,
            'local'         => true,
            'cc'            => $closedCaptions,
            'url'           => $captionsURL,
        ];
        update_post_meta( $this->attachmentId, '_captions', $captions );
        return true;
    }
    
    public function addCaptions( $language, $captionsURL, $closedCaptions = false )
    {
        if ( $this->isTransferred ) {
            return $this->addLocalCaptions( $language, $captionsURL, $closedCaptions );
        }
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
        
        if ( $this->isTransferred ) {
            $captions = get_post_meta( $this->attachmentId, '_captions', true ) ?? [];
            $newCaptions = [];
            foreach ( $captions as $caption ) {
                if ( $caption['id'] == $captionsId ) {
                    continue;
                }
                $newCaptions[] = $caption;
            }
            update_post_meta( $this->attachmentId, '_captions', $newCaptions );
            return true;
        }
        
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
        
        if ( $this->isTransferred && !empty($this->transferData) ) {
            
            if ( $this->transferData['source'] === 's3' ) {
                /** @var StorageTool $storageTool */
                $storageTool = ToolsManager::instance()->tools['storage'];
                return $storageTool->getAttachmentURLFromMeta( [
                    'type' => 'application/vnd.apple.mpegurl',
                    's3'   => [
                    'key' => $this->transferData['playlist'],
                ],
                ] );
            } else {
                $uploadDir = wp_upload_dir();
                return trailingslashit( $uploadDir['baseurl'] ) . $this->transferData['playlist'];
            }
        
        } else {
            $url = ( $preferSecure ? $this->secureVideoUrl() : $this->publicVideoUrl() );
            if ( !empty($url) ) {
                return $url;
            }
            return ( $preferSecure ? $this->publicVideoUrl() : $this->secureVideoUrl() );
        }
    
    }
    
    public function secureRenditionUrl( $qualityLevel )
    {
        Logger::info(
            "Getting secure rendition url for {$qualityLevel}",
            [],
            __METHOD__,
            __LINE__
        );
        return $this->publicRenditionUrl( $qualityLevel );
    }
    
    public function publicRenditionUrl( $qualityLevel )
    {
        $pid = $this->__get( 'publicPlaybackID' );
        Logger::info(
            "Getting public rendition URL for {$pid} at {$qualityLevel}",
            [],
            __METHOD__,
            __LINE__
        );
        if ( empty($pid) ) {
            return null;
        }
        return "https://stream.mux.com/{$pid}/{$qualityLevel}";
    }
    
    public function renditionUrl( $qualityLevel, $preferSecure = true )
    {
        
        if ( $this->isTransferred && !empty($this->transferData) ) {
            if ( isset( $this->transferData['renditions'][$qualityLevel] ) ) {
                
                if ( $this->transferData['source'] === 's3' ) {
                    /** @var StorageTool $storageTool */
                    $storageTool = ToolsManager::instance()->tools['storage'];
                    return $storageTool->getAttachmentURLFromMeta( [
                        'type' => 'video/mp4',
                        's3'   => [
                        'key' => $this->transferData['renditions'][$qualityLevel],
                    ],
                    ] );
                } else {
                    $uploadDir = wp_upload_dir();
                    return trailingslashit( $uploadDir['baseurl'] ) . $this->transferData['renditions'][$qualityLevel];
                }
            
            }
            
            if ( $qualityLevel === 'high.mp4' ) {
                return $this->renditionUrl( 'medium.mp4', $preferSecure );
            } else {
                if ( $qualityLevel === 'medium.mp4' ) {
                    return $this->renditionUrl( 'low.mp4', $preferSecure );
                }
            }
            
            return null;
        }
        
        $url = ( $preferSecure ? $this->secureRenditionUrl( $qualityLevel ) : $this->publicRenditionUrl( $qualityLevel ) );
        Logger::info(
            "Rendition URL: {$qualityLevel} => {$url}",
            [],
            __METHOD__,
            __LINE__
        );
        if ( !empty($url) ) {
            return $url;
        }
        
        if ( $qualityLevel === 'high.mp4' ) {
            return $this->renditionUrl( 'medium.mp4', $preferSecure );
        } else {
            if ( $qualityLevel === 'medium.mp4' ) {
                return $this->renditionUrl( 'low.mp4', $preferSecure );
            }
        }
        
        return null;
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
    //region Filmstrips
    /**
     * Generates filmstrip for video
     *
     * @throws \Freemius_Exception
     */
    public function generateFilmstrip()
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
    
    //endregion
    //region Transfer
    private function downloadFile(
        string $sourceUrl,
        string $destKey,
        string $destFile,
        string $mimeType,
        StorageTool $storageTool,
        bool $localOnly,
        callable $status,
        callable $error
    )
    {
        if ( file_exists( $destFile ) ) {
            @unlink( $destFile );
        }
        $status(
            "Downloading {$sourceUrl} ... ",
            false,
            __METHOD__,
            __LINE__
        );
        $result = ilab_stream_download( $sourceUrl, $destFile );
        $status(
            "Done.",
            true,
            __METHOD__,
            __LINE__
        );
        
        if ( !$result ) {
            $error( "Error downloading {$sourceUrl}", __METHOD__, __LINE__ );
            return false;
        }
        
        
        if ( !$localOnly ) {
            $status(
                "Uploading {$destKey} ... ",
                false,
                __METHOD__,
                __LINE__
            );
            $storageTool->client()->upload(
                $destKey,
                $destFile,
                StorageConstants::ACL_PUBLIC_READ,
                null,
                null,
                $mimeType
            );
            $status(
                "Done.",
                true,
                __METHOD__,
                __LINE__
            );
            
            if ( StorageToolSettings::deleteOnUpload() ) {
                $status(
                    "Deleting {$destFile} from local ... ",
                    false,
                    __METHOD__,
                    __LINE__
                );
                @unlink( $destFile );
                $status(
                    "Done.",
                    true,
                    __METHOD__,
                    __LINE__
                );
            }
        
        }
        
        return true;
    }
    
    /**
     * Transfers a Mux video to cloud storage and/or local storage
     *
     * @param string $dest
     *
     * @return string[]|void
     */
    public function getFilesToTransfer( string $dest )
    {
        
        if ( $this->isDeleted === 1 ) {
            Logger::info(
                "Skipping deleted asset.",
                true,
                __METHOD__,
                __LINE__
            );
            return [];
        }
        
        $allFiles = [];
        return $allFiles;
    }
    
    /**
     * Transfers a Mux video to cloud storage and/or local storage
     *
     * @param string $dest
     * @param bool $delete
     * @param bool $localOnly
     * @param callable $status
     * @param callable $error
     *
     * @return false|void
     * @throws \MediaCloud\Plugin\Tools\Storage\StorageException
     */
    public function transfer(
        string $dest,
        bool $delete,
        bool $localOnly,
        callable $status,
        callable $error
    )
    {
        
        if ( $this->isDeleted === 1 ) {
            $status(
                "Skipping deleted asset.",
                true,
                __METHOD__,
                __LINE__
            );
            return true;
        }
        
        $relatedFiles = [];
        return true;
    }
    
    public function backgroundTransfer()
    {
    }

}