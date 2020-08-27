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

use  MediaCloud\Plugin\Tools\Video\Driver\Mux\Models\MuxAsset ;
use  MediaCloud\Plugin\Utilities\View ;
use function  MediaCloud\Plugin\Utilities\arrayPath ;
class MuxShortcode
{
    /** @var MuxToolSettings|MuxToolProSettings|null  */
    private  $settings = null ;
    public function __construct()
    {
        $this->settings = MuxToolSettings::instance();
        
        if ( is_admin() ) {
            global  $pagenow, $typenow ;
            
            if ( in_array( $pagenow, [
                'post.php',
                'page.php',
                'post-new.php',
                'post-edit.php'
            ] ) && $typenow !== 'download' ) {
                add_action( 'media_buttons', [ $this, 'addMediaButtons' ], 11 );
                add_action( 'admin_footer', function () {
                    echo  View::render_view( 'admin.mux-video-shortcode-editor', [] ) ;
                } );
            }
        
        }
        
        add_action( 'init', function () {
            add_shortcode( "mux_video", [ $this, 'renderShortCode' ] );
        } );
    }
    
    public function addMediaButtons()
    {
        $img = '<span class="wp-media-buttons-icon mux-shortcode-icon" id="edd-media-button"></span>';
        $output = '<a name="Add Mux Video" href="#" class="button mux-shortcode-wizard" style="padding-left: .4em;">' . $img . 'Add Mux Video' . '</a>';
        echo  $output ;
    }
    
    public function renderShortCode( $attrs )
    {
        $muxId = $attrs['id'];
        if ( empty($muxId) ) {
            return '';
        }
        $asset = MuxAsset::assetForAttachment( $muxId );
        if ( $asset === null ) {
            return '';
        }
        $tagAttributeList = [];
        if ( arrayPath( $attrs, 'autoplay', 'false' ) !== 'false' ) {
            $tagAttributeList[] = 'autoplay';
        }
        if ( arrayPath( $attrs, 'loop', 'false' ) !== 'false' ) {
            $tagAttributeList[] = 'loop';
        }
        if ( arrayPath( $attrs, 'muted', 'false' ) !== 'false' ) {
            $tagAttributeList[] = 'muted';
        }
        if ( arrayPath( $attrs, 'controls', 'true' ) !== 'false' ) {
            $tagAttributeList[] = 'controls';
        }
        if ( arrayPath( $attrs, 'inline', 'false' ) !== 'false' ) {
            $tagAttributeList[] = 'playsInline';
        }
        $preload = arrayPath( $attrs, 'preload', null );
        if ( $preload !== null ) {
            $tagAttributeList[] = "preload='{$preload}'";
        }
        $tagAttributes = implode( ' ', $tagAttributeList );
        $classes = "mux-player";
        $extras = "";
        if ( !empty($this->settings->playerCSSClasses) ) {
            $classes .= " {$this->settings->playerCSSClasses}";
        }
        $extras .= " width={$asset->width} height={$asset->height}";
        $posterUrl = get_the_post_thumbnail_url( $asset->attachmentId, 'full' );
        if ( !empty($posterUrl) ) {
            $extras .= " poster='{$posterUrl}'";
        }
        $tag = "<figure><video class='{$classes}' {$extras} {$tagAttributes}>";
        $url = $asset->videoUrl();
        $source = "<source src='{$url}' type='application/x-mpegURL' />";
        $tag .= $source . '</video></figure>';
        return $tag;
    }

}