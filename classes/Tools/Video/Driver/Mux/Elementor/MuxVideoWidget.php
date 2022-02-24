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
namespace MediaCloud\Plugin\Tools\Video\Driver\Mux\Elementor;

use  Elementor\Controls_Manager ;
use  Elementor\Widget_Base ;
use  MediaCloud\Plugin\Tools\Video\Driver\Mux\Models\MuxAsset ;
use  MediaCloud\Plugin\Tools\Video\Driver\Mux\MuxToolProSettings ;
use  MediaCloud\Plugin\Tools\Video\Driver\Mux\MuxToolSettings ;
use function  MediaCloud\Plugin\Utilities\arrayPath ;
use function  MediaCloud\Plugin\Utilities\gen_uuid ;
class MuxVideoWidget extends Widget_Base
{
    public function get_name()
    {
        return "mux-video";
    }
    
    public function get_title()
    {
        return "Mux Video";
    }
    
    public function get_icon()
    {
        return 'far fa-file-video';
    }
    
    public function get_categories()
    {
        return [ 'media-cloud' ];
    }
    
    protected function _register_controls()
    {
        $this->start_controls_section( 'content_section', [
            'label' => 'Content',
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );
        $this->add_control( 'video', [
            'label'      => 'Video',
            'media_type' => 'video',
            'type'       => Controls_Manager::MEDIA,
        ] );
        $this->add_control( 'poster', [
            'label'      => 'Poster Image',
            'media_type' => 'image',
            'separator'  => 'before',
            'type'       => Controls_Manager::MEDIA,
        ] );
        $this->add_control( 'autoplay', [
            'label'     => 'Auto Play',
            'type'      => Controls_Manager::SWITCHER,
            'separator' => 'before',
        ] );
        $this->add_control( 'loop', [
            'label' => 'Loop',
            'type'  => Controls_Manager::SWITCHER,
        ] );
        $this->add_control( 'muted', [
            'label' => 'Muted',
            'type'  => Controls_Manager::SWITCHER,
        ] );
        $this->add_control( 'playsinline', [
            'label'   => 'Play Inline',
            'type'    => Controls_Manager::SWITCHER,
            'default' => 'yes',
        ] );
        $this->add_control( 'controls', [
            'label'   => 'Show Controls',
            'type'    => Controls_Manager::SWITCHER,
            'default' => 'yes',
        ] );
        $this->add_control( 'preload', [
            'label'   => 'Preload',
            'type'    => Controls_Manager::SELECT,
            'options' => [
            'auto'     => 'Auto',
            'metadata' => 'Metadata',
            'none'     => 'None',
        ],
            'default' => 'metadata',
        ] );
        $this->end_controls_section();
    }
    
    private function renderEmpty( $message, $hasError = false )
    {
        $classes = ( $hasError ? 'has-error' : '' );
        echo  <<<RENDER
<div class="mcloud-elem-empty-video {$classes}"><div>{$message}</div></div>
RENDER
 ;
    }
    
    protected function render()
    {
        $settings = $this->get_settings_for_display();
        
        if ( empty($settings['video']['id']) ) {
            $this->renderEmpty( "Please select a video." );
            return;
        }
        
        $asset = MuxAsset::assetForAttachment( $settings['video']['id'] );
        
        if ( empty($asset) ) {
            $this->renderEmpty( "Please select a Mux video.", true );
            return;
        }
        
        $classes = 'mux-player elementor-mux-player';
        $extras = "data-mux-id='{$asset->muxId}'";
        $metadata = [];
        $metadataKey = sanitize_title( gen_uuid( 12 ) );
        $muxSettings = MuxToolSettings::instance();
        $metadataHTML = '';
        $url = $asset->videoUrl();
        
        if ( empty($settings['poster']['id']) ) {
            $posterUrl = get_the_post_thumbnail_url( $asset->id(), 'large' );
        } else {
            $posterUrl = wp_get_attachment_image_url( $settings['poster']['id'], 'large' );
        }
        
        if ( !empty($posterUrl) ) {
            $extras .= "poster = '{$posterUrl}'";
        }
        if ( arrayPath( $settings, 'autoplay', 'yes' ) === 'yes' ) {
            $extras .= ' autoplay';
        }
        if ( arrayPath( $settings, 'loop', 'yes' ) === 'yes' ) {
            $extras .= ' loop';
        }
        if ( arrayPath( $settings, 'muted', 'yes' ) === 'yes' ) {
            $extras .= ' muted';
        }
        if ( arrayPath( $settings, 'controls', 'yes' ) === 'yes' ) {
            $extras .= ' controls';
        }
        if ( arrayPath( $settings, 'playsinline', 'yes' ) === 'yes' ) {
            $extras .= ' playsinline';
        }
        $preload = arrayPath( $settings, 'preload', 'metadata' );
        $extras .= " preload='{$preload}'";
        $aspect = generateAspectRatio( $asset->width, $asset->height );
        $aspectClass = 'mux-ele-video-container aspect-' . implode( '-', $aspect );
        if ( !empty($muxSettings->playerCSSClasses) ) {
            $classes .= " {$muxSettings->playerCSSClasses}";
        }
        $sources = "<source src=\"{$url}\" type=\"application/x-mpegURL\" />";
        echo  <<<RENDER
\t\t<figure class="{$aspectClass}">
\t\t\t<video class="{$classes}" {$extras}>
\t\t\t\t{$sources}
\t\t\t</video>
\t\t\t{$metadataHTML}
\t\t</figure>
RENDER
 ;
    }
    
    public static function filterContent( $content )
    {
        $vidregex = '/<\\s*figure\\s+class=\\"\\s*mux-ele-video-container(?:[^>]+)>\\s*(<video[^>]+>)\\s*(<source[^>]+>)/ms';
        if ( preg_match_all(
            $vidregex,
            $content,
            $matches,
            PREG_SET_ORDER,
            0
        ) ) {
            foreach ( $matches as $match ) {
                $video = $match[1];
                $source = $match[2];
                
                if ( preg_match_all( '/data-mux-id\\s*=\\s*(?:\'|")([^\'"]+)/ms', $video, $idMatches ) ) {
                    $assetId = $idMatches[1][0];
                    $asset = MuxAsset::asset( $assetId );
                    
                    if ( !empty($asset) ) {
                        $newUrl = $asset->videoUrl();
                        $content = str_replace( $source, "<source src=\"{$newUrl}\" type=\"application/x-mpegURL\" />", $content );
                    }
                
                }
            
            }
        }
        return $content;
    }

}