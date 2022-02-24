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
namespace MediaCloud\Plugin\Tools\Storage;

use  MediaCloud\Plugin\Tools\ToolsManager ;
use  MediaCloud\Plugin\Utilities\Environment ;
use  MediaCloud\Plugin\Utilities\Logging\Logger ;
use  MediaCloud\Plugin\Utilities\Tracker ;
use  MediaCloud\Plugin\Utilities\View ;
use  MediaCloud\Vendor\GuzzleHttp\Client ;
use  MediaCloud\Vendor\GuzzleHttp\Exception\RequestException ;
use function  MediaCloud\Plugin\Utilities\arrayPath ;
use function  MediaCloud\Plugin\Utilities\disableHooks ;
use function  MediaCloud\Plugin\Utilities\gen_uuid ;
use function  MediaCloud\Plugin\Utilities\ilab_set_time_limit ;
use function  MediaCloud\Plugin\Utilities\postIdExists ;
class StorageUtilities
{
    /** @var null|StorageUtilities  */
    private static  $_instance = null ;
    public function __construct()
    {
        if ( !is_admin() ) {
            return;
        }
        if ( current_user_can( 'edit_posts' ) ) {
            add_action( 'admin_init', function () {
                add_action( 'wp_ajax_media_cloud_update_metadata', [ $this, 'actionUpdateMetadata' ] );
                add_action( 'wp_ajax_media_cloud_audit_metadata', [ $this, 'actionStartAudit' ] );
                add_action( 'wp_ajax_media_cloud_fix_metadata', [ $this, 'actionFixMetadata' ] );
                if ( strtolower( pathinfo( $_SERVER['SCRIPT_NAME'], PATHINFO_BASENAME ) ) === 'post.php' ) {
                    add_action( 'admin_enqueue_scripts', function ( $hook ) {
                        $cm_settings = [
                            'codeEditor' => wp_enqueue_code_editor( array(
                            'type' => 'text/x-php',
                        ) ),
                        ];
                        wp_localize_script( 'jquery', 'cm_settings', $cm_settings );
                        wp_enqueue_script( 'wp-theme-plugin-editor' );
                        wp_enqueue_style( 'wp-codemirror' );
                    } );
                }
                add_meta_box(
                    'media-cloud-metadata',
                    'Media Cloud Metadata Tools',
                    [ $this, 'renderMetadataMetabox' ],
                    'attachment',
                    'advanced',
                    'low'
                );
            } );
        }
        $this->hookupUI();
    }
    
    /**
     * @return StorageUtilities|null
     */
    public static function instance()
    {
        if ( static::$_instance === null ) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }
    
    //region UI
    private function hookupUI()
    {
    }
    
    public function displayReplaceUI()
    {
        $postId = arrayPath( $_REQUEST, 'postId' );
        $data = [
            'postId'  => $postId,
            'modalId' => gen_uuid( 8 ),
        ];
        
        if ( current_user_can( 'edit_post', $postId ) ) {
            Tracker::trackView( 'Replace Image', '/image/replace' );
            echo  View::render_view( 'storage.replace-image', $data ) ;
        }
        
        die;
    }
    
    private function replacePageURL( $id )
    {
        $url = parse_url( get_admin_url( null, 'admin-ajax.php' ), PHP_URL_PATH ) . "?action=media_cloud_show_replace_UI&postId={$id}";
        return $url;
    }
    
    //endregion
    //region Audit
    private function verifyRemote( $client, $remoteUrl )
    {
        try {
            $res = $client->get( $remoteUrl, [
                'headers' => [
                'Range' => 'bytes=0-0',
            ],
            ] );
            $code = $res->getStatusCode();
        } catch ( RequestException $ex ) {
            $code = 400;
            if ( $ex->hasResponse() ) {
                $code = $ex->getResponse()->getStatusCode();
            }
        }
        return $code;
    }
    
