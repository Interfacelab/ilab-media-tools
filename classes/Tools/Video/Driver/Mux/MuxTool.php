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

use  MediaCloud\Plugin\Tasks\TaskManager ;
use  MediaCloud\Plugin\Tools\Storage\StorageTool ;
use  MediaCloud\Plugin\Tools\Video\Driver\Mux\Data\MuxDatabase ;
use  MediaCloud\Plugin\Tools\Video\Driver\Mux\Models\MuxAsset ;
use  MediaCloud\Plugin\Tools\Video\Driver\Mux\Tasks\MigrateToMuxTask ;
use  MediaCloud\Plugin\Tools\Tool ;
use  MediaCloud\Plugin\Tools\ToolsManager ;
use  MediaCloud\Plugin\Utilities\Logging\Logger ;
use  MediaCloud\Plugin\Utilities\NoticeManager ;
use  MediaCloud\Plugin\Utilities\View ;
use function  MediaCloud\Plugin\Utilities\anyEmpty ;
use function  MediaCloud\Plugin\Utilities\arrayPath ;
use function  MediaCloud\Plugin\Utilities\ilab_set_time_limit ;
class MuxTool extends Tool
{
    /** @var null|MuxToolSettings|MuxToolProSettings */
    protected  $settings = null ;
    /** @var MuxHooks */
    protected  $hooks = null ;
    private  $muxIconSVG = null ;
    private  $hlsIconSVG = null ;
    public function __construct( $toolName, $toolInfo, $toolManager )
    {
        $this->settings = MuxToolSettings::instance();
        add_action(
            'media-cloud/tools/register-setting-type',
            [ $this, 'registerMuxSettingTypes' ],
            10,
            5
        );
        parent::__construct( $toolName, $toolInfo, $toolManager );
    }
    
    //region Tool Overrides
    public function enabled()
    {
        $enabled = parent::enabled();
        if ( empty($this->settings->tokenID) || empty($this->settings->tokenSecret) ) {
            return false;
        }
        if ( empty($this->settings->webhookSecret) ) {
            return false;
        }
        return $enabled;
    }
    
    public function hasSettings()
    {
        return true;
    }
    
    public function setup()
    {
        
        if ( $this->enabled() ) {
            MuxDatabase::init();
            $this->hooks = new MuxHooks();
            
            if ( is_admin() ) {
                add_action( 'admin_enqueue_scripts', function () {
                    wp_enqueue_script(
                        'mux-admin-js',
                        ILAB_PUB_JS_URL . '/mux-admin.js',
                        null,
                        null,
                        true
                    );
                    wp_enqueue_style( 'mux-admin-css', ILAB_PUB_CSS_URL . '/mux-admin.css' );
                } );
                $this->integrateWithMediaLibrary();
                $this->integrateWithAdmin();
            }
            
            $this->integrateREST();
            if ( $this->settings->deleteFromMux ) {
                add_action( 'delete_attachment', [ $this, 'deleteAttachment' ], 999 );
            }
        }
    
    }
    
    //endregion
    //region Properties
    /**
     * @return MuxHooks
     */
    public function hooks()
    {
        return $this->hooks;
    }
    
    //endregion
    // region Settings
    public function registerMuxSettingTypes(
        $option,
        $optionInfo,
        $group,
        $groupInfo,
        $conditions
    )
    {
        
        if ( $optionInfo['type'] === 'mux-webhook' ) {
            $description = arrayPath( $optionInfo, 'description', null );
            add_settings_field(
                $option,
                $optionInfo['title'],
                [ $this, 'renderWebhookField' ],
                $this->options_page,
                $group,
                [
                'option'      => $option,
                'description' => $description,
                'conditions'  => $conditions,
            ]
            );
        }
    
    }
    
    public function renderWebhookField( $args )
    {
        echo  View::render_view( 'settings.fields.mux-webhook', [
            'value'       => home_url( '/__mux/webhook' ),
            'name'        => $args['option'],
            'conditions'  => $args['conditions'],
            'description' => ( isset( $args['description'] ) ? $args['description'] : false ),
        ] ) ;
    }
    
    public function providerHelp()
    {
        return [
            'mux' => [ [
            'title'        => 'Sign Up For Mux Account',
            'external_url' => 'https://mux.com',
        ], [
            'title' => 'Read Documentation',
            'url'   => 'https://support.mediacloud.press/articles/documentation/video-encoding/about-video-encoding',
        ] ],
        ];
    }
    
