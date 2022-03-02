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

use  MediaCloud\Plugin\Tasks\TaskReporter ;
use  MediaCloud\Plugin\Tasks\TaskManager ;
use  MediaCloud\Plugin\Tasks\TaskRunner ;
use  MediaCloud\Plugin\Tasks\TaskSchedule ;
use  MediaCloud\Plugin\Tools\ImageSizes\ImageSizePrivacy ;
use  MediaCloud\Plugin\Tools\Optimizer\OptimizerConsts ;
use  MediaCloud\Plugin\Tools\Optimizer\OptimizerTool ;
use  MediaCloud\Plugin\Tools\Optimizer\OptimizerToolSettings ;
use  MediaCloud\Plugin\Tools\Storage\Driver\S3\S3StorageSettings ;
use  MediaCloud\Plugin\Tools\Storage\Tasks\CleanUploadsTask ;
use  MediaCloud\Plugin\Tools\Storage\Tasks\DeleteUploadsTask ;
use  MediaCloud\Plugin\Tools\Storage\Tasks\FixMetadataTask ;
use  MediaCloud\Plugin\Tools\Storage\Tasks\MigrateFromOtherTask ;
use  MediaCloud\Plugin\Tools\Storage\Tasks\MigrateTask ;
use  MediaCloud\Plugin\Tools\Storage\Tasks\RegenerateThumbnailTask ;
use  MediaCloud\Plugin\Tools\Storage\Tasks\SyncLocalTask ;
use  MediaCloud\Plugin\Tools\Storage\Tasks\UnlinkTask ;
use  MediaCloud\Plugin\Tools\Storage\Tasks\UpdateURLsTask ;
use  MediaCloud\Plugin\Tools\Storage\Tasks\VerifyLibraryTask ;
use  MediaCloud\Plugin\Tools\Tool ;
use  MediaCloud\Plugin\Tools\ToolsManager ;
use  MediaCloud\Plugin\Utilities\Environment ;
use  MediaCloud\Plugin\Utilities\Logging\Logger ;
use  MediaCloud\Plugin\Utilities\NoticeManager ;
use  MediaCloud\Plugin\Utilities\Prefixer ;
use  MediaCloud\Plugin\Utilities\View ;
use  MediaCloud\Vendor\GuzzleHttp\Client ;
use  MediaCloud\Vendor\GuzzleHttp\Exception\RequestException ;
use  MediaCloud\Vendor\Smalot\PdfParser\Parser ;
use function  MediaCloud\Plugin\Utilities\arrayPath ;
use function  MediaCloud\Plugin\Utilities\gen_uuid ;
use function  MediaCloud\Plugin\Utilities\ilab_set_time_limit ;
use function  MediaCloud\Plugin\Utilities\phpMemoryLimit ;
use function  MediaCloud\Plugin\Utilities\typeFromMeta ;

if ( !defined( 'ABSPATH' ) ) {
    header( 'Location: /' );
    die;
}

/**
 * Class StorageTool
 *
 * Storage Tool.
 */
class StorageTool extends Tool
{
    //region Properties/Class Variables
    /** @var null|array */
    protected  $allSizes = null ;
    /** @var StorageToolSettings  */
    private  $settings = null ;
    /** @var array */
    private  $uploadedDocs = array() ;
    /** @var array */
    private  $renamedDocs = array() ;
    /** @var array */
    private  $pdfInfo = array() ;
    /** @var bool */
    private  $skipUpdate = false ;
    /** @var StorageInterface|null */
    private  $client = null ;
    /** @var bool Flag if we are currently processing an optimized image */
    private  $processingOptimized = false ;
    /** @var bool Determines if the user is using an image optimizer */
    private  $usingImageOptimizer = false ;
    /** @var string The name of the image optimizer */
    private  $imageOptimizer = null ;
    /** @var string Controls how file paths should be preserved when updated metadata */
    private  $preserveFilePaths = 'replace' ;
    /** @var string[] */
    private  $deleteCache = array() ;
    /** @var callable */
    private  $dieHandler = null ;
    /** @var array Already processed post IDs */
    private  $processed = array() ;
    private  $generated = array() ;
    private  $updateCallCount = 0 ;
    /** @var StorageContentHooks|null  */
    private  $contentHooks = null ;
    /** @var string|null  */
    private  $cloudIconSVG = null ;
    /** @var string|null  */
    private  $lockIconSVG = null ;
    //endregion
    //region Constructor
    public function __construct( $toolName, $toolInfo, $toolManager )
    {
        $this->settings = StorageToolSettings::instance();
        if ( !empty($toolInfo['storageDrivers']) ) {
            foreach ( $toolInfo['storageDrivers'] as $key => $data ) {
                if ( empty($data['name']) || empty($data['class']) || empty($data['config']) ) {
                    throw new \Exception( "Storage Tool configuration file is malformed.  Storage drivers are missing required information." );
                }
                $configFile = ILAB_CONFIG_DIR . $data['config'];
                if ( !file_exists( $configFile ) ) {
                    throw new \Exception( "Missing driver config file '{$configFile}'. " );
                }
                $config = (include $configFile);
                StorageToolSettings::registerDriver(
                    $key,
                    $data['name'],
                    $data['class'],
                    $config,
                    arrayPath( $data, 'help', null )
                );
            }
        }
        do_action( 'media-cloud/storage/register-drivers' );
        $driverConfigs = [];
        foreach ( StorageToolSettings::drivers() as $key => $driver ) {
            $driverConfigs[$key] = $driver['config'];
        }
        $toolInfo = $this->mergeSettings( $toolInfo, $driverConfigs );
        parent::__construct( $toolName, $toolInfo, $toolManager );
        $this->client = StorageToolSettings::storageInstance();
        if ( $this->haveSettingsChanged() ) {
            $this->settingsChanged();
        }
        $this->testForBadPlugins();
        $this->testForUselessPlugins();
        // Hate doing this but some WordPress installs are just f-cked
        if ( !function_exists( 'wp_generate_attachment_metadata' ) ) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }
        
        if ( is_admin() ) {
            
            if ( media_cloud_licensing()->is_plan( 'pro' ) ) {
                global  $wp_version ;
                $whitelistFilter = ( version_compare( $wp_version, '5.5', '>=' ) ? 'allowed_options' : 'whitelist_options' );
                add_filter( $whitelistFilter, function ( $options ) {
                    $options['ilab-media-s3'][] = 'mcloud-storage-privacy-images';
                    $options['ilab-media-s3'][] = 'mcloud-storage-privacy-video';
                    $options['ilab-media-s3'][] = 'mcloud-storage-privacy-audio';
                    $options['ilab-media-s3'][] = 'mcloud-storage-privacy-docs';
                    $options['ilab-media-s3'][] = 'mcloud-storage-use-presigned-urls-images';
                    $options['ilab-media-s3'][] = 'mcloud-storage-use-presigned-urls-video';
                    $options['ilab-media-s3'][] = 'mcloud-storage-use-presigned-urls-audio';
                    $options['ilab-media-s3'][] = 'mcloud-storage-use-presigned-urls-docs';
                    $options['ilab-media-s3'][] = 'mcloud-storage-presigned-expiration-images';
                    $options['ilab-media-s3'][] = 'mcloud-storage-presigned-expiration-video';
                    $options['ilab-media-s3'][] = 'mcloud-storage-presigned-expiration-audio';
                    $options['ilab-media-s3'][] = 'mcloud-storage-presigned-expiration-docs';
                    return $options;
                } );
            }
            
            
            if ( empty($this->settings->enableBigImageSize) ) {
                add_filter( 'big_image_size_threshold', '__return_false' );
            } else {
                $threshold = (int) Environment::Option( 'mcloud-storage-big-size-threshold', null, 2560 );
                if ( $threshold !== 2560 ) {
                    add_filter( 'big_image_size_threshold', function () use( $threshold ) {
                        return $threshold;
                    }, PHP_INT_MAX );
                }
            }
            
            $this->prepareMigrateFromOtherPlugin();
        }
        
        $canDelete = !empty(apply_filters( 'media-cloud/storage/delete_uploads', true ));
        
        if ( !empty($canDelete) && StorageToolSettings::deleteOnUpload() ) {
            
            if ( !isset( $_POST['action'] ) || isset( $_POST['action'] ) && !in_array( $_POST['action'], [ 'mcloud_task_heartbeat', 'mcloud_all_task_statuses', 'heartbeat' ] ) ) {
                add_filter( 'wp_die_ajax_handler', [ $this, 'hookDieHandler' ], PHP_INT_MAX );
                add_filter( 'wp_die_json_handler', [ $this, 'hookDieHandler' ], PHP_INT_MAX );
                add_filter( 'wp_die_jsonp_handler', [ $this, 'hookDieHandler' ], PHP_INT_MAX );
                add_filter( 'wp_die_xmlrpc_handler', [ $this, 'hookDieHandler' ], PHP_INT_MAX );
                add_filter( 'wp_die_xml_handler', [ $this, 'hookDieHandler' ], PHP_INT_MAX );
                add_filter( 'wp_die_handler', [ $this, 'hookDieHandler' ], PHP_INT_MAX );
                add_action( 'shutdown', function () {
                    $this->doCleanUploads();
                }, PHP_INT_MAX );
            }
            
            add_filter(
                'rest_pre_serve_request',
                function (
                $served,
                $result,
                $request,
                $server
            ) {
                $this->doCleanUploads();
                return $served;
            },
                PHP_INT_MAX,
                4
            );
        }
        