    public function audit( $postId )
    {
        disableHooks( [
            'get_attached_file',
            'image_downsize',
            'wp_get_attachment_url',
            'wp_update_attachment_metadata'
        ] );
        Logger::info(
            "Starting audit",
            [
            'post' => $postId,
        ],
            __METHOD__,
            __LINE__
        );
        add_filter( 'media-cloud/storage/ignore-cdn', '__return_true', PHP_INT_MAX );
        add_filter( 'media-cloud/dynamic-images/skip-url-generation', '__return_true', PHP_INT_MAX );
        /** @var StorageTool $storageTool */
        $storageTool = ToolsManager::instance()->tools['storage'];
        $sizes = ilab_get_image_sizes();
        $mime = get_post_mime_type( $postId );
        $uploadDirs = wp_get_upload_dir();
        $client = new Client();
        $audit = [
            'Core Files' => [],
        ];
        Logger::info(
            "Start Attached File Audit",
            [
            'post' => $postId,
        ],
            __METHOD__,
            __LINE__
        );
        $file = get_attached_file( $postId, true );
        add_filter( 'media-cloud/storage/override-url', '__return_false', PHP_INT_MAX );
        Logger::info(
            "Attached File Audit - raw wp_get_attachment_url",
            [
            'post' => $postId,
        ],
            __METHOD__,
            __LINE__
        );
        $fileUrl = wp_get_attachment_url( $postId );
        remove_filter( 'media-cloud/storage/override-url', '__return_false', PHP_INT_MAX );
        $key = ltrim( str_replace( $uploadDirs['basedir'], '', $file ), '/' );
        Logger::info(
            "Attached File Audit - wp_get_attachment_url",
            [
            'post' => $postId,
        ],
            __METHOD__,
            __LINE__
        );
        $remoteUrl = wp_get_attachment_url( $postId );
        $remoteFound = $this->verifyRemote( $client, $remoteUrl ) < 400;
        $audit['Core Files']['Attached File'] = [
            'Local File' => ( file_exists( $file ) ? $file : null ),
            'Local URL'  => ( file_exists( $file ) ? $fileUrl : null ),
            'Remote URL' => ( $remoteFound ? $remoteUrl : null ),
        ];
        
        if ( !$remoteFound ) {
            $forcedUrl = $storageTool->client()->presignedUrl( $key, 10 );
            $forcedFound = $this->verifyRemote( $client, $forcedUrl ) < 400;
            $audit['Core Files']['Attached File']['Forced URL'] = ( $forcedFound ? $forcedUrl : null );
        }
        
        
        if ( strpos( $mime, 'image' ) === 0 ) {
            Logger::info(
                "Original Image Audit - wp_get_original_image_path",
                [
                'post' => $postId,
            ],
                __METHOD__,
                __LINE__
            );
            $file = wp_get_original_image_path( $postId, true );
            
            if ( !empty($file) ) {
                add_filter( 'media-cloud/storage/override-url', '__return_false', PHP_INT_MAX );
                Logger::info(
                    "Original Image Audit - raw wp_get_original_image_url",
                    [
                    'post' => $postId,
                ],
                    __METHOD__,
                    __LINE__
                );
                $fileUrl = wp_get_original_image_url( $postId );
                remove_filter( 'media-cloud/storage/override-url', '__return_false', PHP_INT_MAX );
                $originalKey = ltrim( str_replace( $uploadDirs['basedir'], '', $file ), '/' );
                
                if ( $originalKey !== $key ) {
                    $remoteUrl = $storageTool->client()->presignedUrl( $originalKey, 10 );
                    $remoteFound = $this->verifyRemote( $client, $remoteUrl ) < 400;
                    $audit['Core Files']['Original Image'] = [
                        'Local File' => ( file_exists( $file ) ? $file : null ),
                        'Local URL'  => ( file_exists( $file ) ? $fileUrl : null ),
                        'Remote URL' => ( $remoteFound ? $remoteUrl : null ),
                    ];
                }
            
            }
            
            $audit['Thumbnails'] = [];
            Logger::info(
                "Thumbnails Audit",
                [
                'post' => $postId,
            ],
                __METHOD__,
                __LINE__
            );
            foreach ( $sizes as $size => $sizeData ) {
                add_filter( 'media-cloud/storage/override-url', '__return_false', PHP_INT_MAX );
                Logger::info(
                    "Thumbnails Audit - {$size} - raw wp_get_attachment_image_src",
                    [
                    'post' => $postId,
                ],
                    __METHOD__,
                    __LINE__
                );
                $thumbUrl = wp_get_attachment_image_src( $postId, $size );
                Logger::info(
                    "Thumbnails Audit - {$size} - finished raw wp_get_attachment_image_src",
                    [
                    'post' => $postId,
                ],
                    __METHOD__,
                    __LINE__
                );
                remove_filter( 'media-cloud/storage/override-url', '__return_false', PHP_INT_MAX );
                
                if ( !empty($thumbUrl) ) {
                    $sizeUrl = $thumbUrl[0];
                    $sizeKey = ltrim( str_replace( $uploadDirs['baseurl'], '', $sizeUrl ), '/' );
                    
                    if ( $sizeKey === $key ) {
                        $audit['Thumbnails'][$size] = [
                            'Local File' => null,
                        ];
                        Logger::info(
                            "Thumbnails Audit - {$size} - Key is the same, skipping",
                            [
                            'post' => $postId,
                        ],
                            __METHOD__,
                            __LINE__
                        );
                        continue;
                    }
                    
                    $sizeFile = trailingslashit( $uploadDirs['basedir'] ) . $sizeKey;
                    Logger::info(
                        "Thumbnails Audit - {$size} - wp_get_attachment_image_src",
                        [
                        'post' => $postId,
                    ],
                        __METHOD__,
                        __LINE__
                    );
                    $remoteSizeUrl = wp_get_attachment_image_src( $postId, $size )[0];
                    $remoteSizeFound = $this->verifyRemote( $client, $remoteSizeUrl ) < 400;
                    $audit['Thumbnails'][$size] = [
                        'Local File' => ( file_exists( $sizeFile ) ? $sizeFile : null ),
                        'Local URL'  => ( file_exists( $sizeFile ) ? $sizeUrl : null ),
                        'Remote URL' => ( $remoteSizeFound ? $remoteSizeUrl : null ),
                    ];
                    
                    if ( !$remoteSizeFound ) {
                        $forcedSizeUrl = $storageTool->client()->presignedUrl( $sizeKey, 10 );
                        $forcedSizeFound = $this->verifyRemote( $client, $forcedSizeUrl ) < 400;
                        $audit['Thumbnails'][$size]['Forced URL'] = ( $forcedSizeFound ? $forcedSizeUrl : null );
                    }
                
                } else {
                    Logger::info(
                        "Thumbnails Audit - {$size} - URL is empty",
                        [
                        'post' => $postId,
                    ],
                        __METHOD__,
                        __LINE__
                    );
                    $audit['Thumbnails'][$size] = [
                        'Local File' => null,
                    ];
                }
            
            }
        }
        
        remove_filter( 'media-cloud/storage/ignore-cdn', '__return_true', PHP_INT_MAX );
        remove_filter( 'media-cloud/dynamic-images/skip-url-generation', '__return_true', PHP_INT_MAX );
        Logger::info(
            "Finished audit",
            [
            'post' => $postId,
        ],
            __METHOD__,
            __LINE__
        );
        return $audit;
    }
    
