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

use  MediaCloud\Plugin\Tools\Video\Driver\Mux\Models\MuxAsset ;
use  MediaCloud\Plugin\Utilities\View ;
use function  MediaCloud\Plugin\Utilities\arrayPath ;
use function  MediaCloud\Plugin\Utilities\postIdExists ;
class VideoPlayerShortcode
{
    /** @var VideoPlayerToolSettings|VideoPlayerToolProSettings|null  */
    private  $settings = null ;
    public function __construct()
    {
        $this->settings = VideoPlayerToolSettings::instance();
        
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
                    echo  View::render_view( 'admin.video-shortcode-editor', [] ) ;
                } );
            }
        
        }
        
        add_action( 'init', function () {
            add_shortcode( "mux_video", [ $this, 'renderShortCode' ] );
            add_shortcode( "mcloud_video", [ $this, 'renderShortCode' ] );
        } );
    }
    
    public function addMediaButtons()
    {
        $img = '<span class="wp-media-buttons-icon mux-shortcode-icon" id="edd-media-button"></span>';
        $output = '<a name="Add Media Cloud Video" href="#" class="button mux-shortcode-wizard" style="padding-left: .4em;">' . $img . 'Add Media Cloud Video' . '</a>';
        echo  $output ;
    }
    
    public function renderShortCode( $attrs )
    {
        $attachmentId = $attrs['id'];
        if ( empty($attachmentId) || !postIdExists( $attachmentId ) ) {
            return '';
        }
        $meta = wp_get_attachment_metadata( $attachmentId );
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
        $asset = MuxAsset::assetForAttachment( $attachmentId );
        if ( !empty($this->settings->playerCSSClasses) ) {
            $classes .= " {$this->settings->playerCSSClasses}";
        }
        
        if ( empty($asset) ) {
            $width = intval( arrayPath( $meta, 'width', (int) 0 ) );
            $height = intval( arrayPath( $meta, 'height', (int) 0 ) );
            if ( !empty($width) && !empty($height) ) {
                $extras .= " width={$asset->width} height={$asset->height}";
            }
        } else {
            $extras .= " width={$asset->width} height={$asset->height}";
        }
        
        $posterUrl = get_the_post_thumbnail_url( $attachmentId, 'full' );
        if ( !empty($posterUrl) ) {
            $extras .= " poster='{$posterUrl}'";
        }
        $tag = "<figure><video class='{$classes}' {$extras} {$tagAttributes}>";
        
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
        
        $tag .= $source . '</video></figure>';
        return $tag;
    }

}