    //endregion
    //region Integration
    protected function actionCaptionDelete()
    {
        $nonce = arrayPath( $_POST, 'nonce' );
        if ( empty($nonce) || !wp_verify_nonce( $nonce, 'mux-delete-caption' ) ) {
            wp_send_json( [
                'status'  => 'error',
                'message' => 'Missing nonce.',
            ], 400 );
        }
        $aid = (int) arrayPath( $_POST, 'aid', null );
        $trackId = arrayPath( $_POST, 'trackId', null );
        if ( anyEmpty( $aid, $trackId ) ) {
            wp_send_json( [
                'status'  => 'error',
                'message' => 'Missing parameters.',
            ], 400 );
        }
        /** @var MuxAsset $asset */
        $asset = MuxAsset::instance( $aid );
        if ( empty($asset) ) {
            wp_send_json( [
                'status'  => 'error',
                'message' => 'Invalid asset ID.',
            ], 400 );
        }
        if ( $asset->deleteCaptions( $trackId ) ) {
            wp_send_json( [
                'status' => 'ok',
            ], 200 );
        }
        wp_send_json( [
            'status'  => 'error',
            'message' => 'Unknown error.',
        ], 400 );
    }
    
    protected function actionCaptionUpload()
    {
        $nonce = arrayPath( $_POST, 'nonce' );
        if ( empty($nonce) || !wp_verify_nonce( $nonce, 'mux-upload-caption' ) ) {
            wp_send_json( [
                'status'  => 'error',
                'message' => 'Missing nonce.',
            ], 400 );
        }
        $aid = (int) arrayPath( $_POST, 'aid', null );
        if ( empty($aid) ) {
            wp_send_json( [
                'status'  => 'error',
                'message' => 'Missing asset ID.',
            ], 400 );
        }
        /** @var MuxAsset $asset */
        $asset = MuxAsset::instance( $aid );
        if ( empty($asset) ) {
            wp_send_json( [
                'status'  => 'error',
                'message' => 'Invalid asset ID.',
            ], 400 );
        }
        $language = arrayPath( $_POST, 'language', null );
        if ( empty($language) ) {
            wp_send_json( [
                'status'  => 'error',
                'message' => 'Missing language.',
            ], 400 );
        }
        $cc = !empty((int) arrayPath( $_POST, 'cc', null ));
        $allowedMimes = ( $asset->isTransferred ? [ 'text/plain', 'text/vtt' ] : [ 'text/vtt', 'text/plain', 'text/srt' ] );
        $finfo = new \finfo( FILEINFO_MIME );
        $info = $finfo->file( $_FILES['file']['tmp_name'] );
        $infoParts = explode( ';', $info );
        $mimeType = array_shift( $infoParts );
        
        if ( !in_array( $mimeType, $allowedMimes ) ) {
            Logger::error(
                "Invalid captions mime type: {$mimeType}",
                [],
                __METHOD__,
                __LINE__
            );
            wp_send_json( [
                'status'  => 'error',
                'message' => "Invalid file type {$mimeType}",
            ], 400 );
        }
        
        $uploadedFile = wp_upload_bits( $_FILES['file']['name'], null, file_get_contents( $_FILES['file']['tmp_name'] ) );
        
        if ( isset( $uploadedFile['error'] ) && !empty($uploadedFile['error']) ) {
            Logger::error(
                "Error importing caption: {$uploadedFile['error']}",
                [],
                __METHOD__,
                __LINE__
            );
            wp_send_json( [
                'status'  => 'error',
                'message' => 'Error importing captions',
            ], 400 );
        }
        
        if ( $asset->addCaptions( $language, $uploadedFile['url'], $cc ) ) {
            wp_send_json( [
                'status' => 'ok',
            ], 200 );
        }
        wp_send_json( [
            'status'  => 'error',
            'message' => 'Unknown error.',
        ], 400 );
    }
    
    protected function integrateWithAdmin()
    {
        
        if ( current_user_can( 'manage_options' ) ) {
            add_action( 'wp_ajax_mux-upload-caption', function () {
                $this->actionCaptionUpload();
            } );
            add_action( 'wp_ajax_mux-delete-caption', function () {
                $this->actionCaptionDelete();
            } );
        }
        
        add_action( 'admin_init', function () {
            add_meta_box(
                'mcloud-mux-meta',
                'Mux Info',
                function ( $post ) {
                /** @var \WP_Post $post */
                $asset = MuxAsset::assetForAttachment( $post->ID );
                echo  View::render_view( 'admin.mux-properties', [
                    'asset' => $asset,
                ] ) ;
            },
                'attachment',
                'side',
                'low'
            );
        } );
    }
    