    //endregion
    //region Fix Metadata
    public function fixMetadata( $postId, $additionalSizes = array() )
    {
        disableHooks( [
            'get_attached_file',
            'image_downsize',
            'wp_get_attachment_url',
            'wp_update_attachment_metadata'
        ] );
        /** @var StorageTool $storageTool */
        $storageTool = ToolsManager::instance()->tools['storage'];
        $providerClass = get_class( $storageTool->client() );
        $providerId = $providerClass::identifier();
        $audit = $this->audit( $postId );
        $postMime = get_post_mime_type( $postId );
        $attachedUrl = arrayPath( $audit, 'Core Files/Attached File/Forced URL' );
        if ( empty($attachedUrl) && strpos( $postMime, 'image' ) === 0 ) {
            $attachedUrl = arrayPath( $audit, 'Core Files/Attached File/Remote URL' );
        }
        if ( empty($attachedUrl) ) {
            return false;
        }
        
        if ( strpos( $postMime, 'image' ) === 0 ) {
            $meta = get_post_meta( $postId, '_wp_attachment_metadata', true );
        } else {
            $meta = get_post_meta( $postId, 'ilab_s3_info', true );
        }
        
        if ( empty($meta) ) {
            $meta = [];
        }
        $attachedKey = ltrim( parse_url( $attachedUrl, PHP_URL_PATH ), '/' );
        if ( $storageTool->client()->isUsingPathStyleEndPoint() ) {
            $attachedKey = ltrim( str_replace( $storageTool->client()->bucket(), '', $attachedKey ), '/' );
        }
        $attachedFile = get_post_meta( $postId, '_wp_attached_file', true );
        try {
            $info = $storageTool->client()->info( $attachedKey );
        } catch ( \Exception $ex ) {
            Logger::error(
                'Error fetching atttached file S3 info: ' . $ex->getMessage(),
                [],
                __METHOD__,
                __LINE__
            );
            return false;
        }
        if ( !empty($attachedFile) ) {
            $meta['file'] = $attachedFile;
        }
        
        if ( strpos( $info->mimeType(), 'image' ) !== 0 ) {
            $meta['type'] = $info->mimeType();
            $meta['url'] = $storageTool->client()->url( $attachedKey, 'skip' );
        }
        
        $meta['s3'] = [
            'url'       => $storageTool->client()->url( $attachedKey, 'skip' ),
            'key'       => $attachedKey,
            'bucket'    => $storageTool->client()->bucket(),
            'privacy'   => $storageTool->client()->acl( $attachedKey ) ?? 'public-read',
            'provider'  => $providerId,
            'v'         => MEDIA_CLOUD_INFO_VERSION,
            'options'   => [],
            'optimized' => false,
            'mime-type' => $info->mimeType(),
        ];
        
        if ( strpos( $info->mimeType(), 'image' ) !== 0 ) {
            update_post_meta( $postId, 'ilab_s3_info', $meta );
            return true;
        }
        
        $sz = $info->size();
        $width = 0;
        $height = 0;
        
        if ( !empty($sz) ) {
            $width = $meta['width'] = $sz[0];
            $height = $meta['height'] = $sz[1];
        }
        
        $originalImageUrl = arrayPath( $audit, 'Core Files/Original Image/Forced URL' );
        if ( empty($originalImageUrl) && strpos( $attachedKey, '-scaled' ) !== false ) {
            $originalImageUrl = $storageTool->client()->url( str_replace( '-scaled', '', $attachedKey ), 'skip' );
        }
        
        if ( !empty($originalImageUrl) ) {
            $originalKey = trim( parse_url( $originalImageUrl, PHP_URL_PATH ), '/' );
            if ( $storageTool->client()->isUsingPathStyleEndPoint() ) {
                $originalKey = ltrim( str_replace( $storageTool->client()->bucket(), '', $originalKey ), '/' );
            }
            try {
                $originalInfo = $storageTool->client()->info( $originalKey );
                $meta['original_image'] = pathinfo( $originalKey, PATHINFO_BASENAME );
                $meta['original_image_s3'] = [
                    'url'       => $storageTool->client()->url( $originalKey, 'skip' ),
                    'key'       => $originalKey,
                    'bucket'    => $storageTool->client()->bucket(),
                    'privacy'   => $storageTool->client()->acl( $originalKey ) ?? 'public-read',
                    'provider'  => $providerId,
                    'v'         => MEDIA_CLOUD_INFO_VERSION,
                    'options'   => [],
                    'optimized' => false,
                    'mime-type' => $originalInfo->mimeType(),
                ];
            } catch ( \Exception $ex ) {
                Logger::error(
                    'Error fetching original image S3 info: ' . $ex->getMessage(),
                    [],
                    __METHOD__,
                    __LINE__
                );
            }
        }
        
        $baseinfo = pathinfo( str_replace( '-scaled', '', $attachedKey ) );
        $basepath = $baseinfo['dirname'] . '/';
        $basefile = $baseinfo['filename'];
        $baseext = $baseinfo['extension'];
        switch ( strtolower( $baseext ) ) {
            case 'png':
                $baseext = '.png';
                break;
            case 'gif':
                $baseext = '.gif';
                break;
            default:
                $baseext = '.jpg';
                break;
        }
        $meta['sizes'] = [];
        $sizes = ilab_get_image_sizes();
        foreach ( $sizes as $size => $sizeData ) {
            $sizeUrl = arrayPath( $audit, "Thumbnails/{$size}/Forced URL" );
            $cropFile = null;
            if ( empty($sizeUrl) && !empty($width) && !empty($height) ) {
                
                if ( !empty($sizeData['crop']) ) {
                    $sizeUrl = $storageTool->client()->url( $basepath . $basefile . "-{$sizeData['width']}x{$sizeData['height']}" . $baseext, 'skip' );
                    $cropFile = $basefile . "-{$sizeData['width']}x{$sizeData['height']}" . $baseext;
                } else {
                    $cropWidth = intval( $sizeData['width'] );
                    $cropHeight = intval( $sizeData['height'] );
                    $cropWidth = ( $cropWidth === 0 ? 9999 : $cropWidth );
                    $cropHeight = ( $cropHeight === 0 ? 9999 : $cropHeight );
                    $newSize = sizeToFitSize(
                        $width,
                        $height,
                        $cropWidth,
                        $cropHeight
                    );
                    $sizeUrl = $storageTool->client()->url( $basepath . $basefile . "-" . intval( $newSize[0] ) . "x" . intval( $newSize[1] ) . $baseext, 'skip' );
                    $cropFile = $basefile . "-" . intval( $newSize[0] ) . "x" . intval( $newSize[1] ) . $baseext;
                }
            
            }
            if ( empty($sizeUrl) ) {
                continue;
            }
            $sizeKey = ltrim( parse_url( $sizeUrl, PHP_URL_PATH ), '/' );
            if ( $storageTool->client()->isUsingPathStyleEndPoint() ) {
                $sizeKey = ltrim( str_replace( $storageTool->client()->bucket(), '', $sizeKey ), '/' );
            }
            try {
                $sizeInfo = $storageTool->client()->info( $sizeKey );
                $meta['sizes'][$size] = [
                    'file'      => $cropFile,
                    'width'     => $sizeInfo->size()[0],
                    'height'    => $sizeInfo->size()[1],
                    'mime-type' => $sizeInfo->mimeType(),
                    's3'        => [
                    'url'       => $storageTool->client()->url( $sizeKey, 'skip' ),
                    'key'       => $sizeKey,
                    'bucket'    => $storageTool->client()->bucket(),
                    'privacy'   => $storageTool->client()->acl( $sizeKey ) ?? 'public-read',
                    'provider'  => $providerId,
                    'v'         => MEDIA_CLOUD_INFO_VERSION,
                    'options'   => [],
                    'optimized' => false,
                    'mime-type' => $sizeInfo->mimeType(),
                ],
                ];
            } catch ( \Exception $ex ) {
                Logger::error(
                    'Error fetching size $size S3 info: ' . $ex->getMessage(),
                    [],
                    __METHOD__,
                    __LINE__
                );
                continue;
            }
        }
        
        if ( count( $additionalSizes ) > 0 ) {
            $sizeKeys = array_keys( arrayPath( $meta, 'sizes', [] ) );
            $diff = array_diff( array_keys( $additionalSizes ), $sizeKeys );
            if ( count( $diff ) > 0 ) {
                foreach ( $diff as $addedSizeName ) {
                    $meta['sizes'][$addedSizeName] = $additionalSizes[$addedSizeName];
                }
            }
        }
        
        update_post_meta( $postId, '_wp_attachment_metadata', $meta );
        return true;
    }
    
