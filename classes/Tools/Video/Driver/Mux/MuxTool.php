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
class MuxTool extends Tool
{
    /** @var null|MuxToolSettings|MuxToolProSettings */
    protected  $settings = null ;
    /** @var MuxHooks */
    protected  $hooks = null ;
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
        $finfo = new \finfo( FILEINFO_MIME );
        $info = $finfo->file( $_FILES['file']['tmp_name'] );
        $infoParts = explode( ';', $info );
        $mimeType = array_shift( $infoParts );
        
        if ( !in_array( $mimeType, [ 'text/plain', ' text/vtt', 'text/srt' ] ) ) {
            Logger::error(
                "Invalid captions mime type: {$mimeType}",
                [],
                __METHOD__,
                __LINE__
            );
            wp_send_json( [
                'status'  => 'error',
                'message' => 'Invalid file type',
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
                $additionalClasses = '<# if (data.hasOwnProperty("mux")) {#>has-mux mux-status-{{data.mux.status}}<#}#>' . $additionalClasses;
                return $additionalClasses;
            } );
            add_filter( 'media-cloud/media-library/attachment-icons', function ( $additionalIcons ) {
                $muxIcon = '<i class="mux-status-icon"></i>';
                return $muxIcon . $additionalIcons;
            } );
        } else {
            $this->hookMediaLibraryGrid();
        }
        
        if ( $this->settings->deleteFromMux ) {
            add_action( 'delete_attachment', [ $this, 'deleteAttachment' ], 999 );
        }
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
        if ( !empty($_REQUEST['post_id']) ) {
            try {
                $asset = MuxAsset::asset( $mux['muxId'] );
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
        if ( !$this->settings->deleteFromMux ) {
            return $id;
        }
        $data = wp_get_attachment_metadata( $id );
        $muxId = arrayPath( $data, 'mux/muxId', null );
        if ( empty($muxId) ) {
            return $id;
        }
        $asset = MuxAsset::asset( $muxId );
        if ( $asset === null ) {
            return $asset;
        }
        try {
            MuxAPI::assetAPI()->deleteAsset( $muxId );
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

}