    protected function integrateWithMediaLibrary()
    {
        add_filter(
            'wp_prepare_attachment_for_js',
            [ $this, 'prepareAttachmentForJS' ],
            1000,
            3
        );
        
        if ( ToolsManager::instance()->toolEnabled( 'storage' ) ) {
            add_filter( 'media-cloud/media-library/attachment-classes', function ( $additionalClasses ) {
                $additionalClasses = '<# if (data.hasOwnProperty("hls")) {#>has-hls<#}#>' . $additionalClasses;
                $additionalClasses = '<# if (data.hasOwnProperty("mux")) {#>has-mux mux-status-{{data.mux.status}}<#}#>' . $additionalClasses;
                return $additionalClasses;
            } );
            add_filter( 'media-cloud/media-library/attachment-icons', function ( $additionalIcons ) {
                $muxIcon = '<i class="mux-status-icon"></i>';
                $hlsIcon = '<i class="hls-icon"></i>';
                return $hlsIcon . $muxIcon . $additionalIcons;
            } );
        } else {
            $this->hookMediaLibraryGrid();
        }
        
        add_filter( 'manage_media_columns', function ( $cols ) {
            $cols["cloud"] = 'Cloud';
            return $cols;
        } );
        add_action(
            'manage_media_custom_column',
            function ( $column_name, $id ) {
            $asset = MuxAsset::assetForAttachment( $id );
            if ( !$asset ) {
                return;
            }
            
            if ( $column_name == "cloud" ) {
                $muxIcon = $this->muxIcon();
                $hlsIcon = $this->hlsIcon();
                
                if ( !$asset->isTransferred ) {
                    echo  "<img src='{$muxIcon}' width='24'></a>" ;
                } else {
                    echo  "<img src='{$hlsIcon}' width='35'></a>" ;
                }
            
            }
        
        },
            PHP_INT_MAX - 8,
            2
        );
        $this->hookMediaDetails();
    }
    
    private function muxIcon()
    {
        
        if ( $this->muxIconSVG === null ) {
            $svg = file_get_contents( ILAB_PUB_IMG_DIR . '/logo-mux-white.svg' );
            $this->muxIconSVG = 'data:image/svg+xml;base64,' . base64_encode( $svg );
        }
        
        return $this->muxIconSVG;
    }
    
    private function hlsIcon()
    {
        
        if ( $this->hlsIconSVG === null ) {
            $svg = file_get_contents( ILAB_PUB_IMG_DIR . '/logo-hls.svg' );
            $this->hlsIconSVG = 'data:image/svg+xml;base64,' . base64_encode( $svg );
        }
        
        return $this->hlsIconSVG;
    }
    
    private function hookMediaLibraryGrid()
    {
        add_action( 'admin_footer', function () {
            ?>
			<script>
                jQuery(document).ready(function () {
                    var attachTemplate = jQuery('#tmpl-attachment');
                    if (attachTemplate) {
                        var txt = attachTemplate.text();

                        var search = '<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }}">';
                        var replace = '<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }} <# if (data.hasOwnProperty("mux")) {#>has-mux mux-status-{{data.mux.status}}<#}#>"><i class="mux-status-icon"></i>';
                        txt = txt.replace(search, replace);
                        attachTemplate.text(txt);
                    }

                    var attachTemplate = jQuery('#tmpl-attachment-grid-view');
                    if (attachTemplate) {
                        var txt = attachTemplate.text();

                        var search = '<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }}">';
                        var replace = '<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }} <# if (data.hasOwnProperty("mux")) {#>has-mux mux-status-{{data.mux.status}}<#}#>"><i class="mux-status-icon"></i>';
                        txt = txt.replace(search, replace);
                        attachTemplate.text(txt);
                    }
                });
			</script>
			<?php 
        } );
    }
    
    private function hookMediaDetails()
    {
        add_filter(
            'media_row_actions',
            function ( $actions, $post ) {
            
            if ( strpos( $post->post_mime_type, 'video' ) === 0 ) {
                $nonce = wp_create_nonce( 'media-cloud-mux-replace-poster' );
                $newaction['mcloud_mux_replace_poster'] = '<a data-nonce="' . $nonce . '" data-attachment-id="' . $post->ID . '" class="replace-video-poster" href="#" title="Replace Poster">' . __( 'Replace Poster' ) . '</a>';
                return array_merge( $actions, $newaction );
            }
            
            return $actions;
        },
            10,
            2
        );
        add_filter( 'mediacloud/ui/media-detail-buttons', function ( $buttons ) {
            $buttons[] = [
                'type'     => 'video',
                'class'    => 'replace-video-poster',
                'thickbox' => false,
                'data'     => [
                'nonce'         => wp_create_nonce( 'media-cloud-mux-replace-poster' ),
                'attachment-id' => '{{data.id}}',
            ],
                'label'    => __( 'Replace Poster' ),
                'url'      => '#',
            ];
            return $buttons;
        }, 2 );
        add_filter( 'mediacloud/ui/media-detail-links', function ( $links ) {
            $links[] = [
                'type'     => 'video',
                'class'    => 'replace-video-poster',
                'thickbox' => false,
                'label'    => __( 'Replace Poster' ),
                'data'     => [
                'nonce'         => wp_create_nonce( 'media-cloud-mux-replace-poster' ),
                'attachment-id' => '{{data.id}}',
            ],
                'url'      => '#',
            ];
            return $links;
        }, 2 );
    }
    