    //endregion
    //region Actions
    public function actionUpdateMetadata()
    {
        check_ajax_referer( 'media_cloud_update_metadata', 'nonce' );
        Tracker::trackView( 'Fix Metadata', '/attachment/update-metadata' );
        if ( !current_user_can( 'edit_posts' ) ) {
            wp_send_json( [
                'status' => 'error',
                'error'  => 'Invalid user',
            ], 400 );
        }
        $postId = intval( $_REQUEST['post'] );
        $ilabS3 = boolval( $_REQUEST['ilabS3'] );
        if ( empty($postId) || !postIdExists( $postId ) ) {
            wp_send_json( [
                'status' => 'error',
                'error'  => 'Invalid user',
            ], 400 );
        }
        $metadataJSON = $_REQUEST['metadata'];
        if ( empty($metadataJSON) ) {
            wp_send_json( [
                'status' => 'error',
                'error'  => 'Invalid JSON',
            ], 400 );
        }
        $metadata = json_decode( stripslashes( $metadataJSON ), true );
        
        if ( $metadata === null ) {
            $last = json_last_error_msg();
            wp_send_json( [
                'status' => 'error',
                'error'  => 'Invalid JSON',
            ], 400 );
        }
        
        
        if ( $ilabS3 ) {
            update_post_meta( $postId, 'ilab_s3_info', $metadata );
        } else {
            update_post_meta( $postId, '_wp_attachment_metadata', $metadata );
        }
        
        wp_send_json( [
            'status' => 'success',
        ], 200 );
    }
    