        add_filter(
            'do_parse_request',
            function ( $do, \WP $wp ) {
            
            if ( strpos( $_SERVER['REQUEST_URI'], '/__mcloud/attachment' ) === 0 ) {
                $postId = sanitize_text_field( arrayPath( $_REQUEST, 'pid', null ) );
                $nonce = sanitize_text_field( arrayPath( $_REQUEST, 'nonce', null ) );
                if ( empty($postId) || empty($nonce) ) {
                    return $do;
                }
                if ( !wp_verify_nonce( $nonce, 'mcloud_view_attachment_' . $postId ) ) {
                    return $do;
                }
                $url = wp_get_attachment_url( $postId );
                
                if ( !empty($url) ) {
                    wp_redirect( $url, 302 );
                    exit;
                }
            
            }
            
            return $do;
        },
            100,
            2
        );
    }
    
    //endregion
    //region Tool Overrides
    public function enabled()
    {
        $enabled = parent::enabled();
        if ( $enabled ) {
            $enabled = $this->client && $this->client->enabled();
        }
        return $enabled;
    }
    
    public function hasSettings()
    {
        return true;
    }
    
    public function hasWizard()
    {
        return true;
    }
    
    public function wizardLink()
    {
        return admin_url( 'admin.php?page=media-cloud-wizard' );
    }
    
    public function activate()
    {
        $provider = Environment::Option( 'mcloud-storage-provider', 'ILAB_CLOUD_STORAGE_PROVIDER', null );
        if ( empty($provider) ) {
            StorageToolMigrations::migrateFromOtherPlugin();
        }
    }
    
    public function setup()
    {
        parent::setup();
        StorageUtilities::instance();
        TaskManager::registerTask( UnlinkTask::class );
        TaskManager::registerTask( FixMetadataTask::class );
        
        if ( $this->enabled() ) {
            
            if ( is_admin() ) {
                
                if ( empty(get_option( 'uploads_use_yearmonth_folders' )) && empty(StorageToolSettings::prefixFormat()) ) {
                    $mediaUrl = ilab_admin_url( 'options-media.php' );
                    $settingsUrl = ilab_admin_url( 'admin.php?page=media-cloud-settings#upload-handling' );
                    NoticeManager::instance()->displayAdminNotice(
                        'warning',
                        "You have the WordPress setting <a href='{$mediaUrl}'><strong>Organize my uploads into month and year based folders</strong></a> disabled, but haven't specified an <em>Upload Path</em> in <a href='{$settingsUrl}'>Cloud Storage Settings</a>.  It is recommended that you either enable that setting, or set an upload directory.  We recommend setting the <em>Upload Path</em> to <code>@{date:Y/m}</code>.",
                        true,
                        'mcloud-no-upload-path',
                        365
                    );
                }
                
                
                if ( !empty($this->settings->replaceSrcSet) ) {
                    $settingsUrl = ilab_admin_url( 'admin.php?page=media-cloud-settings#responsive-image-settings' );
                    NoticeManager::instance()->displayAdminNotice(
                        'warning',
                        "You currently have the option <strong>Replace srcset on image tags</strong> enabled.  This setting is being deprecated and will be removed in the future.  You should disable it now in <a href='{$settingsUrl}'>Cloud Storage Settings</a>.  If you run into any issues, please let us know.",
                        true,
                        'mcloud-deprecated-replace-srcset',
                        365
                    );
                }
                
                
                if ( StorageToolSettings::driver() === 'do' && strpos( $this->client()->bucket(), '.' ) === false && !empty($this->client()->settings()->endPointPathStyle) ) {
                    $settingsUrl = ilab_admin_url( 'admin.php?page=media-cloud-settings#provider-settings' );
                    NoticeManager::instance()->displayAdminNotice(
                        'info',
                        "You currently have the option <strong>Path Style Endpoint</strong> enabled.  You do not need to have this setting enabled with DigitalOcean unless your bucket name contains a period.  It is enabled by default, but you can turn it off in <a href='{$settingsUrl}'>Cloud Storage Settings</a>.",
                        true,
                        'mcloud-do-path-endpoint-info',
                        365
                    );
                }
            
            }
            
            $this->contentHooks = new StorageContentHooks( $this );
            TaskManager::registerTask( CleanUploadsTask::class );
            TaskManager::registerTask( DeleteUploadsTask::class );
            TaskManager::registerTask( VerifyLibraryTask::class );
            TaskManager::registerTask( SyncLocalTask::class );
            $builtInImageOptimizer = ToolsManager::instance()->toolEnabled( 'optimizer' );
            $this->usingImageOptimizer = false;
            if ( empty($builtInImageOptimizer) ) {
                foreach ( $this->toolInfo['compatibleImageOptimizers'] as $key => $plugin ) {
                    
                    if ( is_plugin_active( $plugin ) ) {
                        $this->usingImageOptimizer = true;
                        $this->imageOptimizer = $key;
                        
                        if ( $key == 'shortpixel' ) {
                            add_action( 'shortpixel_image_optimised', [ $this, 'handleImageOptimizer' ] );
                            add_action( 'shortpixel_after_restore_image', [ $this, 'handleImageOptimizer' ] );
                        } else {
                            
                            if ( $key == 'smush' || $key == 'smush_pro' ) {
                                add_action(
                                    'wp_smush_image_optimised',
                                    [ $this, 'handleSmushImageOptimizer' ],
                                    1000,
                                    2
                                );
                            } else {
                                
                                if ( $key == 'ewww' ) {
                                    
                                    if ( $this->settings->disableEWWWBackgroundProcessing ) {
                                        define( 'EWWW_DISABLE_ASYNC', true );
                                        $this->usingImageOptimizer = false;
                                        $this->imageOptimizer = null;
                                    } else {
                                        add_action(
                                            'ewww_image_optimizer_post_optimization',
                                            function ( $file, $type, $fullsize ) {
                                            $this->processingOptimized = true;
                                        },
                                            1000,
                                            3
                                        );
                                    }
                                
                                } else {
                                    
                                    if ( $key == 'imagify' ) {
                                        add_action(
                                            'imagify_after_reoptimize_media',
                                            [ $this, 'handleImagifyAfter' ],
                                            1000,
                                            2
                                        );
                                        add_action(
                                            'imagify_after_optimize_media',
                                            [ $this, 'handleImagifyAfter' ],
                                            1000,
                                            2
                                        );
                                    }
                                
                                }
                            
                            }
                        
                        }
                    
                    }
                
                }
            }
            if ( $builtInImageOptimizer && !empty(OptimizerToolSettings::instance()->backgroundMode) || !empty($this->usingImageOptimizer) ) {
                $this->displayOptimizerAdminNotice( $builtInImageOptimizer );
            }
            global  $wp_version ;
            // NOTE: We handle this differently for 5.3 than versions prior.  Also, in the previous update
            // we only did this if certain post requirements were met:
            // (isset($_REQUEST['action']) && ($_REQUEST['action'] === 'upload-attachment')
            // but I don't remember why we had that requirement.
            
            if ( version_compare( $wp_version, '5.3', '>=' ) ) {
                add_filter(
                    'wp_generate_attachment_metadata',
                    [ $this, 'handleGenerateAttachmentMetadata' ],
                    1000,
                    3
                );
                add_filter(
                    'wp_update_attachment_metadata',
                    [ $this, 'handleUpdateAttachmentMetadataFor53' ],
                    1000,
                    2
                );
            } else {
                add_filter(
                    'wp_update_attachment_metadata',
                    [ $this, 'handleUpdateAttachmentMetadata' ],
                    1000,
                    2
                );
            }
            
            add_filter( 'wp_handle_upload_prefilter', function ( $file ) {
                $addFilter = true;
                
                if ( isset( $_FILES['themezip'] ) ) {
                    $addFilter = $file['name'] != $_FILES['themezip']['name'];
                } else {
                    if ( isset( $_FILES['pluginzip'] ) ) {
                        $addFilter = $file['name'] != $_FILES['pluginzip']['name'];
                    }
                }
                
                $addFilter = apply_filters( 'media-cloud/storage/add-upload-filter', $addFilter );
                if ( $addFilter ) {
                    add_filter( 'upload_dir', function ( $uploads ) use( $file ) {
                        $mimeType = arrayPath( $file, 'type', null );
                        return $this->getUploadDir( $uploads, $mimeType );
                    }, 1000 );
                }
                return $file;
            }, 1000 );
            add_action( 'delete_attachment', [ $this, 'deleteAttachment' ], 1000 );
            add_filter( 'wp_handle_upload', function ( $upload, $context = 'upload' ) {
                $handleUpload = true;
                
                if ( isset( $_FILES['themezip'] ) ) {
                    $fileInfo = pathinfo( $upload['file'] );
                    $handleUpload = $fileInfo['basename'] != $_FILES['themezip']['name'];
                } else {
                    
                    if ( isset( $_FILES['pluginzip'] ) ) {
                        $fileInfo = pathinfo( $upload['file'] );
                        $handleUpload = $fileInfo['basename'] != $_FILES['pluginzip']['name'];
                    }
                
                }
                
                if ( $this->usingImageOptimizer ) {
                    
                    if ( file_is_displayable_image( $upload['file'] ) ) {
                        $handleUpload = false;
                    } else {
                        $this->processingOptimized = true;
                    }
                
                }
                
                if ( !$handleUpload ) {
                    return $upload;
                } else {
                    $result = $this->handleUpload( $upload, $context );
                    remove_filter( 'upload_dir', [ $this, 'getUploadDir' ] );
                    return $result;
                }
            
            }, 10000 );
            add_filter(
                'get_attached_file',
                [ $this, 'getAttachedFile' ],
                10000,
                2
            );
            add_filter(
                'image_downsize',
                [ $this, 'imageDownsize' ],
                999,
                3
            );
            add_action( 'add_attachment', [ $this, 'addAttachment' ], 1000 );
            add_action( 'edit_attachment', [ $this, 'editAttachment' ] );
            add_filter(
                'media-cloud/storage/process-file-name',
                function ( $filename ) {
                if ( !$this->client ) {
                    return $filename;
                }
                if ( strpos( $filename, '/' . $this->client->bucket() ) === 0 ) {
                    return str_replace( '/' . $this->client->bucket(), '', $filename );
                }
                return $filename;
            },
                10000,
                1
            );
            $imgixEnabled = apply_filters( 'media-cloud/imgix/enabled', false );
            if ( !$imgixEnabled ) {
                add_filter( 'wp_image_editors', function ( $editors ) {
                    array_unshift( $editors, '\\MediaCloud\\Plugin\\Tools\\Storage\\StorageImageEditor' );
                    return $editors;
                }, PHP_INT_MAX );
            }
            add_filter(
                'wp_prepare_attachment_for_js',
                [ $this, 'prepareAttachmentForJS' ],
                999,
                3
            );
            add_filter(
                'wp_get_attachment_url',
                [ $this, 'getAttachmentURL' ],
                1000,
                2
            );
            add_filter(
                'theme_mod_header_image',
                [ $this, 'getThemeOptionURL' ],
                1000,
                1
            );
            add_filter(
                'attachment_url_to_postid',
                [ $this, 'attachmentIdFromURL' ],
                1000,
                2
            );
            
            if ( ToolsManager::instance()->toolEnabled( 'debugging' ) ) {
                add_filter(
                    'load_image_to_edit_filesystempath',
                    function ( $filepath, $attachment_id, $size ) {
                    Logger::info(
                        "load_image_to_edit_filesystempath {$filepath} {$attachment_id}",
                        [],
                        __METHOD__,
                        __LINE__
                    );
                    return $filepath;
                },
                    1,
                    3
                );
                add_filter(
                    'load_image_to_edit_attachmenturl',
                    function ( $filepath, $attachment_id, $size ) {
                    Logger::info(
                        "load_image_to_edit_attachmenturl {$filepath} {$attachment_id}",
                        [],
                        __METHOD__,
                        __LINE__
                    );
                    return $filepath;
                },
                    1,
                    3
                );
                add_filter(
                    'load_image_to_edit_path',
                    function ( $filepath, $attachment_id, $size ) {
                    Logger::info(
                        "load_image_to_edit_path {$filepath} {$attachment_id}",
                        [],
                        __METHOD__,
                        __LINE__
                    );
                    return $filepath;
                },
                    1,
                    3
                );
                add_filter(
                    'image_editor_save_pre',
                    function ( $image, $attachment_id ) {
                    Logger::info(
                        "image_editor_save_pre {$attachment_id}",
                        [],
                        __METHOD__,
                        __LINE__
                    );
                    return $image;
                },
                    1,
                    2
                );
            }
            
            $this->hookupUI();
        } else {
            
            if ( !empty($this->client) && $this->client->settingsError() ) {
                $adminUrl = admin_url( 'admin.php?page=media-cloud-settings&tab=storage' );
                $testUrl = admin_url( 'admin.php?page=media-tools-troubleshooter' );
                NoticeManager::instance()->displayAdminNotice( 'error', "Your cloud storage settings are incorrect.  Please <a href='{$adminUrl}'>verify your settings</a> or run the <a href='{$testUrl}'>systems test</a> to troubleshoot the issue." );
            }
        
        }
    
    }
    
    public function settingsChanged()
    {
        $error = empty($this->client);
        if ( !$error ) {
            try {
                $this->client->validateSettings();
            } catch ( StorageException $ex ) {
                $error = true;
            }
        }
        if ( $error ) {
            NoticeManager::instance()->displayAdminNotice( 'error', 'There is a serious issue with your storage settings.  Please check them and try again.' );
        }
        $privacyErrors = [];
        if ( !$this->testPrivacy( 'image' ) ) {
            $privacyErrors[] = 'Privacy for image uploads is set to private, but URL signing for images is not enabled.  Images will display fine in the admin, but appear broken on the front-end.  You should enable URL signing for images.';
        }
        if ( !$this->testPrivacy( 'audio' ) ) {
            $privacyErrors[] = 'Privacy for audio uploads is set to private, but URL signing for audio is not enabled.  Audio will appear and play correctly in the admin, but appear broken on the front-end.  You should enable URL signing for audio.';
        }
        if ( !$this->testPrivacy( 'video' ) ) {
            $privacyErrors[] = 'Privacy for video uploads is set to private, but URL signing for video is not enabled.  Video will appear and play correctly in the admin, but appear broken on the front-end.  You should enable URL signing for video.';
        }
        if ( !$this->testPrivacy( 'application' ) ) {
            $privacyErrors[] = 'Privacy for document uploads is set to private, but URL signing for documents is not enabled.  Documents will display fine in the admin, but appear broken on the front-end.  You should enable URL signing for documents.';
        }
        if ( !empty($privacyErrors) ) {
            NoticeManager::instance()->displayGroupedAdminNotices(
                'warning',
                $privacyErrors,
                true,
                'privacy-errors-url-signing'
            );
        }
    }
    
    private function testPrivacy( $type )
    {
        if ( StorageToolSettings::privacy( $type ) !== 'public-read' && !$this->client->usesSignedURLs( $type ) ) {
            return false;
        }
        return true;
    }
    
    //endregion
    //region Client
    /**
     * The StorageInterface client for this storage tool
     *
     * @return StorageInterface|null
     */
    public function client()
    {
        return $this->client;
    }
    
    //endregion
    public function handleGenerateAttachmentMetadata( $meta, $id, $mode )
    {
        Logger::info(
            "Generate attachment metadata for {$id} for mode {$mode}",
            [],
            __METHOD__,
            __LINE__
        );
        
        if ( !in_array( $id, $this->processed ) ) {
            $this->processed[] = $id;
        } else {
            $this->generated[] = $id;
        }
        
        if ( $this->updateCallCount == 1 ) {
            $this->generated[] = $id;
        }
        
        if ( empty($meta) && get_post_mime_type( $id ) === 'application/pdf' ) {
            $otherMeta = get_post_meta( $id, 'ilab_s3_info', true );
            
            if ( isset( $otherMeta['width'] ) || isset( $otherMeta['sizes'] ) ) {
                $meta = $otherMeta;
                if ( !isset( $otherMeta['sizes'] ) ) {
                    $otherMeta['sizes'] = [];
                }
                
                if ( !isset( $otherMeta['sizes']['full'] ) ) {
                    $otherMeta['sizes']['full'] = [
                        'file'      => $otherMeta['file'],
                        'width'     => $otherMeta['width'],
                        'height'    => $otherMeta['height'],
                        'mime-type' => 'application/pdf',
                    ];
                    if ( isset( $otherMeta['s3'] ) ) {
                        $otherMeta['sizes']['full']['s3'] = $otherMeta['s3'];
                    }
                }
            
            }
        
        }
        
        return $meta;
    }
    
    //region WordPress Upload/Attachment Hooks & Filters
    private function cloudIcon()
    {
        
        if ( $this->cloudIconSVG === null ) {
            $svg = file_get_contents( ILAB_PUB_IMG_DIR . '/ilab-cloud-icon.svg' );
            $this->cloudIconSVG = 'data:image/svg+xml;base64,' . base64_encode( $svg );
        }
        
        return $this->cloudIconSVG;
    }
    
    private function lockIcon()
    {
        
        if ( $this->lockIconSVG === null ) {
            $svg = file_get_contents( ILAB_PUB_IMG_DIR . '/ilab-icon-lock.svg' );
            $this->lockIconSVG = 'data:image/svg+xml;base64,' . base64_encode( $svg );
        }
        
        return $this->lockIconSVG;
    }
    
    public function handleUpdateAttachmentMetadataFor53( $data, $id )
    {
        $this->updateCallCount++;
        Logger::info(
            "Update attachment metadata {$id}",
            [],
            __METHOD__,
            __LINE__
        );
        // Uploads are handled by a different method for non-images
        
        if ( !wp_attachment_is_image( $id ) ) {
            $mimeType = get_post_mime_type( $id );
            
            if ( !isset( $data['sizes'] ) ) {
                Logger::info(
                    "Attachment {$id} - {$mimeType} is not an image",
                    [],
                    __METHOD__,
                    __LINE__
                );
                return $data;
            } else {
                Logger::info(
                    "Attachment {$id} - {$mimeType} has sizes.",
                    [],
                    __METHOD__,
                    __LINE__
                );
            }
        
        }
        
        // This means sizes haven't been generated yet.
        
        if ( !in_array( $id, $this->generated ) && empty($data['sizes']) ) {
            Logger::info(
                "Attachment is missing sizes {$id}",
                [],
                __METHOD__,
                __LINE__
            );
            return $data;
        }
        
        $this->updateCallCount = 0;
        $ewwProcessing = in_array( arrayPath( $_REQUEST, 'action' ), [ 'bulk_loop', 'ewww_bulk_update_meta' ] );
        // In 5.3 `wp_update_attachment_metadata` is called a few times, but we only want to handle the last time its called
        // to prevent uploading stuff twice.
        
        if ( !empty($ewwProcessing) ) {
            add_filter( 'media-cloud/storage/ignore-existing-s3-data', '__return_true' );
            $this->updateCallCount = 1;
        } else {
            
            if ( empty($this->processingOptimized) && !in_array( $id, $this->processed ) && arrayPath( $_REQUEST, 'action' ) != 'image-editor' ) {
                Logger::info(
                    "Attachment hasn't been processed yet.",
                    [],
                    __METHOD__,
                    __LINE__
                );
                $this->processed[] = $id;
                return $data;
            }
        
        }
        
        Logger::info(
            "Processing attachment metadata.",
            [],
            __METHOD__,
            __LINE__
        );
        // Now the goods
        $data = $this->handleUpdateAttachmentMetadata( $data, $id );
        
        if ( $this->settings->uploadOriginal && isset( $data['original_image'] ) ) {
            $s3Data = $this->uploadOriginalImage( $data, $id, $this->preserveFilePaths );
            if ( !empty($s3Data) ) {
                $data['original_image_s3'] = $s3Data;
            }
        }
        
        if ( !empty($ewwProcessing) ) {
            remove_filter( 'media-cloud/storage/ignore-existing-s3-data', '__return_true' );
        }
        return $data;
    }
    
    public function handleUpdateAttachmentMetadata( $data, $id )
    {
        $ignoreOptimizers = apply_filters( 'media-cloud/storage/ignore-optimizers', false, $id );
        if ( $this->usingImageOptimizer && !$this->processingOptimized && !$ignoreOptimizers ) {
            return $data;
        }
        $shouldSkip = apply_filters( 'media-cloud/storage/ignore-metadata-update', false, $id );
        if ( $shouldSkip ) {
            return $data;
        }
        $data = $this->updateAttachmentMetadata( $data, $id, $this->preserveFilePaths );
        $fixedSizes = [];
        $sizesToFix = arrayPath( $data, 'sizes', [] );
        
        if ( count( $sizesToFix ) > 0 ) {
            foreach ( $sizesToFix as $sizeName => $sizeData ) {
                $key = arrayPath( $sizeData, 's3/key' );
                
                if ( !empty($key) ) {
                    $fname = basename( $key );
                    if ( arrayPath( $sizeData, 'file', null ) !== $fname ) {
                        $sizeData['file'] = $fname;
                    }
                }
                
                $fixedSizes[$sizeName] = $sizeData;
            }
            $data['sizes'] = $fixedSizes;
        }
        
        $key = arrayPath( $data, 's3/key', null );
        if ( !empty($key) ) {
            
            if ( arrayPath( $data, 'file', null ) !== $key ) {
                $data['file'] = $key;
                update_post_meta( $id, '_wp_attached_file', $key );
            }
        
        }
        return $data;
    }
    
    /**
     * Filter for when attachments are updated (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/post.php#L5013)
     *
     * @param array $data
     * @param integer $id
     * @param string $preserveFilePaths
     * @param bool $skipThumbnails
     *
     * @return array
     */
    public function uploadOriginalImage(
        $data,
        $id,
        $preserveFilePaths = 'replace',
        $existingPrefix = null
    )
    {
        $path_base = pathinfo( $data['file'] )['dirname'];
        if ( $path_base === '.' ) {
            $path_base = '';
        }
        $originalImage = trailingslashit( $path_base ) . $data['original_image'];
        $mime = get_post_mime_type( $id );
        $upload_info = wp_upload_dir();
        $upload_path = $upload_info['basedir'];
        $newData = [
            'file' => $originalImage,
        ];
        
        if ( $preserveFilePaths === 'replace' ) {
            $upload_path .= DIRECTORY_SEPARATOR . $path_base;
            $newData['prefix'] = $path_base;
            $newData['file'] = trim( str_replace( $path_base, '', $newData['file'] ), DIRECTORY_SEPARATOR );
        }
        
        if ( !file_exists( $upload_path . DIRECTORY_SEPARATOR . $newData['file'] ) ) {
            return [];
        }
        if ( !$mime ) {
            $mime = wp_get_image_mime( $upload_path . DIRECTORY_SEPARATOR . $newData['file'] );
        }
        $newData['mime-type'] = $mime;
        Prefixer::setType( $mime );
        
        if ( $this->client && $this->client->enabled() ) {
            Logger::info(
                "\tProcessing main file {$newData['file']}",
                [],
                __METHOD__,
                __LINE__
            );
            Prefixer::previousVersion();
            $privacy = Environment::Option( 'mcloud-storage-big-size-original-privacy', null, 'private' );
            $doUpload = apply_filters( 'media-cloud/storage/upload-master', true );
            $newData = $this->processFile(
                $upload_path,
                $newData['file'],
                $newData,
                $id,
                $preserveFilePaths,
                $doUpload,
                $privacy,
                $existingPrefix,
                'original'
            );
        }
        
        Prefixer::setType( null );
        Prefixer::nextVersion();
        return ( isset( $newData['s3'] ) ? $newData['s3'] : [] );
    }
    
    /**
     * Filter for when attachments are updated (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/post.php#L5013)
     *
     * @param array $data
     * @param integer $id
     * @param string $preserveFilePaths
     * @param bool $skipThumbnails
     *
     * @return array
     */
    public function updateAttachmentMetadata(
        $data,
        $id,
        $preserveFilePaths = 'replace',
        $skipThumbnails = false,
        $existingPrefix = null
    )
    {
        if ( $this->skipUpdate ) {
            return $data;
        }
        if ( !$data ) {
            return $data;
        }
        global  $media_cloud_licensing ;
        $existingPrefix = ( empty($existingPrefix) ? gen_uuid( 8 ) : $existingPrefix );
        $originalData = $data;
        $imgixEnabled = apply_filters( 'media-cloud/imgix/enabled', false );
        $mime = ( isset( $data['ilab-mime'] ) ? $data['ilab-mime'] : null );
        if ( $mime ) {
            unset( $data['ilab-mime'] );
        }
        $forcedIgnoreExistingS3 = false;
        
        if ( !isset( $data['file'] ) ) {
            if ( !$mime ) {
                $mime = get_post_mime_type( $id );
            }
            
            if ( $mime == 'application/pdf' ) {
                $s3Info = get_post_meta( $id, 'ilab_s3_info', true );
                
                if ( $s3Info ) {
                    $data['file'] = $s3Info['s3']['key'];
                    $data['s3'] = $s3Info['s3'];
                    
                    if ( isset( $this->pdfInfo[$s3Info['file']] ) ) {
                        $pdfInfo = $this->pdfInfo[$s3Info['file']];
                        $data['width'] = $pdfInfo['width'];
                        $data['height'] = $pdfInfo['height'];
                        
                        if ( !empty($imgixEnabled) && empty(arrayPath( $data, 'sizes/full', null )) ) {
                            $data['sizes']['full']['file'] = $s3Info['s3']['key'];
                            $data['sizes']['full']['width'] = $data['width'];
                            $data['sizes']['full']['height'] = $data['height'];
                        }
                    
                    }
                
                }
            
            }
            
            if ( !isset( $data['sizes'] ) || !isset( $data['s3'] ) ) {
                return $data;
            }
            $forcedIgnoreExistingS3 = true;
        }
        
        $upload_info = wp_upload_dir();
        $upload_path = $upload_info['basedir'];
        $path_base = pathinfo( $data['file'], PATHINFO_DIRNAME );
        if ( $path_base === '.' ) {
            $path_base = '';
        }
        $old_path_base = $path_base;
        $old_file = $data['file'];
        
        if ( $preserveFilePaths == 'replace' ) {
            $upload_path .= DIRECTORY_SEPARATOR . $path_base;
            $data['prefix'] = $path_base;
            $data['file'] = trim( str_replace( $path_base, '', $data['file'] ), DIRECTORY_SEPARATOR );
            $upload_info['path'] = str_replace( $upload_info['subdir'], DIRECTORY_SEPARATOR . $path_base, $upload_info['path'] );
            $upload_info['url'] = str_replace( $upload_info['subdir'], DIRECTORY_SEPARATOR . $path_base, $upload_info['url'] );
            $upload_info['subdir'] = '';
            $path_base = '';
        }
        
        $sourceFile = $upload_path . DIRECTORY_SEPARATOR . $data['file'];
        Logger::info(
            "Metadata:" . json_encode( $originalData, JSON_PRETTY_PRINT ),
            [],
            __METHOD__,
            __LINE__
        );
        
        if ( !file_exists( $sourceFile ) ) {
            Logger::error(
                "Missing {$sourceFile}",
                [],
                __METHOD__,
                __LINE__
            );
            return $originalData;
        }
        
        
        if ( is_dir( $sourceFile ) ) {
            Logger::error(
                "{$sourceFile} is directory.  Skipping.",
                [],
                __METHOD__,
                __LINE__
            );
            return $originalData;
        }
        
        $sourceFileSize = filesize( $sourceFile );
        
        if ( filesize( $sourceFile ) < 512 ) {
            Logger::error(
                "File too small {$sourceFile} => {$sourceFileSize} bytes",
                [],
                __METHOD__,
                __LINE__
            );
            return $originalData;
        } else {
            Logger::info(
                "File size {$sourceFile} => {$sourceFileSize} bytes",
                [],
                __METHOD__,
                __LINE__
            );
        }
        
        if ( !$mime ) {
            $mime = wp_get_image_mime( $upload_path . DIRECTORY_SEPARATOR . $data['file'] );
        }
        $ignoreMimeTypes = apply_filters( 'media-cloud/storage/ignore-mime-types', true );
        if ( $mime && $ignoreMimeTypes && StorageToolSettings::mimeTypeIsIgnored( $mime ) ) {
            return $originalData;
        }
        Prefixer::setType( $mime );
        
        if ( $this->client && $this->client->enabled() ) {
            $ignoreExistingS3 = apply_filters( 'media-cloud/storage/ignore-existing-s3-data', $forcedIgnoreExistingS3, $id );
            
            if ( $ignoreExistingS3 || !isset( $data['s3'] ) ) {
                Logger::info(
                    "\tProcessing main file {$data['file']}",
                    [],
                    __METHOD__,
                    __LINE__
                );
                $doUpload = apply_filters( 'media-cloud/storage/upload-master', true );
                if ( empty($forcedIgnoreExistingS3) || !isset( $data['s3'] ) ) {
                    $data = $this->processFile(
                        $upload_path,
                        $data['file'],
                        $data,
                        $id,
                        $preserveFilePaths,
                        $doUpload,
                        null,
                        $existingPrefix,
                        'full'
                    );
                }
                
                if ( $skipThumbnails && isset( $data['sizes'] ) ) {
                    unset( $data['sizes'] );
                } else {
                    
                    if ( isset( $data['sizes'] ) ) {
                        $ignoreExistingS3 = apply_filters( 'media-cloud/storage/ignore-existing-s3-data', false, $id );
                        foreach ( $data['sizes'] as $key => $size ) {
                            if ( !is_array( $size ) ) {
                                continue;
                            }
                            $oldSizeFile = $size['file'];
                            
                            if ( $preserveFilePaths == 'preserve' ) {
                                $size['prefix'] = $old_path_base;
                                $size['file'] = str_replace( $old_path_base, '', $size['file'] );
                            }
                            
                            $file = ltrim( $path_base . '/' . $size['file'], '/' );
                            $sizedFileName = pathinfo( $file, PATHINFO_FILENAME ) . '-scaled.' . pathinfo( $file, PATHINFO_EXTENSION );
                            
                            if ( $file == $data['file'] || $sizedFileName == $data['file'] ) {
                                $size['file'] = $oldSizeFile;
                                unset( $size['prefix'] );
                                $data['sizes'][$key]['s3'] = $data['s3'];
                            } else {
                                Logger::info(
                                    "\tProcessing thumbnail {$size['file']}",
                                    [],
                                    __METHOD__,
                                    __LINE__
                                );
                                $sizeData = $this->processFile(
                                    $upload_path,
                                    $file,
                                    $size,
                                    $id,
                                    $preserveFilePaths,
                                    empty($forcedIgnoreExistingS3),
                                    null,
                                    $existingPrefix,
                                    $key
                                );
                                if ( $ignoreExistingS3 || !isset( $sizeData['s3'] ) ) {
                                    foreach ( $data['sizes'] as $lookKey => $lookData ) {
                                        if ( isset( $lookData['s3'] ) ) {
                                            
                                            if ( $lookData['file'] == $sizeData['file'] ) {
                                                $formats = arrayPath( $sizeData, 's3/formats', [] );
                                                $sizeData['s3'] = $lookData['s3'];
                                                $sizeData['s3']['formats'] = array_merge( arrayPath( $lookData, 's3/formats', [] ), $formats );
                                                break;
                                            }
                                        
                                        }
                                    }
                                }
                                unset( $sizeData['prefix'] );
                                $sizeData['file'] = $oldSizeFile;
                                $data['sizes'][$key] = $sizeData;
                            }
                            
                            if ( $imgixEnabled ) {
                                
                                if ( !ilab_size_is_cropped( $key ) ) {
                                    $w = ( !empty($size['width']) ? $size['width'] : 0 );
                                    $h = ( !empty($size['height']) ? $size['height'] : 0 );
                                    $newSize = sizeToFitSize(
                                        $data['width'],
                                        $data['height'],
                                        $w,
                                        $h
                                    );
                                    $data['sizes'][$key]['height'] = $newSize[1];
                                }
                            
                            }
                        }
                    }
                
                }
                
                
                if ( isset( $data['s3'] ) ) {
                    $data = apply_filters( 'media-cloud/storage/after-upload', $data, $id );
                    $data = apply_filters( 'media-cloud/vision/process-meta', $data, $id );
                }
            
            }
        
        }
        
        unset( $data['prefix'] );
        $data['file'] = $old_file;
        Prefixer::setType( null );
        Prefixer::nextVersion();
        return $data;
    }
    
    /**
     * Filters for when attachments are deleted
     *
     * @param $id
     *
     * @return mixed
     */
    public function deleteAttachment( $id )
    {
        if ( !StorageToolSettings::deleteFromStorage() ) {
            return $id;
        }
        $data = wp_get_attachment_metadata( $id );
        if ( isset( $data['file'] ) && !isset( $data['s3'] ) ) {
            return $id;
        }
        if ( $this->client && $this->client->enabled() ) {
            
            if ( !isset( $data['file'] ) ) {
                $file = get_attached_file( $id );
                
                if ( $file ) {
                    
                    if ( strpos( $file, 'http' ) === 0 ) {
                        $pi = parse_url( $file );
                        $file = trim( $pi['path'], '/' );
                        
                        if ( 0 === strpos( $file, $this->client->bucket() ) ) {
                            $file = substr( $file, strlen( $this->client->bucket() ) ) . '';
                            $file = trim( $file, '/' );
                        }
                    
                    } else {
                        $pi = pathinfo( $file );
                        $upload_info = wp_upload_dir();
                        $upload_path = $upload_info['basedir'];
                        $file = trim( str_replace( $upload_path, '', $pi['dirname'] ), '/' ) . '/' . $pi['basename'];
                    }
                    
                    $this->deleteFile( $file );
                }
            
            } else {
                $deletedFiles = [];
                $deletedFiles[] = $data['s3']['key'];
                $this->deleteFile( $data['s3']['key'] );
                $formats = arrayPath( $data, 's3/formats', [] );
                foreach ( $formats as $format ) {
                    
                    if ( in_array( $format, $deletedFiles ) ) {
                        Logger::info(
                            "File '{$format}' has already been deleted.",
                            [],
                            __METHOD__,
                            __LINE__
                        );
                        continue;
                    }
                    
                    $this->deleteFile( $format );
                    $deletedFiles[] = $format;
                }
                
                if ( isset( $data['sizes'] ) ) {
                    $pathParts = explode( '/', $data['s3']['key'] );
                    array_pop( $pathParts );
                    $path_base = implode( '/', $pathParts );
                    foreach ( $data['sizes'] as $key => $size ) {
                        $file = arrayPath( $size, 's3/key', false );
                        if ( !$file ) {
                            $file = $path_base . '/' . $size['file'];
                        }
                        
                        if ( in_array( $file, $deletedFiles ) ) {
                            Logger::info(
                                "File '{$file}' has already been deleted.",
                                [],
                                __METHOD__,
                                __LINE__
                            );
                            continue;
                        }
                        
                        $this->deleteFile( $file );
                        $deletedFiles[] = $file;
                        $formats = arrayPath( $size, 's3/formats', [] );
                        foreach ( $formats as $format ) {
                            
                            if ( in_array( $format, $deletedFiles ) ) {
                                Logger::info(
                                    "File '{$format}' has already been deleted.",
                                    [],
                                    __METHOD__,
                                    __LINE__
                                );
                                continue;
                            }
                            
                            $this->deleteFile( $format );
                            $deletedFiles[] = $format;
                        }
                    }
                }
            
            }
        
        }
        return $id;
    }
    
    /**
     * Filters the uploads directory data.  (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/functions.php#L1880)
     *
     * @param array $uploads
     * @param string|null $type
     * @return array
     */
    public function getUploadDir( $uploads, $type = null )
    {
        global  $job_manager_upload, $job_manager_uploading_file ;
        if ( !empty($job_manager_upload) && !empty($job_manager_uploading_file) ) {
            return $uploads;
        }
        
        if ( empty(StorageToolSettings::prefixFormat()) ) {
            
            if ( is_multisite() && !is_main_site() ) {
                
                if ( defined( 'UPLOADS' ) ) {
                    $root = trailingslashit( ABSPATH ) . constant( 'UPLOADS' );
                    $rootUrl = rtrim( home_url( '/' . constant( 'UPLOADS' ) ), '/' );
                } else {
                    $root = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads';
                    $rootUrl = rtrim( content_url(), '/' ) . '/uploads';
                }
                
                $sitePrefix = rtrim( str_replace( $root, '', $uploads['basedir'] ), DIRECTORY_SEPARATOR );
                $uploads['siteprefix'] = $sitePrefix;
                $uploads['subdir'] = $sitePrefix . $uploads['subdir'];
                $uploads['basedir'] = $root;
                $uploads['baseurl'] = $rootUrl;
            }
            
            return $uploads;
        }
        
        $lastType = Prefixer::currentType();
        if ( !empty($type) ) {
            Prefixer::setType( $type );
        }
        $prefix = trim( StorageToolSettings::prefix( null ), '/' );
        if ( !empty($type) ) {
            Prefixer::setType( $lastType );
        }
        
        if ( is_multisite() && !is_main_site() ) {
            
            if ( defined( 'UPLOADS' ) ) {
                $root = trailingslashit( ABSPATH ) . constant( 'UPLOADS' );
                $rootUrl = rtrim( home_url( '/' . constant( 'UPLOADS' ) ), '/' );
            } else {
                $root = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads';
                $rootUrl = rtrim( content_url(), '/' ) . '/uploads';
            }
            
            
            if ( !empty($this->settings->keepSubsitePath) ) {
                $sitePrefix = ltrim( str_replace( $root, '', $uploads['basedir'] ), DIRECTORY_SEPARATOR );
                $prefix = $sitePrefix . '/' . $prefix;
                $uploads['siteprefix'] = $sitePrefix;
            }
            
            $uploads['subdir'] = '/' . $prefix;
            $uploads['path'] = $root . '/' . $prefix;
            $uploads['url'] = $rootUrl . '/' . $prefix;
            $uploads['baseurl'] = $rootUrl;
            $uploads['basedir'] = $root;
        } else {
            $uploads['subdir'] = '/' . $prefix;
            $uploads['path'] = $uploads['basedir'] . '/' . $prefix;
            $uploads['url'] = $uploads['baseurl'] . '/' . $prefix;
        }
        
        return $uploads;
    }
    
    private function fileIsDisplayableImage( $file )
    {
        
        if ( function_exists( 'file_is_displayable_image' ) ) {
            return file_is_displayable_image( $file );
        } else {
            $displayable_image_types = [
                IMAGETYPE_GIF,
                IMAGETYPE_JPEG,
                IMAGETYPE_PNG,
                IMAGETYPE_BMP
            ];
            $info = @getimagesize( $file );
            
            if ( empty($info) ) {
                $result = false;
            } else {
                
                if ( !in_array( $info[2], $displayable_image_types ) ) {
                    $result = false;
                } else {
                    $result = true;
                }
            
            }
            
            return apply_filters( 'file_is_displayable_image', $result, $file );
        }
    
    }
    
    private function extractPDFPageSize( $upload_path, $file )
    {
        $didParse = false;
        $pdfWidth = 0;
        $pdfHeight = 0;
        
        if ( !class_exists( '\\WP_Image_Editor_Imagick' ) ) {
            require ABSPATH . 'wp-includes/class-wp-image-editor.php';
            require ABSPATH . 'wp-includes/class-wp-image-editor-imagick.php';
        }
        
        Logger::startTiming(
            "PDF Loading {$file} with File Reader.",
            [],
            __METHOD__,
            __LINE__
        );
        $pdfStream = fopen( $upload_path . '/' . $file, 'rb' );
        while ( true ) {
            $content = fgets( $pdfStream );
            if ( $content === false ) {
                break;
            }
            
            if ( preg_match( '/\\/MediaBox\\s*\\[\\s*([ 0-9.]+)\\s*\\]/m', $content, $matches ) ) {
                $dims = explode( ' ', $matches[1] );
                
                if ( count( $dims ) == 4 ) {
                    $pdfWidth = round( floatval( $dims[2] ) * 4.166666667 );
                    $pdfHeight = round( floatval( $dims[3] ) * 4.166666667 );
                    $didParse = true;
                }
                
                break;
            }
        
        }
        fclose( $pdfStream );
        Logger::endTiming(
            "PDF Loading {$file} with File Reader.",
            [],
            __METHOD__,
            __LINE__
        );
        if ( !$didParse ) {
            
            if ( \WP_Image_Editor_Imagick::test() ) {
                Logger::startTiming(
                    "PDF Loading {$file} with Image Editor.",
                    [],
                    __METHOD__,
                    __LINE__
                );
                $imageEditor = wp_get_image_editor( $upload_path . '/' . $file );
                $sizes = $imageEditor->get_size();
                $pdfWidth = $sizes['width'];
                $pdfHeight = $sizes['height'];
                Logger::endTiming(
                    "PDF Loading {$file} with Image Editor.",
                    [],
                    __METHOD__,
                    __LINE__
                );
                $didParse = true;
            }
        
        }
        //      //&& function_exists('gzuncompress')
        //	    if (!$didParse) {
        //		    $pdfFileSize = filesize($upload_path.'/'.$file);
        //		    $memory = memory_get_usage(true);
        //		    $limit = phpMemoryLimit('32M') * 0.5;
        //
        //		    if ($pdfFileSize <= min(($limit - $memory), 67108864)) {
        //			    set_error_handler(function($errno, $errstr, $errfile, $errline) {
        //				    throw new \Exception($errstr);
        //			    }, E_RECOVERABLE_ERROR);
        //
        //
        //			    try {
        //				    Logger::startTiming("PDF Loading $file with PDFParser.", [], __METHOD__, __LINE__);
        //				    $parser = new Parser();
        //				    $pdf = $parser->parseFile($upload_path.'/'.$file);
        //				    $pages = $pdf->getPages();
        //				    if(count($pages) > 0) {
        //					    $page = $pages[0];
        //					    $details = $page->getDetails();
        //					    if(isset($details['MediaBox'])) {
        //						    $pdfWidth = round(floatval($details['MediaBox'][2]) * 4.166666667);
        //						    $pdfHeight = round(floatval($details['MediaBox'][3]) * 4.166666667);
        //						    $didParse = true;
        //					    } else {
        //						    Logger::info("PDF Loading $file with PDFParser.  Missing MediaBox from the page details.", [], __METHOD__, __LINE__);
        //					    }
        //				    }
        //				    Logger::endTiming("PDF Loading $file with PDFParser.", [], __METHOD__, __LINE__);
        //			    }
        //			    catch(\Exception $ex) {
        //				    Logger::error('PDF Loading $file with PDFParser Error:'.$ex->getMessage(), ['exception' => $ex->getMessage()], __METHOD__, __LINE__);
        //			    }
        //
        //			    restore_error_handler();
        //		    } else {
        //			    Logger::warning("Not enough memory (probably) to parse PDF to get document size.", [], __METHOD__, __LINE__);
        //		    }
        //
        //		    if (!$didParse && class_exists('\Imagick')) {
        //			    try {
        //				    Logger::startTiming("PDF Loading $file with Imagick.", [], __METHOD__, __LINE__);
        //				    $pdfImage = new \Imagick($upload_path.'/'.$file);
        //				    Logger::endTiming("PDF Loading $file with Imagick.", [], __METHOD__, __LINE__);
        //				    Logger::startTiming("PDF Loading $file with Imagick.", [], __METHOD__, __LINE__);
        //				    $pdfWidth = round(floatval($pdfImage->getImageWidth()) * 4.166666667);
        //				    $pdfHeight = round(floatval($pdfImage->getImageHeight()) * 4.166666667);
        //				    Logger::endTiming("PDF Loading $file with Imagick.", [], __METHOD__, __LINE__);
        //			    } catch (\Exception $ex) {
        //				    Logger::error("Error getting size for Imagick: ".$ex->getMessage(), [], __METHOD__, __LINE__);
        //			    }
        //		    }
        //	    }
        Logger::info(
            "PDF Loading {$file} found dimensions: {$pdfWidth}w x {$pdfHeight}h.",
            [],
            __METHOD__,
            __LINE__
        );
        return [ $pdfWidth, $pdfHeight ];
    }
    
    /**
     * Filters the data after a file has been uploaded to WordPress (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-admin/includes/file.php#L416)
     *
     * @param array $upload
     * @param string $context
     *
     * @return array
     */
    public function handleUpload( $upload, $context = 'upload' )
    {
        if ( !isset( $upload['file'] ) ) {
            return $upload;
        }
        $ignoreMimeTypes = apply_filters( 'media-cloud/storage/ignore-mime-types', true );
        if ( isset( $upload['type'] ) && $ignoreMimeTypes && StorageToolSettings::mimeTypeIsIgnored( $upload['type'] ) ) {
            return $upload;
        }
        if ( isset( $_REQUEST["action"] ) && $_REQUEST["action"] == "upload-plugin" ) {
            return $upload;
        }
        $shouldHandleImageUpload = apply_filters( 'media-cloud/storage/should-handle-image-upload', false, $upload );
        if ( empty($shouldHandleImageUpload) && $this->fileIsDisplayableImage( $upload['file'] ) ) {
            return $upload;
        }
        $shouldHandle = apply_filters( 'media-cloud/storage/should-handle-upload', true, $upload );
        if ( !$shouldHandle ) {
            return $upload;
        }
        
        if ( $this->client && $this->client->enabled() ) {
            $pi = pathinfo( $upload['file'] );
            $upload_info = wp_upload_dir();
            $upload_path = $upload_info['basedir'];
            $file = trim( str_replace( $upload_path, '', $pi['dirname'] ), '/' ) . '/' . $pi['basename'];
            if ( !empty($this->settings->extractPDFPageSize) && $upload['type'] == 'application/pdf' && file_exists( $upload_path . '/' . $file ) ) {
                
                if ( !empty($this->settings->extractPDFPageSize) ) {
                    list( $pdfWidth, $pdfHeight ) = $this->extractPDFPageSize( $upload_path, $file );
                    if ( !empty($pdfWidth) && !empty($pdfHeight) ) {
                        $this->pdfInfo[$upload_path . '/' . $file] = $this->pdfInfo[$file] = [
                            'width'  => $pdfWidth,
                            'height' => $pdfHeight,
                        ];
                    }
                }
            
            }
            Prefixer::setType( $upload['type'] );
            $upload = $this->processFile( $upload_path, $file, $upload );
            if ( isset( $upload['s3'] ) ) {
                
                if ( StorageToolSettings::docCdn() ) {
                    $upload['url'] = trim( StorageToolSettings::docCdn(), '/' ) . '/' . $file;
                } else {
                    if ( isset( $upload['s3']['url'] ) ) {
                        $upload['url'] = $upload['s3']['url'];
                    }
                }
            
            }
            Logger::info(
                "Adding {$file} to uploadedDocs",
                [],
                __METHOD__,
                __LINE__
            );
            $this->uploadedDocs[$file] = $upload;
            Prefixer::setType( null );
            Prefixer::nextVersion();
        }
        
        return $upload;
    }
    
    /**
     * Filters the attached file based on the given ID (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/post.php#L293)
     *
     * @param $file
     * @param $attachment_id
     * @return null|string
     * @throws StorageException
     */
    public function getAttachedFile( $file, $attachment_id )
    {
        $shouldOverride = apply_filters( 'media-cloud/storage/should-override-attached-file', true, $attachment_id );
        
        if ( !file_exists( $file ) && $shouldOverride ) {
            $meta = wp_get_attachment_metadata( $attachment_id );
            $new_url = null;
            if ( $meta ) {
                $new_url = $this->getAttachmentURLFromMeta( $meta );
            }
            
            if ( !$new_url ) {
                $meta = get_post_meta( $attachment_id, 'ilab_s3_info', true );
                
                if ( $meta ) {
                    $new_url = $this->getAttachmentURLFromMeta( $meta );
                } else {
                    
                    if ( !$meta && StorageToolSettings::docCdn() ) {
                        $post = \WP_Post::get_instance( $attachment_id );
                        if ( $post && strpos( $post->guid, StorageToolSettings::docCdn() ) === 0 ) {
                            $new_url = $post->guid;
                        }
                    }
                
                }
            
            }
            
            if ( $new_url ) {
                return $new_url;
            }
        }
        
        return $file;
    }
    
    /**
     * Filters whether to preempt the output of image_downsize().  (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/media.php#L201)
     * @param $fail
     * @param $id
     * @param $size
     * @return array
     * @throws StorageException
     */
    public function imageDownsize( $fail, $id, $size )
    {
        if ( apply_filters( 'media-cloud/imgix/enabled', false ) ) {
            return $fail;
        }
        
        if ( empty(apply_filters( 'media-cloud/storage/override-url', true )) ) {
            Logger::info(
                "Override URL is false",
                [
                'fail' => $fail,
                'id'   => $id,
                'size' => $size,
            ],
                __METHOD__,
                __LINE__
            );
            return $fail;
        }
        
        return $this->forcedImageDownsize( $fail, $id, $size );
    }
    
    /**
     * Performs the image downsize regardless if Imgix is enabled or not.
     * @param $fail
     * @param $id
     * @param $size
     * @return array
     * @throws StorageException
     */
    public function forcedImageDownsize( $fail, $id, $size )
    {
        if ( empty(apply_filters( 'media-cloud/storage/override-url', true )) ) {
            return $fail;
        }
        if ( empty($size) || empty($id) || is_array( $size ) ) {
            return $fail;
        }
        $meta = wp_get_attachment_metadata( $id );
        if ( empty($meta) ) {
            return $fail;
        }
        if ( !isset( $meta['sizes'] ) ) {
            return $fail;
        }
        if ( !isset( $meta['sizes'][$size] ) ) {
            return $fail;
        }
        $isOffloadS3 = arrayPath( $meta, 's3/provider', null ) == 'aws';
        $sizeMeta = $meta['sizes'][$size];
        if ( !isset( $sizeMeta['s3'] ) ) {
            
            if ( $isOffloadS3 ) {
                
                if ( $this->fixOffloadS3Meta( $id, $meta ) ) {
                    return $this->forcedImageDownsize( $fail, $id, $size );
                } else {
                    return $fail;
                }
            
            } else {
                return $fail;
            }
        
        }
        $url = $this->getAttachmentURLFromMeta( $sizeMeta );
        // $sizeMeta['s3']['url'];
        $result = [
            $url,
            $sizeMeta['width'],
            $sizeMeta['height'],
            true
        ];
        return $result;
    }
    
    /**
     * Fires once an attachment has been added. (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/post.php#L3457)
     *
     * @param int $post_id
     */
    public function addAttachment( $post_id )
    {
        $file = get_post_meta( $post_id, '_wp_attached_file', true );
        if ( empty($file) ) {
            return;
        }
        $fileKey = $file;
        if ( isset( $this->renamedDocs[$fileKey] ) ) {
            $fileKey = $this->renamedDocs[$file];
        }
        if ( !isset( $this->uploadedDocs[$fileKey] ) ) {
            $fileKey = '/' . $fileKey;
        }
        
        if ( isset( $this->uploadedDocs[$fileKey] ) ) {
            //		    if (isset($this->uploadedDocs[$fileKey]['width'])) {
            //			    add_post_meta($post_id, '_wp_attachment_metadata', $this->uploadedDocs[$fileKey]);
            //            }
            add_post_meta( $post_id, 'ilab_s3_info', $this->uploadedDocs[$fileKey] );
            do_action(
                'media-cloud/storage/uploaded-attachment',
                $post_id,
                $file,
                $this->uploadedDocs[$fileKey]
            );
        } else {
            Logger::info(
                "addAttachment - Missing '{$fileKey}' key on uploadeDocs.",
                [],
                __METHOD__,
                __LINE__
            );
            $keys = array_keys( $this->uploadedDocs );
            $keyList = implode( ' , ', $keys );
            Logger::info(
                'addAttachment - Have keys: ' . $keyList,
                [],
                __METHOD__,
                __LINE__
            );
            // front end upload
            
            if ( !isset( $GLOBALS['current_screen'] ) && !is_customize_preview() ) {
                $uploadDir = wp_get_upload_dir();
                $fullPath = trailingslashit( $uploadDir['basedir'] ) . $file;
                $mimeType = get_post_mime_type( $post_id );
                $data = $this->handleUpload( [
                    'file' => $fullPath,
                    'type' => $mimeType,
                ] );
                
                if ( !empty($data['s3']) ) {
                    add_post_meta( $post_id, 'ilab_s3_info', $data );
                    do_action(
                        'media-cloud/storage/uploaded-attachment',
                        $post_id,
                        $file,
                        $data
                    );
                }
            
            }
        
        }
    
    }
    
    /**
     * Fires once an existing attachment has been updated.  (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/post.php#L3528)
     *
     * @param int $post_id
     */
    public function editAttachment( $post_id )
    {
        $meta = wp_get_attachment_metadata( $post_id );
        
        if ( !isset( $meta['s3'] ) ) {
            $meta = get_post_meta( $post_id, 'ilab_s3_info', true );
            if ( empty($meta) || !isset( $meta['s3'] ) ) {
                return;
            }
            $meta = $this->updateAttachmentS3Props( $post_id, $meta );
            update_post_meta( $post_id, 'ilab_s3_info', $meta );
            return;
        }
        
        $meta = $this->updateAttachmentS3Props( $post_id, $meta );
        wp_update_attachment_metadata( $post_id, $meta );
    }
    
    /**
     * Updates the attachment's properties, as well as updates the metadata on the storage service.
     *
     * @param int $id
     * @param array $meta
     *
     * @return mixed
     */
    private function updateAttachmentS3Props( $id, $meta )
    {
        
        if ( isset( $_POST['s3-access-acl'] ) || isset( $_POST['s3-cache-control'] ) || isset( $_POST['s3-expires'] ) ) {
            $mime = get_post_mime_type( $id );
            $acl = ( isset( $meta['s3']['privacy'] ) ? $meta['s3']['privacy'] : StorageToolSettings::privacy( $mime ) );
            $acl = ( isset( $_POST['s3-access-acl'] ) ? $_POST['s3-access-acl'] : $acl );
            $meta['s3']['privacy'] = $acl;
            $cacheControl = false;
            $expires = false;
            if ( isset( $_POST['s3-cache-control'] ) ) {
                $cacheControl = $_POST['s3-cache-control'];
            }
            
            if ( isset( $_POST['s3-expires'] ) ) {
                $expires = $_POST['s3-expires'];
                
                if ( !empty($expires) ) {
                    
                    if ( !is_numeric( $expires ) ) {
                        $expires = strtotime( $expires ) - time();
                        if ( $expires !== false ) {
                            $expires = round( $expires / 60 );
                        }
                    }
                    
                    if ( $expires !== false && is_numeric( $expires ) ) {
                        $expires = gmdate( 'D, d M Y H:i:00 \\G\\M\\T', time() + $expires * 60 );
                    }
                }
            
            }
            
            try {
                $this->client->copy(
                    $meta['s3']['key'],
                    $meta['s3']['key'],
                    $acl,
                    $mime,
                    $cacheControl,
                    $expires
                );
                
                if ( !empty($cacheControl) ) {
                    if ( !isset( $meta['s3']['options'] ) ) {
                        $meta['s3']['options'] = [];
                    }
                    if ( !isset( $meta['s3']['options']['params'] ) ) {
                        $meta['s3']['options']['params'] = [];
                    }
                    $meta['s3']['options']['params']['CacheControl'] = $cacheControl;
                }
                
                
                if ( !empty($expires) ) {
                    if ( !isset( $meta['s3']['options'] ) ) {
                        $meta['s3']['options'] = [];
                    }
                    if ( !isset( $meta['s3']['options']['params'] ) ) {
                        $meta['s3']['options']['params'] = [];
                    }
                    $meta['s3']['options']['params']['Expires'] = $expires;
                }
                
                if ( isset( $meta['sizes'] ) && !empty($meta['sizes']) ) {
                    foreach ( $meta['sizes'] as $size => $sizeData ) {
                        if ( !isset( $sizeData['s3'] ) ) {
                            continue;
                        }
                        try {
                            $this->client->copy(
                                $sizeData['s3']['key'],
                                $sizeData['s3']['key'],
                                $acl,
                                ( isset( $sizeData['mime-type'] ) ? $sizeData['mime-type'] : $mime ),
                                $cacheControl,
                                $expires
                            );
                            $meta['sizes'][$size]['s3']['privacy'] = $acl;
                        } catch ( StorageException $ex ) {
                            Logger::error(
                                "Error Copying Size {$size}",
                                [
                                'exception' => $ex->getMessage(),
                            ],
                                __METHOD__,
                                __LINE__
                            );
                        }
                    }
                }
            } catch ( StorageException $ex ) {
                Logger::error(
                    'Error Copying Object',
                    [
                    'exception' => $ex->getMessage(),
                ],
                    __METHOD__,
                    __LINE__
                );
            }
        }
        
        return $meta;
    }
    
    /**
     * Filters the attachment data prepared for JavaScript. (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/media.php#L3279)
     *
     * @param array $response
     * @param int|object $attachment
     * @param array $meta
     *
     * @return array
     */
    public function prepareAttachmentForJS( $response, $attachment, $meta )
    {
        if ( empty($meta) || !isset( $meta['s3'] ) ) {
            $meta = get_post_meta( $attachment->ID, 'ilab_s3_info', true );
        }
        if ( isset( $response['filename'] ) && strpos( $response['filename'], '?' ) !== false ) {
            $response['filename'] = preg_replace( '/(\\?.*)/', '', $response['filename'] );
        }
        
        if ( isset( $meta['s3'] ) ) {
            $response['s3'] = $meta['s3'];
            if ( isset( $response['type'] ) && is_admin() ) {
                if ( $this->client()->usesSignedURLs( $response['type'] ) ) {
                    
                    if ( empty($_REQUEST['post_id']) ) {
                        $response['url'] = home_url( '/__mcloud/attachment?pid=' . $attachment->ID . '&nonce=' . wp_create_nonce( 'mcloud_view_attachment_' . $attachment->ID ) );
                    } else {
                        $response['url'] = $this->client()->url( $meta['s3']['key'] );
                    }
                
                }
            }
            if ( !isset( $response['s3']['privacy'] ) ) {
                $response['s3']['privacy'] = StorageToolSettings::privacy( $attachment->post_mime_type );
            }
        }
        
        
        if ( isset( $meta['filesize'] ) ) {
            $bytes = intval( $meta['filesize'] );
            $response['filesizeInBytes'] = $bytes;
            $response['filesizeHumanReadable'] = size_format( $bytes );
        }
        
        $compressionStats = get_post_meta( $attachment->ID, '_mcloud_optimize_stats', true );
        if ( !empty($compressionStats) ) {
            $response['compressionStats'] = [
                'total'      => ( $compressionStats['totalBytes'] == 0 ? '0%' : sprintf( '%.1f%%', $compressionStats['savedBytes'] / $compressionStats['totalBytes'] * 100.0 ) ),
                'totalFiles' => count( $compressionStats['sizes'] ),
            ];
        }
        return $response;
    }
    
    public function getThemeOptionURL( $url )
    {
        if ( !is_string( $url ) || empty($url) ) {
            return $url;
        }
        $uploadDir = wp_get_upload_dir();
        $escapedBase = str_replace( '/', '\\/', $uploadDir['baseurl'] );
        $escapedBase = str_replace( '.', '\\.', $escapedBase );
        $imageRegex = "#{$escapedBase}(.*(jpg|png))#";
        
        if ( preg_match( $imageRegex, $url, $matches ) ) {
            $id = attachment_url_to_postid( $matches[0] );
            
            if ( !empty($id) ) {
                $id = apply_filters(
                    'wpml_object_id',
                    $id,
                    'attachment',
                    true
                );
                $newurl = image_downsize( $id, 'full' );
                if ( is_array( $newurl ) ) {
                    $newurl = $newurl[0];
                }
                $newurl = preg_replace( '/&lang=[aA-zZ0-9]+/m', '', $newurl );
                if ( !empty($newurl) ) {
                    return $newurl;
                }
            }
        
        }
        
        return $url;
    }
    
    /**
     * Filters the attachment's url. (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/post.php#L5077)
     *
     * @param string $url
     * @param int $post_id
     *
     * @return string
     * @throws StorageException
     */
    public function getAttachmentURL( $url, $post_id )
    {
        if ( empty($this->client) ) {
            return $url;
        }
        if ( empty(apply_filters( 'media-cloud/storage/override-url', true )) ) {
            return $url;
        }
        $new_url = null;
        $meta = wp_get_attachment_metadata( $post_id );
        if ( $meta ) {
            $new_url = $this->getAttachmentURLFromMeta( $meta );
        }
        
        if ( empty($new_url) ) {
            $s3Info = get_post_meta( $post_id, 'ilab_s3_info', true );
            if ( !empty($s3Info) ) {
                $new_url = $this->getAttachmentURLFromMeta( $s3Info );
            }
            
            if ( empty($new_url) && empty($this->settings->skipOtherImport) ) {
                $offloadS3Info = $this->loadOffloadMetadata( $post_id );
                
                if ( !empty($offloadS3Info) ) {
                    $new_url = $this->importOffloadMetadata( $post_id, $meta, $offloadS3Info );
                } else {
                    $statelessInfo = get_post_meta( $post_id, 'sm_cloud', true );
                    if ( !empty($statelessInfo) ) {
                        $new_url = $this->importStatelessMetadata( $post_id, $meta, $statelessInfo );
                    }
                }
            
            }
            
            
            if ( !$meta && StorageToolSettings::docCdn() ) {
                $post = \WP_Post::get_instance( $post_id );
                if ( $post && strpos( $post->guid, StorageToolSettings::docCdn() ) === 0 ) {
                    $new_url = $post->guid;
                }
            }
        
        }
        
        if ( !empty($new_url) ) {
            if ( strpos( $new_url, '//s3-.amazonaws' ) !== false ) {
                $new_url = str_replace( '//s3-.amazonaws', '//s3.amazonaws', $new_url );
            }
        }
        return ( $new_url ?: $url );
    }
    
    /**
     * Attempts to get the url based on the S3/Storage metadata
     * @param $meta
     * @return null|string
     * @throws StorageException
     */
    public function getAttachmentURLFromMeta( $meta )
    {
        if ( !isset( $meta['s3'] ) || !isset( $meta['s3']['key'] ) ) {
            return null;
        }
        if ( empty($this->client) ) {
            return null;
        }
        $cdn = apply_filters( 'media-cloud/storage/override-cdn', StorageToolSettings::cdn() );
        $docCdn = apply_filters( 'media-cloud/storage/override-doc-cdn', StorageToolSettings::docCdn() );
        $type = typeFromMeta( $meta );
        $privacy = arrayPath( $meta, 's3/privacy', 'private' );
        $doSign = $this->client->usesSignedURLs( $type ) && $privacy != 'public-read' || $privacy !== 'public-read' && is_admin();
        $doSign = apply_filters( 'media-cloud/storage/sign-url', $doSign );
        $ignoreCDN = apply_filters( 'media-cloud/storage/ignore-cdn', false );
        
        if ( $doSign ) {
            
            if ( $privacy !== 'public-read' && is_admin() ) {
                $url = $this->client->presignedUrl( $meta['s3']['key'], $this->client->signedURLExpirationForType( $type ) );
            } else {
                $url = $this->client->url( $meta['s3']['key'], $type );
            }
            
            
            if ( StorageToolSettings::driver() === 's3' && !empty(StorageToolSettings::signedCDN()) ) {
                return $url;
            } else {
                
                if ( !empty($cdn) && empty($ignoreCDN) ) {
                    $cdnScheme = parse_url( $cdn, PHP_URL_SCHEME );
                    $cdnHost = parse_url( $cdn, PHP_URL_HOST );
                    $urlScheme = parse_url( $url, PHP_URL_SCHEME );
                    $urlHost = parse_url( $url, PHP_URL_HOST );
                    return str_replace( "{$urlScheme}://{$urlHost}", "{$cdnScheme}://{$cdnHost}", $url );
                } else {
                    return $url;
                }
            
            }
        
        } else {
            
            if ( !empty($cdn) && empty($ignoreCDN) ) {
                return $cdn . '/' . $meta['s3']['key'];
            } else {
                
                if ( isset( $meta['s3']['url'] ) ) {
                    
                    if ( isset( $meta['file'] ) && !empty($docCdn) && empty($ignoreCDN) ) {
                        $ext = strtolower( pathinfo( $meta['file'], PATHINFO_EXTENSION ) );
                        $image_exts = array(
                            'jpg',
                            'jpeg',
                            'jpe',
                            'gif',
                            'png'
                        );
                        if ( !in_array( $ext, $image_exts ) ) {
                            return trim( $docCdn, '/' ) . '/' . $meta['s3']['key'];
                        }
                    }
                    
                    $new_url = $meta['s3']['url'];
                    if ( !empty($new_url) ) {
                        if ( strpos( $new_url, '//s3-.amazonaws' ) !== false ) {
                            $new_url = str_replace( '//s3-.amazonaws', '//s3.amazonaws', $new_url );
                        }
                    }
                    return $new_url;
                }
            
            }
            
            try {
                return $this->client->url( $meta['s3']['key'] );
            } catch ( \Exception $ex ) {
                Logger::error(
                    "Error trying to generate url for {$meta['s3']['key']}.  Message:" . $ex->getMessage(),
                    [],
                    __METHOD__,
                    __LINE__
                );
                return null;
            }
        }
    
    }
    
    public function attachmentIdFromURL( $postId, $url )
    {
        return StoragePostMap::attachmentIdFromURL( $postId, $url, $this->client()->bucket() );
    }
    
    //endregion
    //region Crop Tool Related
    /**
     * Processes a file after a crop has been performed, uploading it to storage if it exists
     *
     * @param string $size
     * @param string $upload_path
     * @param string $file
     * @param array $sizeMeta
     *
     * @return array
     */
    public function processCrop(
        $sizeMeta,
        $size,
        $upload_path,
        $file
    )
    {
        $upload_info = wp_upload_dir();
        $subdir = trim( str_replace( $upload_info['basedir'], '', $upload_path ), '/' );
        
        if ( strpos( $upload_info['basedir'], $upload_path ) !== false ) {
            $upload_path = $upload_info['basedir'];
        } else {
            $upload_path = rtrim( substr( $upload_path, 0, strlen( $upload_path ) - strlen( $subdir ) ), '/' );
        }
        
        if ( $this->client && $this->client->enabled() ) {
            $sizeMeta = $this->processFile( $upload_path, $subdir . '/' . $file, $sizeMeta );
        }
        return $sizeMeta;
    }
    
    //endregion
    //region Storage File Processing
    /**
     * Uploads a file to storage and updates the related metadata.
     *
     * @param $upload_path
     * @param $filename
     * @param $data
     * @param null $id
     * @param string $preserveFilePath
     * @param bool $uploadFile
     * @param null $forcedAcl
     * @param null $existingPrefix
     * @param null $currentSize
     *
     * @return array
     * @throws \Exception
     */
    public function processFile(
        $upload_path,
        $filename,
        $data,
        $id = null,
        $preserveFilePath = 'replace',
        $uploadFile = true,
        $forcedAcl = null,
        $existingPrefix = null,
        $currentSize = null
    )
    {
        
        if ( !file_exists( $upload_path . '/' . $filename ) ) {
            if ( !empty(apply_filters( 'media-cloud/storage/report-missing-files', true )) ) {
                Logger::error(
                    "\tFile {$filename} is missing.",
                    [],
                    __METHOD__,
                    __LINE__
                );
            }
            return $data;
        }
        
        $imgixEnabled = apply_filters( 'media-cloud/imgix/enabled', false );
        
        if ( isset( $data['s3'] ) ) {
            $key = $data['s3']['key'];
            if ( $key == $filename ) {
                return $data;
            }
            $this->deleteFile( $key );
        }
        
        global  $media_cloud_licensing ;
        $shouldUseCustomPrefix = apply_filters( 'media-cloud/storage/should-use-custom-prefix', true );
        $shouldUseCustomPrefix = !empty(StorageToolSettings::prefixFormat()) && $shouldUseCustomPrefix;
        
        if ( $preserveFilePath == 'replace' ) {
            
            if ( !isset( $data['prefix'] ) && !$shouldUseCustomPrefix ) {
                $fpath = pathinfo( $data['file'], PATHINFO_DIRNAME );
                $fpath = str_replace( $upload_path, '', $fpath );
                $prefix = trailingslashit( ltrim( $fpath, DIRECTORY_SEPARATOR ) );
                
                if ( $prefix == './' || $prefix == '/' ) {
                    $wpUpload = wp_upload_dir();
                    $prefix = ltrim( trailingslashit( $wpUpload['subdir'] ), '/' );
                }
            
            } else {
                $prefix = trailingslashit( StorageToolSettings::prefix( $id ) );
            }
        
        } else {
            
            if ( $preserveFilePath == 'preserve' ) {
                
                if ( isset( $data['prefix'] ) ) {
                    $prefix = trailingslashit( $data['prefix'] );
                } else {
                    $prefix = trailingslashit( str_replace( basename( $filename ), '', $filename ) );
                }
            
            } else {
                
                if ( $preserveFilePath == 'prepend' ) {
                    
                    if ( isset( $data['prefix'] ) ) {
                        $prefix = trailingslashit( $data['prefix'] );
                    } else {
                        $bn = basename( $filename );
                        $prefix = trailingslashit( str_replace( $bn, '', $filename ) );
                    }
                    
                    $prefix = trailingslashit( StorageToolSettings::prefix( $id ) ) . $prefix;
                }
            
            }
        
        }
        
        
        if ( is_multisite() && !is_main_site() ) {
            $subsiteUploadDir = wp_get_upload_dir();
            if ( !empty($this->settings->keepSubsitePath) && isset( $subsiteUploadDir['siteprefix'] ) ) {
                $prefix = trailingslashit( ltrim( $subsiteUploadDir['siteprefix'] ) ) . $prefix;
            }
        }
        
        if ( $prefix === '/' ) {
            $prefix = '';
        }
        $prefix = apply_filters( 'media-cloud/storage/custom-prefix', $prefix, $preserveFilePath );
        Logger::info(
            "({$preserveFilePath}) Prefix => {$prefix} ",
            [],
            __METHOD__,
            __LINE__
        );
        $parts = explode( '/', $filename );
        $bucketFilename = array_pop( $parts );
        try {
            $url = null;
            // File may already exist on cloud storage, but we'll check that it does first
            $fileExists = $this->client->exists( $prefix . $bucketFilename );
            
            if ( !$uploadFile ) {
                
                if ( !$fileExists ) {
                    $uploadFile = true;
                } else {
                    $url = $this->client->url( $prefix . $bucketFilename );
                }
            
            } else {
                if ( empty($this->settings->overwriteExisting) ) {
                    
                    if ( $fileExists ) {
                        $oldBucketFilename = $bucketFilename;
                        $existingPrefix = ( empty($existingPrefix) ? gen_uuid( 8 ) : $existingPrefix );
                        $bucketFilename = $existingPrefix . '-' . $bucketFilename;
                        $this->renamedDocs[$prefix . $bucketFilename] = $prefix . $oldBucketFilename;
                    }
                
                }
            }
            
            $uploadType = typeFromMeta( $data );
            $privacy = StorageToolSettings::privacy( $uploadType );
            if ( !empty($currentSize) && is_string( $currentSize ) ) {
                $privacy = ImageSizePrivacy::privacyForSize( $currentSize, $privacy );
            }
            if ( !empty($forcedAcl) ) {
                $privacy = $forcedAcl;
            }
            $providerClass = get_class( $this->client );
            $providerId = $providerClass::identifier();
            $privacy = apply_filters(
                'media-cloud/storage/override-privacy',
                $privacy,
                $prefix . $bucketFilename,
                trailingslashit( $upload_path ) . $filename,
                $data
            );
            $didOptimize = false;
            
            if ( $uploadFile || empty($url) ) {
                $skipUpload = false;
                
                if ( !$skipUpload ) {
                    
                    if ( is_dir( $upload_path . '/' . $filename ) ) {
                        Logger::error(
                            "File is a directory: {$upload_path}/{$filename}",
                            [],
                            __METHOD__,
                            __LINE__
                        );
                        return $data;
                    }
                    
                    Logger::info(
                        "\tUploading {$filename} to S3.",
                        [],
                        __METHOD__,
                        __LINE__
                    );
                    $url = $this->client->upload(
                        $prefix . $bucketFilename,
                        $upload_path . '/' . $filename,
                        $privacy,
                        StorageToolSettings::cacheControl(),
                        StorageToolSettings::expires()
                    );
                    Logger::info(
                        "\tFinished uploading {$filename} to S3.",
                        [],
                        __METHOD__,
                        __LINE__
                    );
                }
            
            } else {
                Logger::info(
                    "\tSkipping upload of {$filename} to S3.  Already exists.",
                    [],
                    __METHOD__,
                    __LINE__
                );
            }
            
            $webpKey = null;
            $additionalPaths = apply_filters(
                'as3cf_attachment_file_paths',
                [
                'file' => $upload_path . '/' . $filename,
            ],
                $id,
                $data
            );
            
            if ( isset( $additionalPaths['file-webp'] ) ) {
                $webpPath = $additionalPaths['file-webp'];
                $webpBasename = basename( $webpPath );
                $webpKey = $prefix . $webpBasename;
                
                if ( !$this->client->exists( $webpKey ) ) {
                    Logger::info(
                        "\tUploading {$filename} .webp version to S3",
                        [],
                        __METHOD__,
                        __LINE__
                    );
                    $this->client->upload(
                        $webpKey,
                        $webpPath,
                        $privacy,
                        StorageToolSettings::cacheControl(),
                        StorageToolSettings::expires(),
                        'image/webp'
                    );
                    $canDelete = apply_filters( 'media-cloud/storage/delete_uploads', true );
                    if ( !empty($canDelete) && StorageToolSettings::deleteOnUpload() ) {
                        $this->deleteCache[] = $webpPath;
                    }
                }
            
            }
            
            $options = [];
            $params = [];
            if ( !empty(StorageToolSettings::cacheControl()) ) {
                $params['CacheControl'] = StorageToolSettings::cacheControl();
            }
            if ( !empty(StorageToolSettings::expires()) ) {
                $params['Expires'] = StorageToolSettings::expires();
            }
            if ( !empty($params) ) {
                $options['params'] = $params;
            }
            $data['s3'] = [
                'url'       => $url,
                'bucket'    => $this->client->bucket(),
                'privacy'   => $privacy,
                'key'       => $prefix . $bucketFilename,
                'provider'  => $providerId,
                'v'         => MEDIA_CLOUD_INFO_VERSION,
                'optimized' => $didOptimize,
                'options'   => $options,
                'formats'   => [],
            ];
            if ( !empty($webpKey) ) {
                $data['s3']['formats']['webp'] = $webpKey;
            }
            
            if ( file_exists( $upload_path . '/' . $filename ) ) {
                $ftype = wp_check_filetype( $upload_path . '/' . $filename );
                if ( !empty($ftype) && isset( $ftype['type'] ) ) {
                    $data['s3']['mime-type'] = $ftype['type'];
                }
            }
        
        } catch ( StorageException $ex ) {
            Logger::error(
                'Upload Error',
                [
                'exception'      => $ex->getMessage(),
                'prefix'         => $prefix,
                'bucketFilename' => $bucketFilename,
                'privacy'        => $privacy,
            ],
                __METHOD__,
                __LINE__
            );
        }
        if ( isset( $data['type'] ) && $data['type'] == 'application/pdf' ) {
            
            if ( isset( $data['s3'] ) ) {
                $data['file'] = $data['s3']['key'];
                
                if ( isset( $this->pdfInfo[$data['file']] ) ) {
                    $pdfInfo = $this->pdfInfo[$data['file']];
                    $data['width'] = $pdfInfo['width'];
                    $data['height'] = $pdfInfo['height'];
                    
                    if ( !empty($imgixEnabled) && empty(arrayPath( $data, 'sizes/full', null )) ) {
                        $data['sizes']['full']['file'] = $data['s3']['key'];
                        $data['sizes']['full']['width'] = $data['width'];
                        $data['sizes']['full']['height'] = $data['height'];
                    }
                
                }
            
            }
        
        }
        $canDelete = apply_filters( 'media-cloud/storage/delete_uploads', true );
        if ( !empty($canDelete) ) {
            if ( $uploadType == 'image' && !empty(ToolsManager::instance()->toolEnabled( 'optimizer' )) ) {
                $canDelete = false;
            }
        }
        if ( !empty($canDelete) && StorageToolSettings::deleteOnUpload() ) {
            
            if ( file_exists( $upload_path . '/' . $filename ) ) {
                $fileToDelete = $upload_path . '/' . $filename;
                Logger::info(
                    "StorageTool::processFile - Adding {$fileToDelete} to delete cache.",
                    [],
                    __METHOD__,
                    __LINE__
                );
                $this->deleteCache[] = $fileToDelete;
                //unlink($fileToDelete);
            }
        
        }
        return $data;
    }
    
    public function hookDieHandler( $handler )
    {
        $this->dieHandler = $handler;
        return [ $this, 'cleanUploads' ];
    }
    
    protected function doCleanUploads()
    {
        
        if ( count( $this->deleteCache ) > 0 ) {
            Logger::info(
                "Cleaning uploads ...",
                [],
                __METHOD__,
                __LINE__
            );
            
            if ( StorageToolSettings::queuedDeletes() ) {
                $task = TaskSchedule::nextScheduledTaskOfType( DeleteUploadsTask::identifier() );
                
                if ( !empty($task) ) {
                    $task->selection = array_merge( $task->selection, $this->deleteCache );
                    $task->save();
                } else {
                    DeleteUploadsTask::scheduleIn( StorageToolSettings::queuedDeletesDelay(), [], $this->deleteCache );
                }
            
            } else {
                foreach ( $this->deleteCache as $file ) {
                    
                    if ( file_exists( $file ) ) {
                        
                        if ( @unlink( $file ) ) {
                            Logger::info(
                                "Deleted {$file}",
                                [],
                                __METHOD__,
                                __LINE__
                            );
                        } else {
                            Logger::info(
                                "Error deleting {$file}",
                                [],
                                __METHOD__,
                                __LINE__
                            );
                        }
                    
                    } else {
                        Logger::info(
                            "Skipping missing file: {$file}",
                            [],
                            __METHOD__,
                            __LINE__
                        );
                    }
                
                }
            }
            
            $this->deleteCache = [];
        }
    
    }
    
    public function cleanUploads( $message, $title = '', $args = array() )
    {
        $this->doCleanUploads();
        call_user_func(
            $this->dieHandler,
            $message,
            $title,
            $args
        );
    }
    
    /**
     * Deletes a file from storage
     *
     * @param $file
     */
    private function deleteFile( $file )
    {
        try {
            
            if ( $this->client && $this->client->enabled() ) {
                Logger::info(
                    "StorageTool::deleteFile - Delete {$file} from storage.",
                    [],
                    __METHOD__,
                    __LINE__
                );
                $this->client->delete( $file );
            }
        
        } catch ( StorageException $ex ) {
            Logger::error(
                "StorageTool::deleteFile - Error deleting file '{$file}'.",
                [
                'exception' => $ex->getMessage(),
                'Key'       => $file,
            ],
                __METHOD__,
                __LINE__
            );
        }
    }
    
    //endregion
    //region WordPress UI Hooks
    /**
     * Hooks into the WordPress UI in various ways.
     */
    private function hookupUI()
    {
        $this->hookAttachmentDetails();
        $this->hookMediaList();
        $this->hookStorageInfoMetabox();
        $this->hookMediaGrid();
    }
    
    /**
     * Displays storage info in the attachment details pop up.
     */
    private function hookAttachmentDetails()
    {
        add_action( 'wp_enqueue_media', function () {
            add_action( 'admin_footer', function () {
                $appendedTemplate = <<<TEMPLATE
<# if ( data.compressionStats) { #>
    <div><strong>Average Optimization:</strong> {{data.compressionStats.total}} ({{data.compressionStats.totalFiles}} files)</div>
<# } #>
<# if ( data.s3 ) { #>
    <div><strong>Bucket:</strong> {{data.s3.bucket}}</div>
    <div><strong>Path:</strong> {{data.s3.key}}</div>
    <div><strong>Access:</strong> {{data.s3.privacy}}</div>
    <# if ( data.s3.options && data.s3.options.params ) { #>
        <# if (data.s3.options.params.CacheControl) { #>
        <div><strong>S3 Cache-Control:</strong> {{data.s3.options.params.CacheControl}}</div>
        <# } #>
        <# if (data.s3.options.params.Expires) { #>
        <div><strong>S3 Expires:</strong> {{data.s3.options.params.Expires}}</div>
        <# } #>
    <# } #>
    <div><a href="{{data.s3.url}}" target="_blank">Original Storage URL</a></div>
<# } #>
TEMPLATE;
                ?>
                <script>
                    jQuery(document).ready(function () {
                        var attachTemplate = jQuery('#tmpl-attachment-details-two-column');
                        if (attachTemplate) {
                            var txt = attachTemplate.text();
                            var idx = txt.indexOf('<div class="compat-meta">');
                            txt = txt.slice(0, idx) + '<?php 
                echo  str_replace( "\n", "", $appendedTemplate ) ;
                ?>' + txt.slice(idx);
                            attachTemplate.text(txt);
                        }
                    });
                </script>
				<?php 
            } );
            wp_enqueue_script(
                'ilab-media-grid-js',
                ILAB_PUB_JS_URL . '/ilab-media-grid.js',
                [ 'jquery' ],
                MEDIA_CLOUD_VERSION,
                true
            );
        } );
    }
    
    /**
     * Adds a custom column to the media list.
     */
    private function hookMediaList()
    {
        if ( !$this->settings->mediaListIntegration ) {
            return;
        }
        add_action( 'admin_init', function () {
            add_filter( 'manage_media_columns', function ( $cols ) {
                $cols["cloud"] = 'Cloud';
                return $cols;
            } );
            add_action(
                'manage_media_custom_column',
                function ( $column_name, $id ) {
                
                if ( $column_name == "cloud" ) {
                    $meta = wp_get_attachment_metadata( $id );
                    if ( empty($meta) && !isset( $meta['s3'] ) ) {
                        $meta = get_post_meta( $id, 'ilab_s3_info', true );
                    }
                    
                    if ( !empty($meta) && isset( $meta['s3'] ) ) {
                        $privacy = arrayPath( $meta, 's3/privacy', null );
                        $mimeType = ( isset( $meta['s3']['mime-type'] ) ? $meta['s3']['mime-type'] : '' );
                        $cloudIcon = $this->cloudIcon();
                        //ILAB_PUB_IMG_URL.'/ilab-cloud-icon.svg';
                        $lockIcon = $this->lockIcon();
                        //ILAB_PUB_IMG_URL.'/ilab-icon-lock.svg';
                        $lockImg = ( !empty($privacy) && $privacy !== StorageConstants::ACL_PUBLIC_READ ? "<img class='mcloud-lock' src='{$lockIcon}' height='22'>" : '' );
                        echo  "<a class='media-cloud-info-link' data-post-id='{$id}' data-container='list' data-mime-type='{$mimeType}' href='" . $meta['s3']['url'] . "' target=_blank><img src='{$cloudIcon}' width='24'>{$lockImg}</a>" ;
                    }
                
                }
            
            },
                10,
                2
            );
            add_filter( 'bulk_actions-upload', function ( $actions ) {
                
                if ( $this->client()->canUpdateACL() ) {
                    $actions['mcloud-make-private'] = "Change Privacy to Private";
                    $actions['mcloud-make-authenticated-read'] = "Change Privacy to Authenticated Read";
                    $actions['mcloud-make-public'] = "Change Privacy to Public";
                }
                
                return $actions;
            } );
            add_filter(
                'handle_bulk_actions-upload',
                function ( $redirect_to, $action_name, $post_ids ) {
                
                if ( in_array( $action_name, [ 'mcloud-make-private', 'mcloud-make-public' ] ) ) {
                    
                    if ( $action_name === 'mcloud-make-private' ) {
                        $privacy = StorageConstants::ACL_PRIVATE;
                    } else {
                        
                        if ( $action_name === 'mcloud-make-authenticated-read' ) {
                            $privacy = StorageConstants::ACL_AUTHENTICATED_READ;
                        } else {
                            $privacy = StorageConstants::ACL_PUBLIC_READ;
                        }
                    
                    }
                    
                    foreach ( $post_ids as $postId ) {
                        $attachmentMeta = true;
                        $updated = false;
                        $meta = wp_get_attachment_metadata( $postId );
                        
                        if ( empty($meta) || !isset( $meta['s3'] ) ) {
                            $attachmentMeta = false;
                            $meta = get_post_meta( $postId, 'ilab_s3_info', true );
                            if ( empty($meta) ) {
                                continue;
                            }
                        }
                        
                        $key = arrayPath( $meta, 's3/key', null );
                        
                        if ( !empty($key) ) {
                            $updated = true;
                            $this->client()->updateACL( $key, $privacy );
                            $meta['s3']['privacy'] = $privacy;
                        }
                        
                        $sizes = arrayPath( $meta, 'sizes', [] );
                        foreach ( $sizes as $sizeKey => $sizeData ) {
                            $key = arrayPath( $sizeData, 's3/key', null );
                            
                            if ( !empty($key) ) {
                                $updated = true;
                                $this->client()->updateACL( $key, $privacy );
                                $meta['sizes'][$sizeKey]['s3']['privacy'] = $privacy;
                            }
                        
                        }
                        if ( $updated ) {
                            
                            if ( $attachmentMeta ) {
                                update_post_meta( $postId, '_wp_attachment_metadata', $meta );
                            } else {
                                update_post_meta( $postId, 'ilab_s3_info', $meta );
                            }
                        
                        }
                    }
                }
                
                return $redirect_to;
            },
                1000,
                3
            );
        } );
        add_action( 'wp_enqueue_media', function () {
            add_action( 'admin_head', function () {
                if ( get_current_screen()->base == 'upload' ) {
                    ?>
                    <style>
                        th.column-cloud, td.column-cloud {
                            width: 60px !important;
                            max-width: 60px !important;
                            text-align: center;
                        }
                    </style>
					<?php 
                }
            } );
        } );
        add_action( 'restrict_manage_posts', function () {
            $scr = get_current_screen();
            if ( $scr->base !== 'upload' ) {
                return;
            }
            $selected = ( isset( $_REQUEST['cloud_status'] ) ? $_REQUEST['cloud_status'] : '' );
            ?>
            <select id="cloud-status" name="cloud_status">
                <option>Any Cloud Status</option>
                <option value="uploaded" <?php 
            echo  ( $selected == 'uploaded' ? 'selected' : '' ) ;
            ?>>Uploaded</option>
                <option value="not-uploaded" <?php 
            echo  ( $selected == 'not-uploaded' ? 'selected' : '' ) ;
            ?>>Not Uploaded</option>
            </select>
            <?php 
            $selected = ( isset( $_REQUEST['detected_faces'] ) ? $_REQUEST['detected_faces'] : '' );
            ?>
            <select id="detected-faces" name="detected_faces">
                <option>Any Faces</option>
                <option value="has-faces" <?php 
            echo  ( $selected == 'has-faces' ? 'selected' : '' ) ;
            ?>>Faces Detected</option>
                <option value="no-faces" <?php 
            echo  ( $selected == 'no-faces' ? 'selected' : '' ) ;
            ?>>No Faces Detected</option>
            </select>
			<?php 
            $selected = ( isset( $_REQUEST['cloud_privacy'] ) ? $_REQUEST['cloud_privacy'] : '' );
            ?>
            <select id="cloud-privacy" name="cloud_privacy">
                <option>Any Privacy</option>
                <option value="public-read" <?php 
            echo  ( $selected == 'public-read' ? 'selected' : '' ) ;
            ?>>Public</option>
                <option value="authenticated-read" <?php 
            echo  ( $selected == 'authenticated-read' ? 'selected' : '' ) ;
            ?>>Authenticated Read</option>
                <option value="private" <?php 
            echo  ( $selected == 'private' ? 'selected' : '' ) ;
            ?>>Private</option>
            </select>
			<?php 
        } );
        add_action( 'pre_get_posts', function ( $query ) {
            /** @var \WP_Query $query */
            if ( !is_admin() || !isset( $_REQUEST['cloud_status'] ) || !isset( $_REQUEST['detected_faces'] ) || !isset( $_REQUEST['cloud_privacy'] ) || !$query->is_main_query() ) {
                return;
            }
            $meta_query = [
                'relation' => 'and',
            ];
            
            if ( $_REQUEST['cloud_status'] == 'uploaded' ) {
                $meta_query[] = [
                    'relation' => 'OR',
                    [
                    'key'     => '_wp_attachment_metadata',
                    'value'   => '"s3"',
                    'compare' => 'LIKE',
                    'type'    => 'CHAR',
                ],
                    [
                    'key'     => 'ilab_s3_info',
                    'compare' => 'EXISTS',
                ],
                ];
            } else {
                if ( $_REQUEST['cloud_status'] == 'not-uploaded' ) {
                    $meta_query[] = [
                        'relation' => 'AND',
                        [
                        'key'     => '_wp_attachment_metadata',
                        'value'   => '"s3"',
                        'compare' => 'NOT LIKE',
                        'type'    => 'CHAR',
                    ],
                        [
                        'key'     => 'ilab_s3_info',
                        'compare' => 'NOT EXISTS',
                    ],
                    ];
                }
            }
            
            
            if ( $_REQUEST['detected_faces'] == 'has-faces' ) {
                $meta_query[] = [ [
                    'key'     => '_wp_attachment_metadata',
                    'value'   => '"faces"',
                    'compare' => 'LIKE',
                    'type'    => 'CHAR',
                ] ];
            } else {
                if ( $_REQUEST['detected_faces'] == 'no-faces' ) {
                    $meta_query[] = [ [
                        'key'     => '_wp_attachment_metadata',
                        'value'   => '"faces"',
                        'compare' => 'NOT LIKE',
                        'type'    => 'CHAR',
                    ] ];
                }
            }
            
            
            if ( $_REQUEST['cloud_privacy'] == 'public-read' ) {
                $meta_query[] = [
                    'relation' => 'OR',
                    [
                    'key'     => '_wp_attachment_metadata',
                    'value'   => '"public-read"',
                    'compare' => 'LIKE',
                    'type'    => 'CHAR',
                ],
                    [
                    'key'     => 'ilab_s3_info',
                    'value'   => '"public-read"',
                    'compare' => 'LIKE',
                    'type'    => 'CHAR',
                ],
                ];
            } else {
                
                if ( $_REQUEST['cloud_privacy'] == 'authenticated-read' ) {
                    $meta_query[] = [
                        'relation' => 'OR',
                        [
                        'key'     => '_wp_attachment_metadata',
                        'value'   => '"authenticated-read"',
                        'compare' => 'LIKE',
                        'type'    => 'CHAR',
                    ],
                        [
                        'key'     => 'ilab_s3_info',
                        'value'   => '"authenticated-read"',
                        'compare' => 'LIKE',
                        'type'    => 'CHAR',
                    ],
                    ];
                } else {
                    if ( $_REQUEST['cloud_privacy'] == 'private' ) {
                        $meta_query[] = [
                            'relation' => 'OR',
                            [
                            'key'     => '_wp_attachment_metadata',
                            'value'   => '"private"',
                            'compare' => 'LIKE',
                            'type'    => 'CHAR',
                        ],
                            [
                            'key'     => 'ilab_s3_info',
                            'value'   => '"private"',
                            'compare' => 'LIKE',
                            'type'    => 'CHAR',
                        ],
                        ];
                    }
                }
            
            }
            
            if ( count( $meta_query ) > 1 ) {
                $query->set( 'meta_query', $meta_query );
            }
        } );
    }
    
    /**
     * Displays a cloud icon on items in the media grid.
     */
    private function hookMediaGrid()
    {
        if ( !$this->settings->displayBadges ) {
            return;
        }
        add_action( 'wp_ajax_ilab_regenerate_thumbnails_manual', [ $this, 'handleRegenerateFile' ] );
        add_action( 'wp_ajax_ilab_s3_get_media_info', [ $this, 'getMediaInfo' ] );
        add_action( 'admin_head', function () {
            ?>
            <style>
                .mcloud-grid-lock {
                    display: none;
                    position: absolute;
                    right: 34px;
                    bottom: 5px;
                    z-index: 5;
                }

                .ilab-s3-logo {
                    display: none;
                    position: absolute;
                    right: 5px;
                    bottom: 5px;
                    z-index: 5;
                }

                .has-s3 > .ilab-s3-logo {
                    display: block;
                }

                .has-s3 > .mcloud-grid-lock {
                    display: block;
                }
            </style>
			<?php 
        } );
        add_action( 'admin_footer', function () {
            $additionalClasses = apply_filters( 'media-cloud/media-library/attachment-classes', '' );
            $additionalIcons = apply_filters( 'media-cloud/media-library/attachment-icons', '' );
            ?>
            <script>
                jQuery(document).ready(function () {
                    var attachTemplate = jQuery('#tmpl-attachment');
                    if (attachTemplate) {
                        var txt = attachTemplate.text();

                        var search = '<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }}">';
                        var replace = '<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }} <# if (data.hasOwnProperty("s3")) {#>has-s3<#}#> <?php 
            echo  $additionalClasses ;
            ?>"><?php 
            echo  $additionalIcons ;
            ?><img data-post-id="{{data.id}}" data-container="grid" data-mime-type="{{data.type}}" src="<?php 
            echo  $this->cloudIcon() ;
            ?>" width="29" height="18" class="ilab-s3-logo"><# if (data.hasOwnProperty("s3") && (data.s3.privacy!="public-read")) {#><img src="<?php 
            echo  $this->lockIcon() ;
            ?>" height="18" class="mcloud-grid-lock"><#}#>\n';
                        txt = txt.replace(search, replace);
                        attachTemplate.text(txt);
                    }

                    var attachTemplate = jQuery('#tmpl-attachment-grid-view');
                    if (attachTemplate) {
                        var txt = attachTemplate.text();

                        var search = '<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }}">';
                        var replace = '<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }} <# if (data.hasOwnProperty("s3")) {#>has-s3<#}#> <?php 
            echo  $additionalClasses ;
            ?>"><?php 
            echo  $additionalIcons ;
            ?><img data-post-id="{{data.id}}" data-container="grid" data-mime-type="{{data.type}}" src="<?php 
            echo  $this->cloudIcon() ;
            ?>" width="29" height="18" class="ilab-s3-logo"><# if (data.hasOwnProperty("s3") && (data.s3.privacy!="public-read")) {#><img src="<?php 
            echo  $this->lockIcon() ;
            ?>" height="18" class="mcloud-grid-lock"><#}#>\n';
                        txt = txt.replace(search, replace);
                        attachTemplate.text(txt);
                    }
                });
            </script>
			<?php 
        } );
    }
    
    /**
     * Adds the Cloud Storage metabox on attachment edit pages.
     */
    private function hookStorageInfoMetabox()
    {
        add_action( 'admin_init', function () {
            add_meta_box(
                'ilab-s3-info-meta',
                'Cloud Storage Info',
                [ $this, 'renderStorageInfoMeta' ],
                'attachment',
                'side',
                'low'
            );
            wp_enqueue_script(
                'ilab-media-grid-js',
                ILAB_PUB_JS_URL . '/ilab-media-grid.js',
                [ 'jquery' ],
                MEDIA_CLOUD_VERSION,
                true
            );
        } );
    }
    
    public function getMediaInfo()
    {
        if ( !is_admin() ) {
            die;
        }
        if ( !isset( $_POST['id'] ) ) {
            die;
        }
        $this->doRenderStorageInfoMeta( $_POST['id'], true );
        die;
    }
    
    /**
     * @param \WP_Post $post
     */
    public function renderStorageInfoMeta( $post )
    {
        $this->doRenderStorageInfoMeta( $post->ID );
    }
    
    /**
     * Renders the Cloud Storage metabox
     * @param $postId
     * @param $readOnly
     */
    private function doRenderStorageInfoMeta( $postId = null, $readOnly = false )
    {
        global  $post ;
        if ( empty($postId) ) {
            $postId = $post->ID;
        }
        $meta = wp_get_attachment_metadata( $postId );
        $blogSwitched = false;
        
        if ( empty($meta) && is_multisite() ) {
            $siteId = apply_filters( 'global_media.site_id', false );
            
            if ( $siteId !== false ) {
                $postId = str_replace( "{$siteId}00000", "", $postId );
                switch_to_blog( $siteId );
                $meta = wp_get_attachment_metadata( $postId );
                $blogSwitched = true;
            }
        
        }
        
        if ( empty($meta['s3']) ) {
            $meta = get_post_meta( $postId, 'ilab_s3_info', true );
        }
        
        if ( empty($meta) || empty($meta['s3']) ) {
            if ( $blogSwitched ) {
                restore_current_blog();
            }
            echo  'Not uploaded.' ;
            return;
        }
        
        $type = arrayPath( $meta, 'type', false );
        if ( empty($type) ) {
            $type = get_post_mime_type( $postId );
        }
        
        if ( strpos( $type, 'image' ) === 0 ) {
            $this->doRenderStoreageInfoMetaImage( $postId, $meta, $readOnly );
        } else {
            $this->doRenderStorageinfoMetaDocument( $postId, $meta, $readOnly );
        }
        
        if ( $blogSwitched ) {
            restore_current_blog();
        }
    }
    
    private function doRenderStorageinfoMetaDocument( $postId, $meta, $readOnly )
    {
        $type = arrayPath( $meta, 'type', false );
        if ( empty($type) ) {
            $type = get_post_mime_type( $postId );
        }
        $providerClass = get_class( $this->client );
        $providerId = $providerClass::identifier();
        $enabled = $this->enabled() && arrayPath( $meta, 's3/provider', false ) == $providerId;
        $clientClass = get_class( $this->client );
        $uploadDriverId = arrayPath( $meta, 's3/provider', $clientClass::identifier() );
        $uploadDriver = StorageToolSettings::driverClass( $uploadDriverId );
        $bucket = arrayPath( $meta, 's3/bucket', null );
        $key = arrayPath( $meta, 's3/key', null );
        $privacy = arrayPath( $meta, 's3/privacy', 'public-read' );
        $cacheControl = arrayPath( $meta, 's3/options/params/CacheControl', null );
        $expires = arrayPath( $meta, 's3/options/params/Expires', null );
        $url = arrayPath( $meta, 's3/url', null );
        $publicUrl = wp_get_attachment_url( $postId );
        //$this->getAttachmentURLFromMeta($meta);
        $data = [
            'uploaded'     => 1,
            'type'         => $type,
            'enabled'      => $enabled,
            'postId'       => $postId,
            'bucket'       => $bucket,
            'key'          => $key,
            'readOnly'     => $readOnly,
            'privacy'      => $privacy,
            'cacheControl' => $cacheControl,
            'expires'      => $expires,
            'url'          => $url,
            'optimization' => false,
            'publicUrl'    => $publicUrl,
            'driverName'   => $uploadDriver::name(),
            'bucketLink'   => $uploadDriver::bucketLink( $bucket ),
            'pathLink'     => $this->client->pathLink( $bucket, $key ),
        ];
        echo  View::render_view( 'storage/document-info-panel.php', $data ) ;
    }
    
    private function doRenderStoreageInfoMetaImage( $postId, $meta, $readOnly )
    {
        $imgixEnabled = apply_filters( 'media-cloud/imgix/enabled', false );
        $providerClass = get_class( $this->client );
        $providerId = $providerClass::identifier();
        $enabled = $this->enabled() && arrayPath( $meta, 's3/provider', false ) == $providerId;
        $clientClass = get_class( $this->client );
        $uploadDriverId = arrayPath( $meta, 's3/provider', $clientClass::identifier() );
        if ( $uploadDriverId == 'aws' ) {
            $uploadDriverId = 's3';
        }
        $uploadDriver = StorageToolSettings::driverClass( $uploadDriverId );
        $bucket = arrayPath( $meta, 's3/bucket', null );
        $key = arrayPath( $meta, 's3/key', null );
        $privacy = arrayPath( $meta, 's3/privacy', 'public-read' );
        $cacheControl = arrayPath( $meta, 's3/options/params/CacheControl', null );
        $expires = arrayPath( $meta, 's3/options/params/Expires', null );
        $url = arrayPath( $meta, 's3/url', null );
        $publicUrl = wp_get_attachment_url( $postId );
        //$this->getAttachmentURLFromMeta($meta);
        $optimInfo = get_post_meta( $postId, '_mcloud_optimize_stats', true );
        $sizes = [];
        if ( $meta['sizes'] ) {
            foreach ( $meta['sizes'] as $sizeKey => $size ) {
                $sizeData = [];
                $sizeData['uploaded'] = isset( $size['s3'] );
                $sizeData['enabled'] = $enabled;
                $sizeData['postId'] = $postId;
                $sizeData['readOnly'] = $readOnly;
                $sizeData['name'] = ucwords( str_replace( '_', ' ', str_replace( '-', ' ', $sizeKey ) ) );
                $sizeData['bucket'] = arrayPath( $size, 's3/bucket', null );
                $sizeData['key'] = arrayPath( $size, 's3/key', null );
                $sizeData['privacy'] = arrayPath( $size, 's3/privacy', 'public-read' );
                $sizeData['cacheControl'] = arrayPath( $size, 's3/options/params/CacheControl', null );
                $sizeData['expires'] = arrayPath( $size, 's3/options/params/Expires', null );
                $sizeData['url'] = arrayPath( $size, 's3/url', null );
                $sizeData['width'] = arrayPath( $size, 'width', 0 );
                $sizeData['height'] = arrayPath( $size, 'height', 0 );
                $sizeData['driverName'] = $uploadDriver::name();
                $sizeData['bucketLink'] = $uploadDriver::bucketLink( $sizeData['bucket'] );
                $sizeData['isSize'] = 1;
                $sizeData['pathLink'] = $this->client->pathLink( $sizeData['bucket'], $sizeData['key'] );
                $sizeData['imgixEnabled'] = $imgixEnabled;
                
                if ( isset( $optimInfo['sizes'][$sizeKey] ) ) {
                    $sizeData['optimization'] = $optimInfo['sizes'][$sizeKey];
                } else {
                    $sizeData['optimization'] = false;
                }
                
                $result = wp_get_attachment_image_src( $postId, $sizeKey );
                
                if ( $result && is_array( $result ) && count( $result ) > 0 ) {
                    $sizeData['publicUrl'] = $result[0];
                } else {
                    $sizeData['publicUrl'] = $this->getAttachmentURLFromMeta( $size );
                }
                
                $sizes[$sizeKey] = $sizeData;
            }
        }
        $missingSizes = [];
        if ( $this->allSizes == null ) {
            $this->allSizes = ilab_get_image_sizes();
        }
        foreach ( $this->allSizes as $wpSizeKey => $wpSize ) {
            if ( !isset( $sizes[$wpSizeKey] ) ) {
                $missingSizes[$wpSizeKey] = ucwords( str_replace( '_', ' ', str_replace( '-', ' ', $wpSizeKey ) ) );
            }
        }
        $data = [
            'uploaded'     => 1,
            'enabled'      => $enabled,
            'postId'       => $postId,
            'bucket'       => $bucket,
            'key'          => $key,
            'readOnly'     => $readOnly,
            'privacy'      => $privacy,
            'cacheControl' => $cacheControl,
            'expires'      => $expires,
            'url'          => $url,
            'publicUrl'    => $publicUrl,
            'width'        => $meta['width'],
            'height'       => $meta['height'],
            'driverName'   => $uploadDriver::name(),
            'bucketLink'   => $uploadDriver::bucketLink( $bucket ),
            'pathLink'     => $this->client->pathLink( $bucket, $key ),
            'imgixEnabled' => $imgixEnabled,
            'optimization' => ( isset( $optimInfo['sizes']['full'] ) ? $optimInfo['sizes']['full'] : false ),
            'sizes'        => $sizes,
            'missingSizes' => $missingSizes,
        ];
        echo  View::render_view( 'storage/info-panel.php', $data ) ;
    }
    
    //endregion
    //region Regeneration
    private function loadImageToEditPath( $attachment_id, $size = 'full' )
    {
        $filepath = get_attached_file( $attachment_id );
        
        if ( $filepath && file_exists( $filepath ) ) {
            if ( 'full' != $size && ($data = image_get_intermediate_size( $attachment_id, $size )) ) {
                $filepath = apply_filters(
                    'load_image_to_edit_filesystempath',
                    path_join( dirname( $filepath ), $data['file'] ),
                    $attachment_id,
                    $size
                );
            }
        } elseif ( function_exists( 'fopen' ) && true == ini_get( 'allow_url_fopen' ) ) {
            $filepath = apply_filters(
                'load_image_to_edit_attachmenturl',
                wp_get_attachment_url( $attachment_id ),
                $attachment_id,
                $size
            );
        }
        
        return apply_filters(
            'load_image_to_edit_path',
            $filepath,
            $attachment_id,
            $size
        );
    }
    
    /**
     * Regenerates an image's thumbnails and re-uploads them to the storage service.
     *
     * @param $postId
     * @param bool $existingSizes
     * @return bool|string
     */
    public function regenerateFile( $postId, $existingSizes = false )
    {
        add_filter( 'wp_image_editors', function ( $editors ) {
            array_unshift( $editors, '\\MediaCloud\\Plugin\\Tools\\Storage\\StorageImageEditor' );
            return $editors;
        } );
        ilab_set_time_limit( 0 );
        $originalImagePath = $fullsizepath = wp_get_original_image_path( $postId, true );
        $originalImageBasePath = pathinfo( $originalImagePath, PATHINFO_DIRNAME );
        if ( !file_exists( $originalImageBasePath ) ) {
            @mkdir( $originalImageBasePath, 0755, true );
        }
        $hasOriginalImage = !empty($fullsizepath);
        
        if ( empty($fullsizepath) || !file_exists( $fullsizepath ) ) {
            $scaledImagePath = $fullsizepath = get_attached_file( $postId, true );
            $scaledImageBasePath = pathinfo( $originalImagePath, PATHINFO_DIRNAME );
            if ( !file_exists( $scaledImageBasePath ) ) {
                @mkdir( $scaledImageBasePath, 0755, true );
            }
        }
        
        $mimeType = get_post_mime_type( $postId );
        $meta = get_post_meta( $postId, '_wp_attachment_metadata', true );
        $httpClient = new Client();
        
        if ( !file_exists( $fullsizepath ) && isset( $meta['s3'] ) ) {
            $processed = false;
            
            if ( $hasOriginalImage ) {
                $originalImageKey = arrayPath( $meta, 'original_image_s3/key' );
                
                if ( !empty($originalImageKey) ) {
                    $privacy = arrayPath( $meta, 'original_image_s3/privacy', 'private' );
                    try {
                        $doSign = $this->client->usesSignedURLs( $mimeType ) || $privacy !== 'public-read';
                        $url = ( $doSign ? $this->client->presignedUrl( $originalImageKey, $this->client->signedURLExpirationForType( $mimeType ) ) : $this->client->url( $originalImageKey, $mimeType ) );
                        try {
                            $httpClient->get( $url, [
                                'sink' => $originalImagePath,
                            ] );
                            $processed = file_exists( $originalImagePath );
                            if ( $processed ) {
                                $fullsizepath = $originalImagePath;
                            }
                        } catch ( RequestException $ex ) {
                            Logger::error(
                                "Error downloading original image: " . $ex->getMessage(),
                                [],
                                __METHOD__,
                                __LINE__
                            );
                        }
                    } catch ( \Exception $ex ) {
                        Logger::error(
                            "Error downloading original image: " . $ex->getMessage(),
                            [],
                            __METHOD__,
                            __LINE__
                        );
                    }
                }
            
            }
            
            
            if ( !$processed ) {
                $imageKey = arrayPath( $meta, 's3/key' );
                
                if ( !empty($imageKey) ) {
                    $privacy = arrayPath( $meta, 's3/privacy', 'private' );
                    try {
                        $doSign = $this->client->usesSignedURLs( $mimeType ) || $privacy !== 'public-read';
                        $url = ( $doSign ? $this->client->presignedUrl( $imageKey, $this->client->signedURLExpirationForType( $mimeType ) ) : $this->client->url( $imageKey, $mimeType ) );
                        try {
                            $filename = pathinfo( $scaledImagePath, PATHINFO_FILENAME );
                            $scaledImagePath = str_replace( $filename, str_replace( '-scaled', '', $filename ), $scaledImagePath );
                            $httpClient->get( $url, [
                                'sink' => $scaledImagePath,
                            ] );
                            $processed = file_exists( $scaledImagePath );
                            if ( $processed ) {
                                $fullsizepath = $originalImagePath;
                            }
                        } catch ( RequestException $ex ) {
                            Logger::error(
                                "Error downloading resized image: " . $ex->getMessage(),
                                [],
                                __METHOD__,
                                __LINE__
                            );
                        }
                    } catch ( \Exception $ex ) {
                        Logger::error(
                            "Error downloading resized image: " . $ex->getMessage(),
                            [],
                            __METHOD__,
                            __LINE__
                        );
                    }
                }
            
            }
        
        }
        
        if ( !file_exists( $fullsizepath ) ) {
            return "No original or resized image exists on cloud storage.";
        }
        $shouldPreserve = $this->preserveFilePaths;
        $this->preserveFilePaths = 'preserve';
        Logger::startTiming(
            'Regenerating metadata ...',
            [
            'id' => $postId,
        ],
            __METHOD__,
            __LINE__
        );
        $metadata = wp_generate_attachment_metadata( $postId, $fullsizepath );
        if ( $existingSizes !== false && is_array( $existingSizes ) && !empty($existingSizes) ) {
            $metadata['sizes'] = array_merge( $existingSizes, arrayPath( $metadata, 'sizes', [] ) );
        }
        Logger::endTiming(
            'Regenerating metadata ...',
            [
            'id' => $postId,
        ],
            __METHOD__,
            __LINE__
        );
        $newSizes = [];
        $sizes = arrayPath( $metadata, 'sizes', [] );
        foreach ( $sizes as $sizeName => $size ) {
            if ( !isset( $size['file'] ) || empty($size['file']) ) {
                continue;
            }
            $newSizes[$sizeName] = $size;
        }
        $metadata['sizes'] = $newSizes;
        wp_update_attachment_metadata( $postId, $metadata );
        $this->preserveFilePaths = $shouldPreserve;
        return true;
    }
    
    //endregion
    //region Importer
    /**
     * @param int $index
     * @param int $postId
     * @param ImportProgressDelegate|null $progressDelegate
     * @param array $options
     */
    public function processImport(
        $index,
        $postId,
        $progressDelegate,
        $options = array()
    )
    {
        //		if ($progressDelegate) {
        //		    $progressDelegate->updateCurrentIndex($index + 1);
        //        }
        //
        $isDocument = false;
        $skipThumbnails = ( empty($options['skip-thumbnails']) ? false : true );
        $pathmode = ( empty($options['path-handling']) ? 'preserve' : $options['path-handling'] );
        $data = wp_get_attachment_metadata( $postId );
        $existingPrefix = gen_uuid( 8 );
        
        if ( empty($data) ) {
            $isDocument = true;
            $post_mime = get_post_mime_type( $postId );
            $upload_file = get_attached_file( $postId );
            $file = _wp_relative_upload_path( $upload_file );
            $fileName = basename( $upload_file );
            if ( $progressDelegate ) {
                $progressDelegate->updateCurrentFileName( $fileName );
            }
            $data = [
                'file' => $file,
            ];
            
            if ( is_file( $upload_file ) ) {
                $mime = null;
                $ftype = wp_check_filetype( $upload_file );
                if ( !empty($ftype) && isset( $ftype['type'] ) ) {
                    $mime = $ftype['type'];
                }
                if ( $mime == 'image/vnd.adobe.photoshop' ) {
                    $mime = 'application/vnd.adobe.photoshop';
                }
                $data['ilab-mime'] = $mime;
                if ( $mime != $post_mime ) {
                    wp_update_post( [
                        'ID'             => $postId,
                        'post_mime_type' => $mime,
                    ] );
                }
                $imagesize = getimagesize( $upload_file );
                if ( $imagesize ) {
                    
                    if ( file_is_displayable_image( $upload_file ) ) {
                        $data['width'] = $imagesize[0];
                        $data['height'] = $imagesize[1];
                        $data['sizes'] = [
                            'full' => [
                            'file'   => $data['file'],
                            'width'  => $data['width'],
                            'height' => $data['height'],
                        ],
                        ];
                        $isDocument = false;
                    }
                
                }
                if ( $mime == 'application/pdf' ) {
                    
                    if ( !empty($this->settings->extractPDFPageSize) ) {
                        list( $pageWidth, $pageHeight ) = $this->extractPDFPageSize( pathinfo( $upload_file, PATHINFO_DIRNAME ), pathinfo( $upload_file, PATHINFO_BASENAME ) );
                        
                        if ( !empty($pageWidth) && !empty($pageHeight) ) {
                            $data['sizes'] = [
                                'full' => [
                                'file'   => $data['file'],
                                'width'  => $pageWidth,
                                'height' => $pageHeight,
                            ],
                            ];
                            $isDocument = false;
                        }
                    
                    }
                
                }
            }
        
        } else {
            
            if ( empty($data['file']) ) {
                $attachedFile = get_attached_file( $postId );
                $data['file'] = _wp_relative_upload_path( $attachedFile );
            }
            
            $fileName = basename( $data['file'] );
            if ( $progressDelegate ) {
                $progressDelegate->updateCurrentFileName( $fileName );
            }
        }
        
        $data = $this->updateAttachmentMetadata(
            $data,
            $postId,
            $pathmode,
            $skipThumbnails,
            $existingPrefix
        );
        
        if ( isset( $data['original_image'] ) ) {
            $s3Data = $this->uploadOriginalImage(
                $data,
                $postId,
                $pathmode,
                $existingPrefix
            );
            if ( !empty($s3Data) ) {
                $data['original_image_s3'] = $s3Data;
            }
        }
        
        
        if ( $isDocument ) {
            update_post_meta( $postId, 'ilab_s3_info', $data );
        } else {
            update_post_meta( $postId, '_wp_attachment_metadata', $data );
        }
    
    }
    
    //endregion
    //region Direct Upload Support
    /**
     * Gets a pre-signed URL for uploading directly to the storage backend
     *
     * @param string $filename
     *
     * @return \MediaCloud\Plugin\Tools\Storage\UploadInfo|null
     */
    public function uploadUrlForFile( $filename )
    {
        $prefix = StorageToolSettings::prefix( null );
        $parts = explode( '/', $filename );
        $bucketFilename = array_pop( $parts );
        if ( $this->client && $this->client->enabled() ) {
            try {
                return $this->client->uploadUrl(
                    $prefix . $bucketFilename,
                    StorageToolSettings::privacy(),
                    StorageToolSettings::cacheControl(),
                    StorageToolSettings::expires()
                );
            } catch ( StorageException $ex ) {
                Logger::error(
                    'Generate File Upload URL Error',
                    [
                    'exception' => $ex->getMessage(),
                ],
                    __METHOD__,
                    __LINE__
                );
            }
        }
        return null;
    }
    
    /**
     * Once a file has been directly uploaded, it'll need to be "imported" into WordPress
     *
     * @param FileInfo $fileInfo
     * @param string[] $thumbs
     *
     * @return array|bool
     * @throws StorageException
     */
    public function importImageAttachmentFromStorage( $fileInfo, $thumbs = array() )
    {
        if ( !$this->client || !$this->client->enabled() ) {
            return null;
        }
        if ( !is_array( $fileInfo->size() ) ) {
            return null;
        }
        $this->client->insureACL( $fileInfo->key(), StorageToolSettings::privacy( $fileInfo->mimeType() ) );
        $fileParts = explode( '/', $fileInfo->key() );
        $filename = array_pop( $fileParts );
        $url = $this->client->url( $fileInfo->key() );
        $providerClass = get_class( $this->client );
        $providerId = $providerClass::identifier();
        $s3Info = [
            'url'       => $url,
            'provider'  => $providerId,
            'mime-type' => $fileInfo->mimeType(),
            'bucket'    => $this->client->bucket(),
            'privacy'   => StorageToolSettings::privacy( $fileInfo->mimeType() ),
            'key'       => $fileInfo->key(),
            'v'         => MEDIA_CLOUD_INFO_VERSION,
            'options'   => [
            'params' => [],
        ],
        ];
        if ( !empty(StorageToolSettings::cacheControl()) ) {
            $s3Info['options']['params']['CacheControl'] = StorageToolSettings::cacheControl();
        }
        if ( !empty(StorageToolSettings::expires()) ) {
            $s3Info['options']['params']['Expires'] = StorageToolSettings::expires();
        }
        $meta = [
            'width'      => $fileInfo->size()[0],
            'height'     => $fileInfo->size()[1],
            'file'       => $fileInfo->key(),
            'image_meta' => [],
            's3'         => $s3Info,
            'sizes'      => [],
        ];
        $builtInSizes = [];
        foreach ( [
            'thumbnail',
            'medium',
            'medium_large',
            'large'
        ] as $size ) {
            $builtInSizes[$size] = [
                'width'  => get_option( "{$size}_size_w" ),
                'height' => get_option( "{$size}_size_h" ),
                'crop'   => get_option( "{$size}_crop", 0 ),
            ];
        }
        $additional_sizes = wp_get_additional_image_sizes();
        $sizes = array_merge( $builtInSizes, $additional_sizes );
        $dynamicEnabled = apply_filters( 'media-cloud/dynamic-images/enabled', false );
        
        if ( !$dynamicEnabled || !empty($thumbs) ) {
            $indexedThumbs = [];
            foreach ( $thumbs as $thumb ) {
                if ( preg_match( '/([0-9]+x[0-9]+)\\.(?:.*)$/', $thumb, $matches ) ) {
                    $indexedThumbs[$matches[1]] = $thumb;
                }
            }
            foreach ( $sizes as $sizeKey => $size ) {
                $resized = image_resize_dimensions(
                    $fileInfo->size()[0],
                    $fileInfo->size()[1],
                    $size['width'],
                    $size['height'],
                    $size['crop']
                );
                
                if ( $resized ) {
                    $sizeIndex = "{$resized[4]}x{$resized[5]}";
                    
                    if ( isset( $indexedThumbs[$sizeIndex] ) ) {
                        $sizeS3Info = $s3Info;
                        $sizeS3Info['url'] = $this->client()->url( $indexedThumbs[$sizeIndex] );
                        $sizeS3Info['key'] = $indexedThumbs[$sizeIndex];
                        $meta['sizes'][$sizeKey] = [
                            'file'      => basename( $indexedThumbs[$sizeIndex] ),
                            'width'     => $resized[4],
                            'height'    => $resized[5],
                            'mime-type' => 'image/jpeg',
                            's3'        => $sizeS3Info,
                        ];
                    }
                
                }
            
            }
        } else {
            foreach ( $sizes as $sizeKey => $size ) {
                $resized = image_resize_dimensions(
                    $fileInfo->size()[0],
                    $fileInfo->size()[1],
                    $size['width'],
                    $size['height'],
                    $size['crop']
                );
                if ( $resized ) {
                    $meta['sizes'][$sizeKey] = [
                        'file'      => $filename,
                        'width'     => $resized[4],
                        'height'    => $resized[5],
                        'mime-type' => 'image/jpeg',
                        's3'        => $s3Info,
                    ];
                }
            }
        }
        
        $dir = wp_upload_dir();
        $guid = trailingslashit( $dir['baseurl'] ) . $fileInfo->key();
        $post = wp_insert_post( [
            'post_author'    => get_current_user_id(),
            'post_title'     => $filename,
            'post_status'    => 'inherit',
            'post_type'      => 'attachment',
            'guid'           => $url,
            'post_mime_type' => $fileInfo->mimeType(),
        ] );
        if ( is_wp_error( $post ) ) {
            return false;
        }
        $meta = apply_filters( 'media-cloud/storage/after-upload', $meta, $post );
        $meta = apply_filters( 'media-cloud/vision/process-meta', $meta, $post );
        add_post_meta( $post, '_wp_attached_file', $fileInfo->key() );
        add_post_meta( $post, '_wp_attachment_metadata', $meta );
        $thumbUrl = image_downsize( $post, [ 128, 128 ] );
        if ( is_array( $thumbUrl ) ) {
            $thumbUrl = $thumbUrl[0];
        }
        return [
            'id'    => $post,
            'url'   => $url,
            'thumb' => $thumbUrl,
        ];
    }
    
    /**
     * Once a file has been directly uploaded, it'll need to be "imported" into WordPress
     *
     * @param int|string $postId
     * @param FileInfo $fileInfo
     * @param string[] $thumbs
     *
     * @return array|bool|null
     *
     * @throws StorageException
     */
    public function importExistingAttachmentFromStorage(
        $postId,
        $fileInfo,
        $thumbs = array(),
        $scaled = null
    )
    {
        if ( !$this->client || !$this->client->enabled() ) {
            return null;
        }
        if ( $fileInfo->mimeType() && strpos( $fileInfo->mimeType(), 'image/' ) === 0 ) {
            return $this->importExistingImageAttachmentFromStorage(
                $postId,
                $fileInfo,
                $thumbs,
                $scaled
            );
        }
        $url = $this->client->url( $fileInfo->key() );
        $s3Info = [
            'url'       => $url,
            'mime-type' => $fileInfo->mimeType(),
            'bucket'    => $this->client->bucket(),
            'privacy'   => StorageToolSettings::privacy( $fileInfo->mimeType() ),
            'key'       => $fileInfo->key(),
            'v'         => MEDIA_CLOUD_INFO_VERSION,
            'options'   => [
            'params' => [],
        ],
        ];
        if ( !empty(StorageToolSettings::cacheControl()) ) {
            $s3Info['options']['params']['CacheControl'] = StorageToolSettings::cacheControl();
        }
        if ( !empty(StorageToolSettings::expires()) ) {
            $s3Info['options']['params']['Expires'] = StorageToolSettings::expires();
        }
        $meta = wp_get_attachment_metadata( $postId, true );
        $meta['s3'] = $s3Info;
        update_post_meta( $postId, '_wp_attachment_metadata', $meta );
        return [
            'id'    => $postId,
            'url'   => $url,
            'thumb' => wp_mime_type_icon( $postId ),
        ];
    }
    
    /**
     * Once a file has been directly uploaded, it'll need to be "imported" into WordPress
     *
     * @param int|string $postId
     * @param FileInfo $fileInfo
     * @param string[] $thumbs
     *
     * @return array|bool
     * @throws StorageException
     */
    public function importExistingImageAttachmentFromStorage(
        $postId,
        $fileInfo,
        $thumbs = array(),
        $scaled = null
    )
    {
        if ( !$this->client || !$this->client->enabled() ) {
            return null;
        }
        $originalFileInfo = $fileInfo;
        if ( !empty($scaled) ) {
            $fileInfo = $this->client->info( $scaled );
        }
        if ( !is_array( $fileInfo->size() ) ) {
            return null;
        }
        $url = $this->client->url( $fileInfo->key() );
        $s3Info = [
            'url'       => $url,
            'mime-type' => $fileInfo->mimeType(),
            'bucket'    => $this->client->bucket(),
            'privacy'   => StorageToolSettings::privacy( $fileInfo->mimeType() ),
            'key'       => $fileInfo->key(),
            'v'         => MEDIA_CLOUD_INFO_VERSION,
            'options'   => [
            'params' => [],
        ],
        ];
        if ( !empty(StorageToolSettings::cacheControl()) ) {
            $s3Info['options']['params']['CacheControl'] = StorageToolSettings::cacheControl();
        }
        if ( !empty(StorageToolSettings::expires()) ) {
            $s3Info['options']['params']['Expires'] = StorageToolSettings::expires();
        }
        $meta = wp_get_attachment_metadata( $postId, true );
        $meta['s3'] = $s3Info;
        $builtInSizes = [];
        foreach ( [
            'thumbnail',
            'medium',
            'medium_large',
            'large'
        ] as $size ) {
            $builtInSizes[$size] = [
                'width'  => get_option( "{$size}_size_w" ),
                'height' => get_option( "{$size}_size_h" ),
                'crop'   => get_option( "{$size}_crop", 0 ),
            ];
        }
        $additional_sizes = wp_get_additional_image_sizes();
        $sizes = array_merge( $builtInSizes, $additional_sizes );
        $indexedThumbs = [];
        foreach ( $thumbs as $thumb ) {
            if ( preg_match( '/(-[0-9]+x[0-9]+){2,}\\.(?:.*)$/', $thumb, $matches ) ) {
                continue;
            }
            if ( preg_match( '/([0-9]+x[0-9]+)\\.(?:.*)$/', $thumb, $matches ) ) {
                if ( !isset( $indexedThumbs[$matches[1]] ) ) {
                    $indexedThumbs[$matches[1]] = $thumb;
                }
            }
        }
        $meta['sizes'] = [];
        foreach ( $sizes as $sizeKey => $size ) {
            $resized = image_resize_dimensions(
                $fileInfo->size()[0],
                $fileInfo->size()[1],
                $size['width'],
                $size['height'],
                $size['crop']
            );
            
            if ( $resized ) {
                $sizeIndex = "{$resized[4]}x{$resized[5]}";
                
                if ( isset( $indexedThumbs[$sizeIndex] ) ) {
                    $sizeS3Info = $s3Info;
                    $sizeS3Info['url'] = $this->client()->url( $indexedThumbs[$sizeIndex] );
                    $sizeS3Info['key'] = $indexedThumbs[$sizeIndex];
                    $meta['sizes'][$sizeKey] = [
                        'file'      => basename( $indexedThumbs[$sizeIndex] ),
                        'width'     => $resized[4],
                        'height'    => $resized[5],
                        'mime-type' => 'image/jpeg',
                        's3'        => $sizeS3Info,
                    ];
                }
            
            }
        
        }
        
        if ( !empty($scaled) ) {
            $meta['file'] = $scaled;
            $url = $this->client->url( $originalFileInfo->key() );
            $s3Info = [
                'url'       => $url,
                'mime-type' => $originalFileInfo->mimeType(),
                'bucket'    => $this->client->bucket(),
                'privacy'   => StorageToolSettings::privacy( $originalFileInfo->mimeType() ),
                'key'       => $originalFileInfo->key(),
                'v'         => MEDIA_CLOUD_INFO_VERSION,
                'options'   => [
                'params' => [],
            ],
            ];
            $meta['original_image_s3'] = $s3Info;
            $meta['original_image'] = pathinfo( $originalFileInfo->key(), PATHINFO_BASENAME );
        }
        
        update_post_meta( $postId, '_wp_attachment_metadata', $meta );
        $thumbUrl = image_downsize( $postId, [ 128, 128 ] );
        if ( is_array( $thumbUrl ) ) {
            $thumbUrl = $thumbUrl[0];
        }
        return [
            'id'    => $postId,
            'url'   => $url,
            'thumb' => $thumbUrl,
        ];
    }
    
    /**
     * Once a file has been directly uploaded, it'll need to be "imported" into WordPress
     *
     * @param FileInfo $fileInfo
     * @param string[] $thumbs
     *
     * @return array|bool
     * @throws StorageException
     */
    public function importAttachmentFromStorage( $fileInfo, $thumbs = array(), $scaled = null )
    {
        if ( !$this->client || !$this->client->enabled() ) {
            return null;
        }
        if ( $fileInfo->mimeType() && strpos( $fileInfo->mimeType(), 'image/' ) === 0 ) {
            return $this->importImageAttachmentFromStorage( $fileInfo, $thumbs );
        }
        $this->client->insureACL( $fileInfo->key(), StorageToolSettings::privacy( $fileInfo->mimeType() ) );
        $fileParts = explode( '/', $fileInfo->key() );
        $filename = array_pop( $fileParts );
        $url = $this->client->url( $fileInfo->key() );
        $providerClass = get_class( $this->client );
        $providerId = $providerClass::identifier();
        $s3Info = [
            'url'       => $url,
            'provider'  => $providerId,
            'mime-type' => $fileInfo->mimeType(),
            'bucket'    => $this->client->bucket(),
            'privacy'   => StorageToolSettings::privacy( $fileInfo->mimeType() ),
            'key'       => $fileInfo->key(),
            'v'         => MEDIA_CLOUD_INFO_VERSION,
            'options'   => [
            'params' => [],
        ],
        ];
        if ( !empty(StorageToolSettings::cacheControl()) ) {
            $s3Info['options']['params']['CacheControl'] = StorageToolSettings::cacheControl();
        }
        if ( !empty(StorageToolSettings::expires()) ) {
            $s3Info['options']['params']['Expires'] = StorageToolSettings::expires();
        }
        $meta = [
            'file'     => $fileInfo->key(),
            'filesize' => $fileInfo->length(),
            's3'       => $s3Info,
        ];
        if ( strpos( $url, '?' ) !== false ) {
            $url = $this->client->url( $fileInfo->key(), 'skip' );
        }
        $postData = [
            'post_author'    => get_current_user_id(),
            'post_title'     => $filename,
            'post_status'    => 'inherit',
            'post_type'      => 'attachment',
            'guid'           => $url,
            'post_mime_type' => $fileInfo->mimeType(),
        ];
        $post = wp_insert_post( $postData );
        if ( is_wp_error( $post ) ) {
            return false;
        }
        $meta = apply_filters( 'media-cloud/storage/after-upload', $meta, $post );
        add_post_meta( $post, '_wp_attached_file', $fileInfo->key() );
        add_post_meta( $post, '_wp_attachment_metadata', $meta );
        return [
            'id'    => $post,
            'url'   => $url,
            'thumb' => wp_mime_type_icon( $post ),
        ];
    }
    
    //endregion
    //region File List
    public function getFileList(
        $directoryKeys = array( '' ),
        $skipThumbnails = false,
        $limit = -1,
        $next = null,
        $recursive = false
    )
    {
        $tempFileList = [];
        $nextToken = null;
        foreach ( $directoryKeys as $key ) {
            
            if ( empty($key) || strpos( strrev( $key ), '/' ) === 0 ) {
                if ( is_multisite() && !empty($this->multisiteRoot) ) {
                    if ( strpos( $key, $this->multisiteRoot ) === false ) {
                        continue;
                    }
                }
                $res = $this->client()->ls(
                    $key,
                    '/',
                    $limit,
                    $next,
                    $recursive
                );
                $nextToken = $res['next'];
                $tempFileList = $res['files'];
            } else {
                $tempFileList[] = $key;
            }
        
        }
        $unmatchedFileList = [];
        $fileList = [];
        
        if ( $skipThumbnails ) {
            foreach ( $tempFileList as $file ) {
                if ( strpos( strrev( $file ), '/' ) !== 0 ) {
                    
                    if ( preg_match( '/([0-9]+x[0-9]+)\\.(?:.*)$/', $file, $matches ) ) {
                        $sourceFile = str_replace( '-' . $matches[1], '', $file );
                        
                        if ( isset( $fileList[strtolower( $sourceFile )] ) ) {
                            $fileList[strtolower( $sourceFile )][] = $file;
                        } else {
                            
                            if ( isset( $unmatchedFileList[strtolower( $sourceFile )] ) ) {
                                $unmatchedFileList[strtolower( $sourceFile )]['thumbs'][] = $file;
                            } else {
                                $unmatchedFileList[strtolower( $sourceFile )] = [
                                    'key'    => $sourceFile,
                                    'thumbs' => [ $file ],
                                ];
                            }
                        
                        }
                    
                    } else {
                        
                        if ( preg_match( '/.*-scaled\\.[aA-zZ]+/', $file, $scaledMatch ) ) {
                            $sourceFile = str_replace( '-scaled', '', $file );
                            
                            if ( isset( $fileList[strtolower( $sourceFile )] ) ) {
                                $fileList[strtolower( $sourceFile )]['scaled'] = $file;
                            } else {
                                
                                if ( isset( $unmatchedFileList[strtolower( $sourceFile )] ) ) {
                                    $unmatchedFileList[strtolower( $sourceFile )]['scaled'] = $file;
                                } else {
                                    $unmatchedFileList[strtolower( $sourceFile )] = [
                                        'key'    => $sourceFile,
                                        'scaled' => $file,
                                    ];
                                }
                            
                            }
                        
                        } else {
                            $fileList[strtolower( $file )] = [
                                'key'             => $file,
                                'missingOriginal' => false,
                                'thumbs'          => [],
                            ];
                        }
                    
                    }
                
                }
            }
        } else {
            foreach ( $tempFileList as $file ) {
                $fileList[strtolower( $file )] = [
                    'key'             => $file,
                    'missingOriginal' => false,
                    'thumbs'          => [],
                ];
            }
        }
        
        foreach ( $unmatchedFileList as $key => $thumbs ) {
            if ( !isset( $fileList[$key] ) && isset( $thumbs['scaled'] ) ) {
                $fileList[$key] = [
                    'key'             => $thumbs['scaled'],
                    'originalKey'     => $thumbs['key'],
                    'missingOriginal' => true,
                    'thumbs'          => [],
                ];
            }
            
            if ( isset( $fileList[$key] ) ) {
                if ( isset( $thumbs['scaled'] ) ) {
                    $fileList[$key]['scaled'] = $thumbs['scaled'];
                }
                if ( isset( $thumbs['thumbs'] ) ) {
                    $fileList[$key]['thumbs'] = array_merge( $fileList[$key]['thumbs'], $thumbs['thumbs'] );
                }
            }
        
        }
        return [
            'next'  => $nextToken,
            'files' => array_values( $fileList ),
        ];
    }
    
    //endregion
    //region Image Optimizer
    public function handleImageOptimizer( $postId )
    {
        $this->processingOptimized = true;
        Logger::info(
            'Handle Image Optimizer: ' . $postId,
            [],
            __METHOD__,
            __LINE__
        );
        $mimeType = get_post_mime_type( $postId );
        
        if ( StorageGlobals::mimeTypeIsIgnored( $mimeType ) ) {
            Logger::info(
                "Mime type {$mimeType} for {$postId} is ignored.",
                [],
                __METHOD__,
                __LINE__
            );
            return;
        }
        
        add_filter(
            'media-cloud/storage/ignore-existing-s3-data',
            function ( $shouldIgnore, $attachmentId ) use( $postId ) {
            if ( $postId == $attachmentId ) {
                return true;
            }
            return $shouldIgnore;
        },
            10000,
            2
        );
        $this->processImport( 1, $postId, null );
    }
    
    public function handleSmushImageOptimizer( $postId, $stats )
    {
        // wp_smush_image_optimised runs inside of a wp_update_attachment_metadata
        // filter hook, so any metadata written by processImport will be overwritten.
        // We'll use the standard handleUpdateAttachmentMetadata() method to handle
        // the upload instead.
        $this->processingOptimized = true;
    }
    
    public function handleImagifyImageOptimizer( $postId, $data )
    {
        $this->handleImageOptimizer( $postId );
    }
    
    public function handleImagifyAfter( $process, $task )
    {
        $attachmentId = null;
        $data = $process->get_data();
        
        if ( !empty($data) ) {
            $media = $process->get_media();
            
            if ( !empty($media) ) {
                $attachmentId = $media->get_id();
                if ( !empty($attachmentId) ) {
                    $this->handleImageOptimizer( $attachmentId );
                }
            }
        
        }
    
    }
    
    private function displayOptimizerAdminNotice( $builtInImageOptimizer )
    {
        $type = 'warning';
        $suffix = '';
        
        if ( media_cloud_licensing()->is_plan( 'pro' ) ) {
            
            if ( class_exists( '\\Elementor\\Plugin' ) ) {
                $suffix .= '-elementor';
                $adminUrl = admin_url( 'admin.php?page=media-cloud-settings-integrations#elementor' );
                $adminUrlStorage = admin_url( 'admin.php?page=media-cloud-settings&tab=storage#deleting-files' );
                $whatThing = ( $builtInImageOptimizer ? 'image optimization' : 'an image optimization plugin and Media Cloud' );
                $additionalText = "<p><strong>Important!</strong>  To use <strong>Elementor</strong> with {$whatThing}, please turn on <strong>Auto Update Elementor</strong> in <a style='font-weight: bold' href='{$adminUrl}'>Integration Settings</a> and <strong>Queue Deletes</strong> in <a style='font-weight: bold' href='{$adminUrlStorage}'>Cloud Storage Settings</a>.</p>";
            } else {
                $additionalText = '';
            }
        
        } else {
            
            if ( class_exists( '\\Elementor\\Plugin' ) ) {
                $type = 'error';
                $suffix .= '-elementor';
                $additionalText = "<p style='font-weight: bold'>Using an image optimizer other than EWWW Image Optimizer with Elementor and Media Cloud is not supported and will lead to problems and the appearance of missing images.  It's recommended to use <a href='https://wordpress.org/plugins/ewww-image-optimizer/' target='_blank'>EWWW Image Optimizer</a> instead of the plugin you are currently using.  The Pro version of Media Cloud does not have this limitation, you can <a href='https://mediacloud.press/pricing' target='_blank'>upgrade here</a>.</p>";
            } else {
                $additionalText = '';
            }
        
        }
        
        
        if ( empty($builtInImageOptimizer) ) {
            $warningId = "ilab-optimizer-{$this->imageOptimizer}-{$type}{$suffix}-forever";
            $warningText = "Image optimizer plugins often do the optimization step in the background, not actually during the upload process.";
        } else {
            $warningId = "mcloud-builtin-optimizer-{$type}{$suffix}-forever";
            $warningText = "You have background processing enabled for the image optimization feature.";
        }
        
        $message = <<<Optimizer
<p style='text-transform:uppercase; font-weight:bold; opacity: 0.8; margin-bottom:0; padding-bottom:0px;'>Image Optimizer Warning</p>
{$additionalText}
<p>{$warningText} Because of this, Media Cloud will not upload your images to your cloud storage provider <strong>until after the image is optimized</strong>.  This means 
your uploaded images will appear as a local images until after the optimization process happens.  This can take several minutes.</p>
Optimizer;
        NoticeManager::instance()->displayAdminNotice(
            $type,
            $message,
            true,
            $warningId
        );
    }
    
    //endregion
    //region Settings
    public function providerOptions()
    {
        $providers = [];
        foreach ( StorageToolSettings::drivers() as $id => $driver ) {
            $providers[$id] = $driver['name'];
        }
        return $providers;
    }
    
    public function providerHelp()
    {
        $help = [];
        foreach ( StorageToolSettings::drivers() as $id => $driver ) {
            $helpData = arrayPath( $driver, 'help', null );
            if ( !empty($helpData) ) {
                $help[$id] = $helpData;
            }
        }
        return $help;
    }
    
    //endregion
    //region Importing From Cloud
    private function doImportFile(
        $key,
        $thumbs,
        $scaled = null,
        &$newPostId = null
    )
    {
        $dir = wp_upload_dir();
        $base = trailingslashit( $dir['basedir'] );
        $destFile = $base . $key;
        $desturl = trailingslashit( $dir['baseurl'] ) . $key;
        Logger::info(
            "DIRECT URL " . $desturl,
            [],
            __METHOD__,
            __LINE__
        );
        Logger::info(
            "DIRECT BASE " . $dir['baseurl'],
            [],
            __METHOD__,
            __LINE__
        );
        $postId = $this->findPostId( null, $key );
        $destDir = pathinfo( $destFile, PATHINFO_DIRNAME );
        if ( !file_exists( $destDir ) ) {
            @mkdir( $destDir, 0777, true );
        }
        
        if ( !file_exists( $destFile ) ) {
            $url = $this->client()->presignedUrl( $key );
            $client = new Client();
            $response = $client->get( $url, [
                'save_to' => $destFile,
            ] );
            if ( $response->getStatusCode() != 200 ) {
                return false;
            }
        }
        
        $indexedThumbs = [];
        if ( !empty($postId) ) {
            foreach ( $thumbs as $thumb ) {
                if ( preg_match( '/(-[0-9]+x[0-9]+){2,}\\.(?:.*)$/', $thumb, $matches ) ) {
                    continue;
                }
                if ( preg_match( '/([0-9]+x[0-9]+)\\.(?:.*)$/', $thumb, $matches ) ) {
                    
                    if ( !isset( $indexedThumbs[$matches[1]] ) ) {
                        $indexedThumbs[$matches[1]] = $thumb;
                        $thumbFile = $base . $thumb;
                        
                        if ( !file_exists( $thumbFile ) ) {
                            $url = $this->client()->presignedUrl( $thumb );
                            $client = new Client();
                            $response = $client->get( $url, [
                                'save_to' => $thumbFile,
                            ] );
                            if ( $response->getStatusCode() != 200 ) {
                                unset( $indexedThumbs[$matches[1]] );
                            }
                        }
                    
                    }
                
                }
            }
        }
        
        if ( !empty($scaled) && $scaled !== $key ) {
            $scaledFile = $base . $scaled;
            
            if ( !file_exists( $scaledFile ) ) {
                $url = $this->client()->presignedUrl( $scaled );
                $client = new Client();
                $response = $client->get( $url, [
                    'save_to' => $scaledFile,
                ] );
                if ( $response->getStatusCode() != 200 ) {
                    $scaled = null;
                }
            }
        
        }
        
        require_once ABSPATH . 'wp-admin/includes/image.php';
        
        if ( empty($postId) ) {
            $filetype = wp_check_filetype( basename( $destFile ), null );
            $mimeType = $filetype['type'];
            $attachment = [
                'guid'           => $desturl,
                'post_mime_type' => $filetype['type'],
                'post_title'     => preg_replace( '/\\.[^.]+$/', '', basename( $destFile ) ),
                'post_content'   => '',
                'post_status'    => 'inherit',
            ];
            $postId = wp_insert_attachment( $attachment, $destFile );
            add_filter( 'media-cloud/storage/upload-master', [ $this, 'uploadMaster' ] );
            $meta = wp_generate_attachment_metadata( $postId, $destFile );
        } else {
            add_filter( 'media-cloud/storage/upload-master', [ $this, 'uploadMaster' ] );
            $meta = wp_get_attachment_metadata( $postId );
            $basefilename = basename( $key );
            foreach ( $meta['sizes'] as $size => $sizeData ) {
                if ( $sizeData['file'] == $basefilename ) {
                    unset( $meta['sizes'][$size] );
                }
            }
            $mimeType = typeFromMeta( $meta );
        }
        
        
        if ( count( $indexedThumbs ) > 0 ) {
            $builtInSizes = [];
            foreach ( [
                'thumbnail',
                'medium',
                'medium_large',
                'large'
            ] as $size ) {
                $builtInSizes[$size] = [
                    'width'  => get_option( "{$size}_size_w" ),
                    'height' => get_option( "{$size}_size_h" ),
                    'crop'   => get_option( "{$size}_crop", 0 ),
                ];
            }
            $additional_sizes = wp_get_additional_image_sizes();
            $sizes = array_merge( $builtInSizes, $additional_sizes );
            $meta['sizes'] = [];
            $s3Info = [
                'bucket'  => $this->client->bucket(),
                'privacy' => StorageToolSettings::privacy( $mimeType ),
                'v'       => MEDIA_CLOUD_INFO_VERSION,
                'options' => [
                'params' => [],
            ],
            ];
            foreach ( $sizes as $sizeKey => $size ) {
                $fileInfo = $this->client()->info( $key );
                $resized = image_resize_dimensions(
                    $fileInfo->size()[0],
                    $fileInfo->size()[1],
                    $size['width'],
                    $size['height'],
                    $size['crop']
                );
                
                if ( $resized ) {
                    $sizeIndex = "{$resized[4]}x{$resized[5]}";
                    
                    if ( isset( $indexedThumbs[$sizeIndex] ) ) {
                        $sizeS3Info = $s3Info;
                        $sizeS3Info['mime-type'] = $fileInfo->mimeType();
                        $sizeS3Info['url'] = $this->client()->url( $indexedThumbs[$sizeIndex] );
                        $sizeS3Info['key'] = $indexedThumbs[$sizeIndex];
                        $meta['sizes'][$sizeKey] = [
                            'file'      => basename( $indexedThumbs[$sizeIndex] ),
                            'width'     => $resized[4],
                            'height'    => $resized[5],
                            'mime-type' => 'image/jpeg',
                            's3'        => $sizeS3Info,
                        ];
                    }
                
                }
            
            }
        }
        
        $newPostId = $postId;
        wp_update_attachment_metadata( $postId, $meta );
        remove_filter( 'media-cloud/storage/upload-master', [ $this, 'uploadMaster' ] );
        if ( count( $meta['sizes'] ) == 0 ) {
            $this->regenerateFile( $postId );
        }
        return true;
    }
    
    private function findPostId( $info, $key )
    {
        $dir = wp_upload_dir();
        $desturl = trailingslashit( $dir['baseurl'] ) . $key;
        global  $wpdb ;
        
        if ( !empty($info) ) {
            $query = $wpdb->prepare( "select ID from {$wpdb->posts} where (guid = %s) or (guid = %s)", $desturl, $info->url() );
        } else {
            $query = $wpdb->prepare( "select ID from {$wpdb->posts} where guid = %s", $desturl );
        }
        
        $postId = $wpdb->get_var( $query );
        
        if ( empty($postId) ) {
            $query = $wpdb->prepare( "select ID from {$wpdb->posts} where guid = %s", $this->client()->url( $key ) );
            $postId = $wpdb->get_var( $query );
            
            if ( empty($postId) ) {
                $query = $wpdb->prepare( "select post_id from {$wpdb->postmeta} where meta_key='_wp_attached_file' and meta_value = %s", $key );
                $results = $wpdb->get_results( $query, ARRAY_A );
                if ( count( $results ) === 1 ) {
                    $postId = $results[0]['post_id'];
                }
                
                if ( empty($postId) ) {
                    $query = $wpdb->prepare( "select post_id from {$wpdb->postmeta} where meta_key='_wp_attachment_metadata' and meta_value LIKE %s", '%' . $key . '%' );
                    $results = $wpdb->get_results( $query, ARRAY_A );
                    if ( count( $results ) === 1 ) {
                        $postId = $results[0]['post_id'];
                    }
                    
                    if ( empty($postId) ) {
                        $query = $wpdb->prepare( "select post_id from {$wpdb->postmeta} where meta_key='_wp_attachment_metadata' and meta_value LIKE %s", '%' . str_replace( '-scaled', '', $key ) . '%' );
                        $results = $wpdb->get_results( $query, ARRAY_A );
                        if ( count( $results ) === 1 ) {
                            $postId = $results[0]['post_id'];
                        }
                    }
                
                }
            
            }
        
        }
        
        return $postId;
    }
    
    private function doImportDynamicFile(
        $key,
        $thumbs,
        $scaled = null,
        &$newPostId = null
    )
    {
        $info = $this->client()->info( $key );
        $postId = $this->findPostId( $info, $key );
        
        if ( !empty($postId) ) {
            $newPostId = $postId;
            $this->importExistingAttachmentFromStorage(
                $postId,
                $info,
                $thumbs,
                $scaled
            );
        } else {
            $result = $this->importAttachmentFromStorage( $info, $thumbs, $scaled );
            $newPostId = arrayPath( $result, 'id', null );
        }
        
        return true;
    }
    
    /**
     * @param string $key
     * @param array $thumbs
     * @param bool $importOnly
     * @param string $preservePaths
     * @param null $scaled
     * @param null|TaskReporter $reporter
     *
     * @return bool
     */
    public function importFileFromStorage(
        $key,
        $thumbs,
        $importOnly,
        $preservePaths,
        $scaled = null,
        $reporter = null
    )
    {
        $oldPreserve = $this->preserveFilePaths;
        $this->preserveFilePaths = ( $preservePaths ? 'preserve' : 'replace' );
        $error = 'Success';
        try {
            
            if ( $importOnly ) {
                $success = $this->doImportDynamicFile(
                    $key,
                    $thumbs,
                    $scaled,
                    $newPostId
                );
            } else {
                $success = $this->doImportFile(
                    $key,
                    $thumbs,
                    $scaled,
                    $newPostId
                );
            }
        
        } catch ( \Exception $ex ) {
            $error = $ex->getMessage();
            Logger::error(
                $ex->getMessage(),
                [],
                __METHOD__,
                __LINE__
            );
            $success = false;
        }
        $this->preserveFilePaths = $oldPreserve;
        if ( !empty($reporter) ) {
            $reporter->add( [
                $newPostId,
                $key,
                ( empty($thumbs) ? 0 : count( $thumbs ) ),
                ( empty($thumbs) ? '' : implode( "\n", $thumbs ) ),
                ( empty($importOnly) ? 'false' : 'true' ),
                $scaled,
                $error
            ] );
        }
        return $success;
    }
    
    public function uploadMaster( $shouldUpload )
    {
        return false;
    }
    
    //endregion
    //region Regenerate Ajax Endpoint
    /**
     * Ajax endpoint for regenerating a single file
     */
    public function handleRegenerateFile()
    {
        if ( !is_admin() ) {
            wp_send_json( [
                'status'  => 'error',
                'message' => 'Invalid security credentials.',
            ], 400 );
        }
        if ( !isset( $_POST['post_id'] ) ) {
            wp_send_json( [
                'status'  => 'error',
                'message' => 'Missing post ID.',
            ], 400 );
        }
        $postId = intval( $_POST['post_id'] );
        if ( !current_user_can( 'edit_post', $postId ) ) {
            wp_send_json( [
                'status'  => 'error',
                'message' => 'User is attempting to edit a post that they do not have access to.',
            ], 400 );
        }
        $result = $this->regenerateFile( $postId );
        
        if ( $result === true ) {
            wp_send_json( [
                'status' => 'success',
            ] );
        } else {
            wp_send_json( [
                'status'  => 'error',
                'message' => $result,
            ], 400 );
        }
    
    }
    
    //endregion
    //region Other Plugin Migration
    private function prepareMigrateFromOtherPlugin()
    {
        $migratedFrom = get_option( 'mcloud-other-plugins-did-migrate' );
        
        if ( !empty($migratedFrom) ) {
            TaskManager::registerTask( MigrateFromOtherTask::class );
            add_action( 'wp_ajax_mcloud_migrate_from_other', [ $this, 'migrateFromOtherAjax' ] );
            $settingsUrl = admin_url( 'admin.php?page=media-cloud-settings&tab=storage' );
            $systemCheck = admin_url( 'admin.php?page=media-tools-troubleshooter' );
            $migrateNonce = wp_create_nonce( 'mcloud_migrate_from_other' );
            $migrationMessage = <<<MIGRATED
<h3>Welcome to Media Cloud!</h3>
<p>Media Cloud noticed you were using {$migratedFrom} and has migrated your settings automatically.  Everything should be working as before, but make sure to double check your <a href='{$settingsUrl}'>Cloud Storage</a> settings.  You may also want to run a <a href='{$systemCheck}'>System Check</a> to make sure everything is running properly.</p>
<p>Media Cloud will automatically migrate any media you uploaded with the other plugins as needed, however you may want to do a bulk migration first.</p>
<p><a href="#" class="button button-primary button-migrate-from-other" data-nonce="{$migrateNonce}">Migrate From {$migratedFrom} Now</a></p>
MIGRATED;
            NoticeManager::instance()->displayAdminNotice(
                'info',
                $migrationMessage,
                true,
                'mcloud-migrated-other-plugin',
                'forever'
            );
        }
    
    }
    
    /**
     * Generates a URL from a WP-Stateless Import
     *
     * @param $bucket
     * @param $key
     *
     * @return string|null
     * @throws StorageException
     */
    private function getStatelessURL( $bucket, $key )
    {
        if ( 'google' === StorageToolSettings::driver() && $bucket === $this->client->bucket() ) {
            return $this->client->url( $key );
        }
        return "https://storage.googleapis.com/{$bucket}/{$key}";
    }
    
    /**
     * Generates a URL from Offload import
     *
     * @param $provider
     * @param $region
     * @param $bucket
     * @param $key
     *
     * @return string|null
     *
     * @throws StorageException
     */
    private function getOffloadS3URL(
        string $provider,
        $region,
        $bucket,
        $key
    )
    {
        if ( $provider === StorageToolSettings::driver() && $bucket === $this->client->bucket() ) {
            return $this->client->url( $key );
        }
        
        if ( $provider === 's3' ) {
            if ( empty($region) ) {
                return "https://s3.amazonaws.com/{$bucket}/{$key}";
            }
            return "https://s3-{$region}.amazonaws.com/{$bucket}/{$key}";
        }
        
        if ( $provider === 'do' ) {
            return "https://{$region}.digitaloceanspaces.com/{$bucket}/{$key}";
        }
        if ( $provider === 'google' ) {
            return "https://storage.googleapis.com/{$bucket}/{$key}";
        }
        return null;
    }
    
    /**
     * Imports metadata from WP-Stateless
     *
     * @param $post_id
     * @param $meta
     * @param $statelessData
     *
     * @return mixed|null
     * @throws StorageException
     */
    private function importStatelessMetadata( $post_id, $meta, $statelessData )
    {
        $provider = 'google';
        $key = arrayPath( $statelessData, 'name', null );
        $bucket = arrayPath( $statelessData, 'bucket', null );
        if ( empty($bucket) || empty($key) ) {
            return null;
        }
        $mime = get_post_mime_type( $post_id );
        $hasMeta = !empty($meta);
        if ( empty($meta) ) {
            $meta = [];
        }
        $s3Info = [
            'url'       => $this->getStatelessURL( $bucket, $key ),
            'provider'  => $provider,
            'bucket'    => $bucket,
            'privacy'   => 'public-read',
            'key'       => $key,
            'v'         => MEDIA_CLOUD_INFO_VERSION,
            'mime-type' => $mime,
        ];
        $meta['s3'] = $s3Info;
        $sizes = $meta['sizes'];
        $statelessSizes = arrayPath( $statelessData, 'sizes', [] );
        $meta['sizes'] = [];
        foreach ( $statelessSizes as $statelessSize => $statelessSizeData ) {
            $sizeKey = arrayPath( $statelessSizeData, 'name' );
            if ( !isset( $sizes[$statelessSize] ) || empty($sizeKey) ) {
                continue;
            }
            $sizeData = $sizes[$statelessSize];
            $sizeS3Info = $s3Info;
            $sizeS3Info['url'] = $this->getStatelessURL( $bucket, $sizeKey );
            $sizeS3Info['key'] = $sizeKey;
            $sizeS3Info['mime-type'] = $sizeData['mime-type'];
            $sizeData['s3'] = $sizeS3Info;
            $meta['sizes'][$statelessSize] = $sizeData;
        }
        
        if ( $hasMeta ) {
            update_post_meta( $post_id, '_wp_attachment_metadata', $meta );
        } else {
            update_post_meta( $post_id, 'ilab_s3_info', [
                's3' => $s3Info,
            ] );
        }
        
        return $s3Info['url'];
    }
    
    /**
     * Imports metadata from WP Offload
     *
     * @param $post_id
     * @param $meta
     * @param $offloadS3Data
     *
     * @return mixed|null
     * @throws StorageException
     */
    private function importOffloadMetadata( $post_id, $meta, $offloadS3Data )
    {
        $provider = arrayPath( $offloadS3Data, 'provider', 's3' );
        $bucket = arrayPath( $offloadS3Data, 'bucket', null );
        $region = arrayPath( $offloadS3Data, 'region', null );
        $key = arrayPath( $offloadS3Data, 'key', null );
        if ( empty($provider) || empty($bucket) || empty($key) || !in_array( $provider, [
            's3',
            'aws',
            'do',
            'gcp'
        ] ) ) {
            return null;
        }
        $providerMap = [
            'aws' => 's3',
            's3'  => 's3',
            'do'  => 'do',
            'gcp' => 'google',
        ];
        $provider = $providerMap[$provider];
        $mime = get_post_mime_type( $post_id );
        $hasMeta = !empty($meta);
        if ( empty($meta) ) {
            $meta = [];
        }
        $s3Info = [
            'url'       => $this->getOffloadS3URL(
            $provider,
            $region,
            $bucket,
            $key
        ),
            'provider'  => $provider,
            'bucket'    => $bucket,
            'key'       => $key,
            'privacy'   => 'public-read',
            'v'         => MEDIA_CLOUD_INFO_VERSION,
            'mime-type' => $mime,
        ];
        if ( $provider !== 'google' ) {
            $s3Info['region'] = $region;
        }
        $meta['s3'] = $s3Info;
        
        if ( isset( $meta['sizes'] ) ) {
            $baseKey = ltrim( pathinfo( '/' . $key, PATHINFO_DIRNAME ), '/' );
            $newSizes = [];
            foreach ( $meta['sizes'] as $size => $sizeData ) {
                $sizeKey = trailingslashit( $baseKey ) . $sizeData['file'];
                $sizeS3Info = $s3Info;
                $sizeS3Info['url'] = $this->getOffloadS3URL(
                    $provider,
                    $region,
                    $bucket,
                    $sizeKey
                );
                $sizeS3Info['key'] = $sizeKey;
                $sizeS3Info['mime-type'] = $sizeData['mime-type'];
                $sizeData['s3'] = $sizeS3Info;
                $newSizes[$size] = $sizeData;
            }
            $meta['sizes'] = $newSizes;
        }
        
        
        if ( $hasMeta ) {
            update_post_meta( $post_id, '_wp_attachment_metadata', $meta );
        } else {
            update_post_meta( $post_id, 'ilab_s3_info', [
                's3' => $s3Info,
            ] );
        }
        
        return $s3Info['url'];
    }
    
    private function fixOffloadS3Meta( $postId, $meta )
    {
        if ( empty($meta['s3']) ) {
            return false;
        }
        $meta['s3']['provider'] = 's3';
        $mimetype = get_post_mime_type( $postId );
        $meta['s3']['mime-type'] = $mimetype;
        $s3Url = $meta['s3']['url'];
        if ( strpos( $s3Url, '//s3-.amazonaws' ) !== false ) {
            $s3Url = str_replace( '//s3-.amazonaws', '//s3.amazonaws', $s3Url );
        }
        $url = parse_url( $s3Url );
        $path = pathinfo( $url['path'] );
        $baseUrl = "{$url['scheme']}://{$url['host']}{$path['dirname']}/";
        $path = pathinfo( $meta['s3']['key'] );
        $baseKey = $path['dirname'] . '/';
        foreach ( $meta['sizes'] as $size => $sizeData ) {
            $sizeS3 = $meta['s3'];
            $sizeS3['url'] = $baseUrl . $sizeData['file'];
            $sizeS3['key'] = $baseKey . $sizeData['file'];
            $sizeS3['options'] = [];
            $sizeS3['mime-type'] = $sizeData['mime-type'];
            $sizeData['s3'] = $sizeS3;
            $meta['sizes'][$size] = $sizeData;
        }
        $shouldSkip = $this->skipUpdate;
        $this->skipUpdate = true;
        wp_update_attachment_metadata( $postId, $meta );
        $this->skipUpdate = $shouldSkip;
        return true;
    }
    
    private function loadOffloadMetadata( $post_id )
    {
        $offloadS3Info = get_post_meta( $post_id, 'amazonS3_info', true );
        if ( !empty($offloadS3Info) ) {
            return $offloadS3Info;
        }
        $schemaVersion = get_option( 'as3cf_schema_version' );
        if ( empty($schemaVersion) ) {
            return null;
        }
        if ( version_compare( $schemaVersion, '2.3', '<' ) ) {
            return null;
        }
        global  $wpdb ;
        /** @var array|null $info */
        $info = $wpdb->get_row( "select * from {$wpdb->prefix}as3cf_items where source_id={$post_id}", ARRAY_A );
        if ( !empty($info) ) {
            $info['key'] = $info['path'];
        }
        return $info;
    }
    
    public function migrateFromOtherAjax()
    {
        if ( !is_admin() || !current_user_can( 'manage_options' ) ) {
            wp_send_json( [
                'status'  => 'error',
                'message' => 'Invalid security credentials.',
            ], 400 );
        }
        NoticeManager::instance()->dismissAdminNotice( 'mcloud-migrated-other-plugin', 'forever' );
        $migrate = new MigrateFromOtherTask();
        $migrate->prepare();
        $migrate->wait();
        TaskRunner::dispatch( $migrate );
        wp_send_json( [
            'redirect' => admin_url( 'admin.php?page=media-cloud-task-manager' ),
        ] );
    }
    
    public function migratePostFromOtherPlugin( $postId )
    {
        $meta = wp_get_attachment_metadata( $postId );
        if ( empty($meta) ) {
            return false;
        }
        $offloadS3Info = $this->loadOffloadMetadata( $postId );
        $new_url = null;
        
        if ( !empty($offloadS3Info) ) {
            $new_url = $this->importOffloadMetadata( $postId, $meta, $offloadS3Info );
        } else {
            $statelessInfo = get_post_meta( $postId, 'sm_cloud', true );
            if ( !empty($statelessInfo) ) {
                $new_url = $this->importStatelessMetadata( $postId, $meta, $statelessInfo );
            }
        }
        
        return !empty($new_url);
    }
    
    //endregion
    //region Verification
    private function verifyLocal( $uploadDir, $url )
    {
        $attachmentFile = trailingslashit( $uploadDir['basedir'] ) . ltrim( str_replace( $uploadDir['baseurl'], '', $url ), '/' );
        
        if ( file_exists( $attachmentFile ) ) {
            return $url;
        } else {
            return 'Missing';
        }
    
    }
    
    private function getLocalUrl( $callback )
    {
        add_filter( 'media-cloud/storage/override-url', '__return_false', PHP_INT_MAX );
        add_filter( 'media-cloud/dynamic-images/skip-url-generation', '__return_true', PHP_INT_MAX );
        $callback();
        remove_filter( 'media-cloud/storage/override-url', '__return_false', PHP_INT_MAX );
        remove_filter( 'media-cloud/dynamic-images/skip-url-generation', '__return_true', PHP_INT_MAX );
    }
    
    /**
     * @param int $postId
     * @param boolean $includeLocal
     * @param TaskReporter $reporter
     * @param \Closure $infoCallback
     */
    public function verifyPost(
        $postId,
        $includeLocal,
        $reporter,
        $infoCallback
    )
    {
        $client = new Client();
        $allSizes = ilab_get_image_sizes();
        $sizeKeys = array_keys( $allSizes );
        sort( $sizeKeys );
        $sizesData = [];
        foreach ( $sizeKeys as $key ) {
            if ( $includeLocal ) {
                $sizesData[$key . ' Local'] = null;
            }
            $sizesData[$key] = null;
        }
        $reportLine = [ $postId ];
        $mimeType = get_post_mime_type( $postId );
        $reportLine[] = $mimeType;
        $metaFromAttachment = true;
        $meta = get_post_meta( $postId, '_wp_attachment_metadata', true );
        
        if ( empty($meta) || empty($meta['s3']) ) {
            $metaFromAttachment = false;
            $otherMeta = get_post_meta( $postId, 'ilab_s3_info', true );
            if ( !empty($otherMeta) ) {
                $meta = $otherMeta;
            }
        }
        
        
        if ( empty($meta) ) {
            $reportLine[] = 'Missing metadata';
            $reporter->add( $reportLine );
            $infoCallback( "Missing metadata.", true );
        }
        
        $processS3 = true;
        
        if ( empty($meta['s3']) ) {
            $reportLine[] = 'Missing S3 metadata';
            $processS3 = false;
            
            if ( !$includeLocal ) {
                $reporter->add( $reportLine );
                $infoCallback( "Missing S3 metadata.", true );
                return;
            }
        
        }
        
        $provider = arrayPath( $meta, 's3/provider', null );
        
        if ( !empty($provider) && $provider != StorageToolSettings::driver() ) {
            $processS3 = false;
            $reportLine[] = 'Wrong provider';
            
            if ( !$includeLocal ) {
                $reporter->add( $reportLine );
                $infoCallback( "S3 provider mismatch, is '{$provider}' expecting '" . StorageToolSettings::driver() . "'.", true );
                return;
            }
        
        }
        
        $providerWorked = 0;
        if ( $processS3 ) {
            
            if ( empty($provider) ) {
                $reportLine[] = 'S3 info exists, but provider is missing, trying with current provider';
            } else {
                $reportLine[] = 'S3 Info Exists';
            }
        
        }
        $uploadDir = wp_get_upload_dir();
        
        if ( !$processS3 ) {
            add_filter( 'media-cloud/storage/override-url', '__return_false', PHP_INT_MAX );
            add_filter( 'media-cloud/dynamic-images/skip-url-generation', '__return_true', PHP_INT_MAX );
            $infoCallback( "Checking attachment url ... " );
            $attachmentUrl = wp_get_attachment_url( $postId );
            $reportLine[] = $this->verifyLocal( $uploadDir, $attachmentUrl );
            $reportLine[] = '';
            $originalImage = wp_get_original_image_url( $postId );
            
            if ( !empty($originalImage) ) {
                $originalFile = wp_get_original_image_path( $postId );
                
                if ( file_exists( $originalFile ) ) {
                    $reportLine[] = $originalImage;
                    $reportLine[] = '';
                } else {
                    $reportLine[] = 'Missing';
                    $reportLine[] = '';
                }
            
            } else {
                $reportLine[] = '';
                $reportLine[] = '';
            }
            
            $sizes = arrayPath( $meta, 'sizes' );
            
            if ( !empty($sizes) ) {
                $infoCallback( "Checking sizes ... " );
                foreach ( $sizes as $sizeKey => $sizeInfo ) {
                    $url = wp_get_attachment_image_url( $postId, $sizeKey );
                    if ( empty($url) ) {
                        continue;
                    }
                    $result = $this->verifyLocal( $uploadDir, $url );
                    $sizesData[$sizeKey . ' Local'] = $result;
                }
                $reportLine = array_merge( $reportLine, array_values( $sizesData ) );
            }
            
            remove_filter( 'media-cloud/storage/override-url', '__return_false', PHP_INT_MAX );
            remove_filter( 'media-cloud/dynamic-images/skip-url-generation', '__return_true', PHP_INT_MAX );
        } else {
            $infoCallback( "Checking attachment url ... " );
            if ( $includeLocal ) {
                $this->getLocalUrl( function () use( $postId, $uploadDir, &$reportLine ) {
                    $attachmentUrl = wp_get_attachment_url( $postId );
                    $reportLine[] = $this->verifyLocal( $uploadDir, $attachmentUrl );
                } );
            }
            $attachmentUrl = wp_get_attachment_url( $postId );
            try {
                $res = $client->get( $attachmentUrl, [
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
            
            if ( in_array( $code, [ 200, 206 ] ) ) {
                $providerWorked++;
                $reportLine[] = $attachmentUrl;
            } else {
                $reportLine[] = "Missing, code: {$code}";
            }
            
            $originalUrl = null;
            
            if ( strpos( $mimeType, 'image' ) === 0 ) {
                
                if ( !empty(arrayPath( $meta, 'original_image' )) ) {
                    if ( $includeLocal ) {
                        $this->getLocalUrl( function () use( $postId, &$reportLine ) {
                            $originalImage = wp_get_original_image_url( $postId );
                            
                            if ( !empty($originalImage) ) {
                                $originalFile = wp_get_original_image_path( $postId );
                                
                                if ( file_exists( $originalFile ) ) {
                                    $reportLine[] = $originalImage;
                                } else {
                                    $reportLine[] = 'Missing';
                                }
                            
                            } else {
                                $reportLine[] = '';
                            }
                        
                        } );
                    }
                    $originalKey = arrayPath( $meta, 'original_image_s3/key' );
                    
                    if ( !empty($originalKey) ) {
                        try {
                            $privacy = arrayPath( $meta, 'original_image_s3/privacy', 'private' );
                            $infoCallback( "Checking original url ... " );
                            $doSign = $this->client->usesSignedURLs( $mimeType ) || $privacy !== 'public-read';
                            $originalUrl = ( $doSign ? $this->client->presignedUrl( $originalKey, $this->client->signedURLExpirationForType( $mimeType ) ) : $this->client->url( $originalKey, $mimeType ) );
                            
                            if ( $originalUrl == $attachmentUrl ) {
                                $reportLine[] = '';
                            } else {
                                try {
                                    $res = $client->get( $originalUrl, [
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
                                
                                if ( in_array( $code, [ 200, 206 ] ) ) {
                                    $providerWorked++;
                                    $reportLine[] = $originalUrl;
                                } else {
                                    $reportLine[] = "Missing, code: {$code}";
                                }
                            
                            }
                        
                        } catch ( \Exception $ex ) {
                            $reportLine[] = "Client error: " . $ex->getMessage();
                        }
                    } else {
                        $reportLine[] = "Missing original image S3 key.";
                    }
                
                } else {
                    if ( $includeLocal ) {
                        $reportLine[] = '';
                    }
                    $reportLine[] = '';
                }
                
                $sizes = arrayPath( $meta, 'sizes' );
                
                if ( !empty($sizes) ) {
                    $infoCallback( "Checking sizes ... " );
                    foreach ( $sizes as $sizeKey => $sizeInfo ) {
                        if ( $includeLocal ) {
                            $this->getLocalUrl( function () use(
                                $postId,
                                $uploadDir,
                                $sizeKey,
                                &$sizesData
                            ) {
                                $url = wp_get_attachment_image_url( $postId, $sizeKey );
                                if ( empty($url) ) {
                                    return;
                                }
                                $result = $this->verifyLocal( $uploadDir, $url );
                                $sizesData[$sizeKey . ' Local'] = $result;
                            } );
                        }
                        
                        if ( empty(arrayPath( $sizeInfo, 's3' )) ) {
                            $sizesData[$sizeKey] = 'Missing S3 metadata';
                            continue;
                        }
                        
                        $sizeS3Key = arrayPath( $sizeInfo, 's3/key' );
                        
                        if ( empty($sizeS3Key) ) {
                            $sizesData[$sizeKey] = 'Missing S3 key';
                            continue;
                        }
                        
                        $privacy = arrayPath( $sizeInfo, 's3/privacy', 'private' );
                        try {
                            $doSign = $this->client->usesSignedURLs( $mimeType ) || $privacy !== 'public-read';
                            $url = ( $doSign ? $this->client->presignedUrl( $sizeS3Key, $this->client->signedURLExpirationForType( $mimeType ) ) : $this->client->url( $sizeS3Key, $mimeType ) );
                            
                            if ( !empty($url) && ($url == $attachmentUrl || $url == $originalUrl) ) {
                                $reportLine[] = '';
                            } else {
                                try {
                                    $res = $client->get( $url, [
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
                                
                                if ( in_array( $code, [ 200, 206 ] ) ) {
                                    $providerWorked++;
                                    $sizesData[$sizeKey] = $url;
                                } else {
                                    $sizesData[$sizeKey] = "Missing, code: {$code}";
                                }
                            
                            }
                        
                        } catch ( \Exception $ex ) {
                            $sizesData[$sizeKey] = "Client error: " . $ex->getMessage();
                        }
                    }
                    $reportLine = array_merge( $reportLine, array_values( $sizesData ) );
                }
            
            }
            
            
            if ( empty($provider) && $providerWorked >= 1 ) {
                $reportLine[] = 'Fixed missing provider.';
                $meta['s3']['provider'] = StorageToolSettings::driver();
                
                if ( $metaFromAttachment ) {
                    update_post_meta( $postId, '_wp_attachment_metadata', $meta );
                } else {
                    update_post_meta( $postId, 'ilab_s3_info', $meta );
                }
            
            }
        
        }
        
        $reporter->add( $reportLine );
    }
    
    //endregion
    //region Local Sync
    /**
     * @param int $postId
     * @param TaskReporter $reporter
     * @param \Closure $infoCallback
     */
    public function syncLocal( $postId, $reporter, $infoCallback )
    {
        $client = new Client();
        $allSizes = ilab_get_image_sizes();
        $sizeKeys = array_keys( $allSizes );
        sort( $sizeKeys );
        $sizesData = [];
        foreach ( $sizeKeys as $key ) {
            $sizesData[$key] = null;
            $sizesData[$key . ' Local'] = null;
        }
        $reportLine = [ $postId ];
        $mimeType = get_post_mime_type( $postId );
        $reportLine[] = $mimeType;
        $metaFromAttachment = true;
        $meta = get_post_meta( $postId, '_wp_attachment_metadata', true );
        
        if ( empty($meta) || empty($meta['s3']) ) {
            $metaFromAttachment = false;
            $meta = get_post_meta( $postId, 'ilab_s3_info', true );
        }
        
        
        if ( empty($meta) || empty($meta['s3']) ) {
            $reportLine[] = 'Missing';
            $reporter->add( $reportLine );
            $infoCallback( "Missing S3 metadata.", true );
            return;
        }
        
        $provider = arrayPath( $meta, 's3/provider', null );
        
        if ( !empty($provider) && $provider != StorageToolSettings::driver() ) {
            $reportLine[] = 'Wrong provider';
            $reporter->add( $reportLine );
            $infoCallback( "S3 provider mismatch, is '{$provider}' expecting '" . StorageToolSettings::driver() . "'.", true );
            return;
        }
        
        $providerWorked = 0;
        
        if ( empty($provider) ) {
            $reportLine[] = 'S3 info exists, but provider is missing, trying with current provider';
        } else {
            $reportLine[] = 'S3 Info Exists';
        }
        
        $uploadDir = trailingslashit( wp_upload_dir()['basedir'] );
        $attachmentUrl = null;
        $attachmentKey = arrayPath( $meta, 's3/key' );
        
        if ( empty($attachmentKey) ) {
            $reportLine[] = 'Missing key';
            $reportLine[] = '';
        } else {
            $infoCallback( "Checking attachment url ... " );
            $attachmentUrl = wp_get_attachment_url( $postId );
            try {
                $res = $client->get( $attachmentUrl, [
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
            
            if ( in_array( $code, [ 200, 206 ] ) ) {
                $providerWorked++;
                $localFile = $uploadDir . $attachmentKey;
                $localFileDir = pathinfo( $localFile, PATHINFO_DIRNAME );
                if ( !file_exists( $localFileDir ) ) {
                    @mkdir( $localFileDir, 0755, true );
                }
                
                if ( !file_exists( $localFileDir ) ) {
                    $reportLine[] = "Could not create directory.";
                    $reportLine[] = '';
                    return;
                } else {
                    $client->get( $attachmentUrl, [
                        'sink' => $localFile,
                    ] );
                    $reportLine[] = $attachmentUrl;
                    $reportLine[] = $localFile;
                }
            
            } else {
                $reportLine[] = "Missing, code: {$code}";
                $reportLine[] = '';
            }
        
        }
        
        
        if ( strpos( $mimeType, 'image' ) === 0 ) {
            $originalUrl = null;
            
            if ( !empty(arrayPath( $meta, 'original_image' )) ) {
                $originalKey = arrayPath( $meta, 'original_image_s3/key' );
                
                if ( empty($originalKey) ) {
                    $reportLine[] = 'Missing key';
                    $reportLine[] = '';
                } else {
                    try {
                        $privacy = arrayPath( $meta, 'original_image_s3/privacy', 'private' );
                        $infoCallback( "Checking original url ... " );
                        $doSign = $this->client->usesSignedURLs( $mimeType ) || $privacy !== 'public-read';
                        $originalUrl = ( $doSign ? $this->client->presignedUrl( $originalKey, $this->client->signedURLExpirationForType( $mimeType ) ) : $this->client->url( $originalKey, $mimeType ) );
                        
                        if ( $originalUrl != $attachmentUrl ) {
                            $reportLine[] = '';
                            $reportLine[] = '';
                        } else {
                            try {
                                $res = $client->get( $originalUrl, [
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
                            
                            if ( in_array( $code, [ 200, 206 ] ) ) {
                                $providerWorked++;
                                $localFile = $uploadDir . $originalKey;
                                $localFileDir = pathinfo( $localFile, PATHINFO_DIRNAME );
                                if ( !file_exists( $localFileDir ) ) {
                                    @mkdir( $localFileDir, 0755, true );
                                }
                                
                                if ( !file_exists( $localFileDir ) ) {
                                    $reportLine[] = "Could not create directory.";
                                    $reportLine[] = '';
                                    return;
                                } else {
                                    $client->get( $originalUrl, [
                                        'sink' => $localFile,
                                    ] );
                                    $reportLine[] = $originalUrl;
                                    $reportLine[] = $localFile;
                                }
                            
                            } else {
                                $reportLine[] = "Missing, code: {$code}";
                                $reportLine[] = '';
                            }
                        
                        }
                    
                    } catch ( \Exception $ex ) {
                        $reportLine[] = "Client error: " . $ex->getMessage();
                        $reportLine[] = '';
                    }
                }
            
            } else {
                $reportLine[] = '';
                $reportLine[] = '';
            }
            
            $sizes = arrayPath( $meta, 'sizes' );
            
            if ( !empty($sizes) ) {
                $infoCallback( "Checking sizes ... " );
                foreach ( $sizes as $sizeKey => $sizeInfo ) {
                    
                    if ( empty(arrayPath( $sizeInfo, 's3' )) ) {
                        $sizesData[$sizeKey] = 'Missing S3 metadata';
                        $sizesData[$sizeKey . ' Local'] = '';
                        continue;
                    }
                    
                    $sizeS3Key = arrayPath( $sizeInfo, 's3/key' );
                    
                    if ( empty($sizeS3Key) ) {
                        $sizesData[$sizeKey] = 'Missing S3 key';
                        $sizesData[$sizeKey . ' Local'] = '';
                        continue;
                    }
                    
                    $privacy = arrayPath( $sizeInfo, 's3/privacy', 'private' );
                    try {
                        $doSign = $this->client->usesSignedURLs( $mimeType ) || $privacy !== 'public-read';
                        $url = ( $doSign ? $this->client->presignedUrl( $sizeS3Key, $this->client->signedURLExpirationForType( $mimeType ) ) : $this->client->url( $sizeS3Key, $mimeType ) );
                        
                        if ( !empty($url) && ($url == $attachmentUrl || $url == $originalUrl) ) {
                            $sizesData[$sizeKey] = '';
                            $sizesData[$sizeKey . ' Local'] = '';
                        } else {
                            try {
                                $res = $client->get( $url, [
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
                            
                            if ( in_array( $code, [ 200, 206 ] ) ) {
                                $providerWorked++;
                                $localFile = $uploadDir . $sizeS3Key;
                                $localFileDir = pathinfo( $localFile, PATHINFO_DIRNAME );
                                if ( !file_exists( $localFileDir ) ) {
                                    @mkdir( $localFileDir, 0755, true );
                                }
                                
                                if ( !file_exists( $localFileDir ) ) {
                                    $sizesData[$sizeKey] = "Could not create directory.";
                                    $sizesData[$sizeKey . ' Local'] = '';
                                    return;
                                } else {
                                    $client->get( $url, [
                                        'sink' => $localFile,
                                    ] );
                                    $sizesData[$sizeKey] = $url;
                                    $sizesData[$sizeKey . ' Local'] = $localFile;
                                }
                            
                            } else {
                                $sizesData[$sizeKey] = "Missing, code: {$code}";
                            }
                        
                        }
                    
                    } catch ( \Exception $ex ) {
                        $sizesData[$sizeKey] = "Client error: " . $ex->getMessage();
                        $sizesData[$sizeKey . ' Local'] = '';
                    }
                }
                $reportLine = array_merge( $reportLine, array_values( $sizesData ) );
            }
        
        }
        
        $updateMeta = false;
        $fixedSizes = [];
        $sizesToFix = arrayPath( $meta, 'sizes', [] );
        
        if ( count( $sizesToFix ) > 0 ) {
            foreach ( $sizesToFix as $sizeName => $sizeData ) {
                $key = arrayPath( $sizeData, 's3/key' );
                
                if ( !empty($key) ) {
                    $fname = basename( $key );
                    
                    if ( arrayPath( $sizeData, 'file', null ) !== $fname ) {
                        $sizeData['file'] = $fname;
                        $updateMeta = true;
                    }
                
                }
                
                $fixedSizes[$sizeName] = $sizeData;
            }
            $meta['sizes'] = $fixedSizes;
        }
        
        
        if ( empty($provider) && $providerWorked >= 1 ) {
            $reportLine[] = 'Fixed missing provider.';
            $meta['s3']['provider'] = StorageToolSettings::driver();
            $updateMeta = true;
        }
        
        $key = arrayPath( $meta, 's3/key', null );
        if ( !empty($key) ) {
            
            if ( arrayPath( $meta, 'file', null ) !== $key ) {
                $meta['file'] = $key;
                $updateMeta = true;
                update_post_meta( $postId, '_wp_attached_file', $key );
            }
        
        }
        if ( $updateMeta ) {
            
            if ( $metaFromAttachment ) {
                update_post_meta( $postId, '_wp_attachment_metadata', $meta );
            } else {
                update_post_meta( $postId, 'ilab_s3_info', $meta );
            }
        
        }
        $reporter->add( $reportLine );
    }

}