    /**
     * Generate the url for selecting the poster
     *
     * @param $id
     * @return string
     */
    public function posterSelectURL( $id, $partial = false )
    {
        $url = parse_url( get_admin_url( null, 'admin-ajax.php' ), PHP_URL_PATH ) . "?action=mcloud_poster_select&post={$id}";
        if ( $partial === true ) {
            $url .= '&partial=1';
        }
        return $url;
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
        if ( empty($meta) || !isset( $meta['mux'] ) ) {
            return $response;
        }
        $mux = $meta['mux'];
        $asset = null;
        $muxId = arrayPath( $mux, 'muxId', null );
        if ( $muxId ) {
            $asset = MuxAsset::asset( $muxId );
        }
        
        if ( $asset && ($asset->isTransferred == 1 || $asset->isDeleted == 1) ) {
            if ( $asset->isTransferred ) {
                $response['hls'] = [
                    'url'       => explode( '?', $asset->videoUrl() )[0],
                    'subtitles' => $asset->subtitles,
                ];
            }
            return $response;
        } else {
            
            if ( $asset ) {
                $mux['url'] = $asset->videoUrl();
                $mux['subtitles'] = [];
            }
        
        }
        
        if ( !empty($_REQUEST['post_id']) ) {
            try {
                if ( $asset === null ) {
                    return $response;
                }
                $mux['src'] = $asset->videoUrl( false );
                $mux['gif'] = $asset->gifUrl( false );
            } catch ( \Exception $ex ) {
                Logger::error(
                    "Mux: Exception fetching Mux Asset {$mux['muxId']}: " . $ex->getMessage(),
                    [],
                    __METHOD__,
                    __LINE__
                );
                return $response;
            }
        }
        $response['mux'] = $mux;
        return $response;
    }
    
    public function deleteAttachment( $id )
    {
        $asset = MuxAsset::assetForAttachment( $id );
        if ( $asset === null ) {
            return $id;
        }
        if ( !$this->settings->deleteFromMux ) {
            return $id;
        }
        
        if ( $asset->isTransferred && is_array( $asset->relatedFiles ) ) {
            ilab_set_time_limit( 0 );
            /** @var StorageTool $storageTool */
            $storageTool = ( ToolsManager::instance()->toolEnabled( 'storage' ) ? ToolsManager::instance()->tools['storage'] : null );
            $uploadDirs = wp_upload_dir();
            foreach ( $asset->relatedFiles as $file ) {
                $filepath = trailingslashit( $uploadDirs['basedir'] ) . $file;
                if ( file_exists( $filepath ) ) {
                    @unlink( $filepath );
                }
                if ( $asset->transferData && $storageTool && $asset->transferData['source'] === 's3' ) {
                    try {
                        $storageTool->client()->delete( $file );
                    } catch ( \Exception $ex ) {
                        Logger::error(
                            "Mux: Exception deleting file {$file} from S3: " . $ex->getMessage(),
                            [],
                            __METHOD__,
                            __LINE__
                        );
                    }
                }
            }
        }
        
        try {
            MuxAPI::assetAPI()->deleteAsset( $asset->muxId );
        } catch ( \Exception $ex ) {
            Logger::error(
                'Mux: Error deleting asset from Mux: ' . $ex->getMessage(),
                [],
                __METHOD__,
                __LINE__
            );
        }
        $asset->delete();
        return $id;
    }
    
    //endregion
    //region REST
    private function integrateREST()
    {
        add_action( 'rest_api_init', function () {
            register_rest_field( 'attachment', 'hls', [
                'get_callback'    => function ( $data ) {
                $attachment = $data['id'];
                $asset = MuxAsset::assetForAttachment( $attachment );
                if ( !$asset ) {
                    return null;
                }
                return [
                    'playlist'  => $asset->videoUrl(),
                    'poster'    => get_the_post_thumbnail_url( $attachment, 'full' ),
                    'filmstrip' => $asset->filmstripUrl,
                    'mp4'       => $asset->renditionUrl( $this->settings->playerMP4Quality ),
                    'subtitles' => $asset->subtitles,
                    'width'     => (int) $asset->width,
                    'height'    => (int) $asset->height,
                    'duration'  => floatval( $asset->duration ),
                ];
            },
                'update_callback' => null,
                'schema'          => null,
            ] );
        } );
    }

}