    public function actionStartAudit()
    {
        check_ajax_referer( 'media_cloud_audit_metadata', 'nonce' );
        Tracker::trackView( 'Fix Metadata', '/attachment/audit' );
        if ( !current_user_can( 'edit_posts' ) ) {
            wp_send_json( [
                'status' => 'error',
                'error'  => 'Invalid user',
            ], 400 );
        }
        $postId = intval( $_REQUEST['post'] );
        if ( empty($postId) || !postIdExists( $postId ) ) {
            wp_send_json( [
                'status' => 'error',
                'error'  => 'Invalid post',
            ], 400 );
        }
        $audit = $this->audit( $postId );
        $auditHtml = View::render_view( 'storage.audit-table', [
            'audit' => $audit,
        ] );
        wp_send_json( [
            'status' => 'success',
            'audit'  => $audit,
            'html'   => $auditHtml,
        ], 200 );
    }
    
    public function actionFixMetadata()
    {
        check_ajax_referer( 'media_cloud_fix_metadata', 'nonce' );
        Tracker::trackView( 'Fix Metadata', '/attachment/fix-metadata' );
        if ( !current_user_can( 'edit_posts' ) ) {
            wp_send_json( [
                'status' => 'error',
                'error'  => 'Invalid user',
            ], 400 );
        }
        $postId = intval( $_REQUEST['post'] );
        if ( empty($postId) || !postIdExists( $postId ) ) {
            wp_send_json( [
                'status' => 'error',
                'error'  => 'Invalid post',
            ], 400 );
        }
        $this->fixMetadata( $postId );
        wp_send_json( [
            'status' => 'success',
        ], 200 );
    }
    
    public function actionRegenerateThumbnail()
    {
    }
    
    public function actionReplaceImage()
    {
    }
    
    //endregion
    //region Metabox
    public function renderMetadataMetabox( $post )
    {
        $meta = get_post_meta( $post->ID, '_wp_attachment_metadata', true );
        $ilab = get_post_meta( $post->ID, 'ilab_s3_info', true );
        $attached = get_post_meta( $post->ID, '_wp_attached_file', true );
        echo  View::render_view( 'storage.metadata-panel', [
            'meta'         => json_encode( $meta, JSON_PRETTY_PRINT ),
            'ilab'         => json_encode( $ilab, JSON_PRETTY_PRINT ),
            'post'         => $post,
            'attachedFile' => $attached,
        ] ) ;
    }

}