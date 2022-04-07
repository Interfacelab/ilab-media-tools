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
namespace MediaCloud\Plugin\Tools\Video\Player;

use  Elementor\Elements_Manager ;
use  Elementor\Plugin ;
use  MediaCloud\Plugin\Tools\ToolsManager ;
use  MediaCloud\Plugin\Tools\Video\Driver\Mux\Data\MuxDatabase ;
use  MediaCloud\Plugin\Tools\Video\Driver\Mux\Elementor\MuxVideoWidget ;
use  MediaCloud\Plugin\Tools\Tool ;
use  MediaCloud\Plugin\Tools\Video\Driver\Mux\Models\MuxAsset ;
use  MediaCloud\Plugin\Utilities\View ;
use function  MediaCloud\Plugin\Utilities\arrayPath ;
use function  MediaCloud\Plugin\Utilities\gen_uuid ;
use function  MediaCloud\Plugin\Utilities\postIdExists ;
class VideoPlayerTool extends Tool
{
    /** @var null|VideoPlayerToolSettings|VideoPlayerToolProSettings */
    protected  $settings = null ;
    /** @var null|VideoPlayerShortcode */
    protected  $shortCode = null ;
    public function __construct( $toolName, $toolInfo, $toolManager )
    {
        $this->settings = VideoPlayerToolSettings::instance();
        parent::__construct( $toolName, $toolInfo, $toolManager );
    }
    
    //region Tool Overrides
    public function hasSettings()
    {
        return true;
    }
    
    public function setup()
    {
        
        if ( $this->enabled() ) {
            MuxDatabase::init();
            $this->shortCode = new VideoPlayerShortcode();
            add_filter(
                'render_block',
                [ $this, 'filterBlocks' ],
                PHP_INT_MAX - 1,
                2
            );
            static::enqueuePlayer( is_admin() );
            
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
                $this->integrateWithAdmin();
            }
            
            $this->initBlocks();
        }
    
    }
    
    //endregion
    //region UI
    private function integrateWithAdmin()
    {
        add_action( 'add_meta_boxes_attachment', function ( $post ) {
            add_meta_box(
                'mcloud-video-filmstrip',
                'Filmstrip',
                function ( $post ) {
                /** @var \WP_Post $post */
                echo  View::render_view( 'admin.video.filmstrip', [
                    'post' => $post,
                ] ) ;
            },
                'attachment',
                'side',
                'low'
            );
        } );
    }
    
    //endregion
    //region Subtitle actions
    private function actionCaptionUpload()
    {
    }
    
    private function actionCaptionDelete()
    {
    }
    
    private function actionSetDefaultCaption()
    {
    }
    
    //endregion
    //region Player
    public static function enqueuePlayer( $admin = false )
    {
        add_action( ( !empty($admin) ? 'admin_enqueue_scripts' : 'wp_enqueue_scripts' ), function () {
            wp_enqueue_script(
                'mux_video_player_hlsjs',
                ILAB_PUB_JS_URL . '/mux-hls.js',
                null,
                null,
                true
            );
        } );
    }
    
    //endregion
    //region Blocks
    protected function initBlocks()
    {
        add_action( 'init', function () {
            register_block_type( ILAB_BLOCKS_DIR . '/mediacloud-video-block' );
            //			register_block_type( ILAB_BLOCKS_DIR . '/mediacloud-video-block/build' );
        } );
        add_filter(
            'block_categories',
            function ( $categories, $post ) {
            foreach ( $categories as $category ) {
                if ( $category['slug'] === 'mediacloud' ) {
                    return $categories;
                }
            }
            $categories[] = [
                'slug'  => 'mediacloud',
                'title' => 'Media Cloud',
                'icon'  => null,
            ];
            return $categories;
        },
            10,
            2
        );
        
        if ( class_exists( 'Elementor\\Plugin' ) ) {
            add_action( 'elementor/widgets/widgets_registered', function () {
                Plugin::instance()->widgets_manager->register_widget_type( new MuxVideoWidget() );
            } );
            add_action(
                'elementor/elements/categories_registered',
                function ( $elementsManager ) {
                /** @var Elements_Manager $elementsManager */
                $elementsManager->add_category( 'media-cloud', [
                    'title' => 'Media Cloud',
                    'icon'  => 'fa fa-plug',
                ] );
            },
                10,
                1
            );
            add_filter(
                'the_content',
                function ( $content ) {
                return MuxVideoWidget::filterContent( $content );
            },
                PHP_INT_MAX,
                1
            );
            add_action( 'wp_enqueue_scripts', function () {
                wp_enqueue_style(
                    'mcloud-elementor',
                    trailingslashit( ILAB_PUB_CSS_URL ) . 'mcloud-elementor.css',
                    [],
                    MEDIA_CLOUD_VERSION
                );
            } );
        }
    
    }
    
    //endregion
    //region Content Filters
    /**
     * Filters the File block to include the goddamn attachment ID
     *
     * @param $block_content
     * @param $block
     *
     * @return mixed
     * @throws \Exception
     */
    function filterBlocks( $block_content, $block )
    {
        if ( isset( $block['blockName'] ) ) {
            if ( $block['blockName'] === 'media-cloud/mux-video-block' ) {
                return $this->filterVideoBlock( $block_content, $block );
            }
        }
        return $block_content;
    }
    
    protected function filterVideoBlock( $block_content, $block )
    {
        $attachmentId = arrayPath( $block, 'attrs/id', null );
        if ( empty($attachmentId) || !postIdExists( $attachmentId ) ) {
            return '';
        }
        $asset = null;
        $muxId = arrayPath( $block, 'attrs/muxId', null );
        if ( !empty($muxId) ) {
            $asset = MuxAsset::asset( $muxId );
        }
        $classes = "mux-player";
        $extras = "";
        $metadata = [];
        $metadataKey = sanitize_title( gen_uuid( 12 ) );
        $meta = wp_get_attachment_metadata( $attachmentId );
        if ( !empty($this->settings->playerCSSClasses) ) {
            $classes .= " {$this->settings->playerCSSClasses}";
        }
        $block_content = str_replace( '<video ', "<video class='{$classes}' {$extras} ", $block_content );
        
        if ( empty($asset) ) {
            $url = wp_get_attachment_url( $attachmentId );
            $mime = arrayPath( $meta, 'mime_type', null );
            
            if ( $mime === 'video/quicktime' ) {
                $fileformat = arrayPath( $meta, 'fileformat', null );
                if ( !empty($fileformat) && $fileformat === 'mp4' ) {
                    $mime = 'video/mp4';
                }
            }
            
            
            if ( !empty($mime) ) {
                $source = "<source src='{$url}' type='{$mime}' />";
            } else {
                $source = "<source src='{$url}' />";
            }
        
        } else {
            $url = $asset->videoUrl();
            $source = "<source src='{$url}' type='application/x-mpegURL' />";
        }
        
        $block_content = str_replace( '<source/>', $source, $block_content );
        
        if ( !empty($metadata) ) {
            $metadataHTML = "<script id='mux-{$metadataKey}' type='application/json'>" . json_encode( $metadata, JSON_PRETTY_PRINT ) . "</script>";
            $block_content .= "\n" . $metadataHTML;
        }
        
        return $block_content;
    }

}