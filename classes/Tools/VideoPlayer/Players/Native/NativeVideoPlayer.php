<?php

namespace MediaCloud\Plugin\Tools\VideoPlayer\Players\Native;

use MediaCloud\Plugin\Tools\VideoPlayer\VideoPlayerInterface;
use function MediaCloud\Plugin\Utilities\arrayPath;
class NativeVideoPlayer implements VideoPlayerInterface {
    /** @var NativeVideoPlayerSettings|NativeVideoPlayerProSettings */
    protected $settings;

    protected $enqueued = false;

    public function __construct() {
        $this->settings = NativeVideoPlayerSettings::instance();
    }

    public function enqueueScripts( $admin ) {
        if ( $this->enqueued ) {
            return;
        }
        $this->enqueued = true;
        add_action( ( $admin ? 'admin_enqueue_scripts' : 'enqueue_scripts' ), function () {
            wp_enqueue_script(
                'mcloud-hls-player-js',
                ILAB_PUB_JS_URL . '/mcloud-hls-player.js',
                null,
                MEDIA_CLOUD_VERSION,
                true
            );
        }, PHP_INT_MAX - 10 );
    }

    public function renderPlayer(
        $attachmentId,
        $playerData,
        $videoAttributes = [
            'inline'   => true,
            'controls' => true,
        ],
        $downloads = [
            'allow'    => 'inherit',
            'original' => 'inherit',
        ],
        $posterUrl = null,
        $videoId = null,
        $videoClasses = [],
        $videoStyles = [],
        $wrapperEle = null,
        $wrapperClasses = [],
        $wrapperStyles = []
    ) {
        $this->enqueueScripts( is_admin() );
        $sources = [];
        if ( $playerData->playlistUrl ) {
            $sources[] = "<source type='application/x-mpegURL' src={$playerData->playlistUrl} />";
        }
        if ( $playerData->renditionUrl ) {
            $sources[] = "<source type='video/mp4' src={$playerData->renditionUrl} />";
        }
        if ( !empty( $playerData->subtitles ) ) {
            foreach ( $playerData->subtitles as $subtitle ) {
                $sources[] = "<source type='text/vtt' src='{$subtitle['url']}' />";
            }
        }
        $tagAttributeList = [];
        if ( !empty( arrayPath( $videoAttributes, 'autoplay', false ) ) ) {
            $tagAttributeList[] = 'autoplay';
        }
        if ( !empty( arrayPath( $videoAttributes, 'loop', false ) ) ) {
            $tagAttributeList[] = 'loop';
        }
        if ( !empty( arrayPath( $videoAttributes, 'muted', false ) ) ) {
            $tagAttributeList[] = 'muted';
        }
        if ( !empty( arrayPath( $videoAttributes, 'controls', false ) ) ) {
            $tagAttributeList[] = 'controls';
        }
        if ( !empty( arrayPath( $videoAttributes, 'inline', false ) ) ) {
            $tagAttributeList[] = 'playsInline';
        }
        if ( !empty( arrayPath( $videoAttributes, 'disabled', false ) ) ) {
            $tagAttributeList[] = 'disabled';
            //			$videoStyles[] = ['pointer-events' => 'none'];
        }
        if ( !empty( arrayPath( $videoAttributes, 'preload', null ) ) ) {
            $preload = arrayPath( $videoAttributes, 'preload', null );
            if ( $preload !== null && in_array( strtolower( $preload ), ['auto', 'metadata', 'none'] ) ) {
                $tagAttributeList[] = "preload='" . strtolower( $preload ) . "'";
            }
        }
        $tagAttributes = implode( ' ', $tagAttributeList );
        $sourcesText = implode( "\n", $sources );
        $posterAttr = ( $posterUrl ? "poster='{$posterUrl}'" : "" );
        $videoIdAttr = ( $videoId ? "id='{$videoId}'" : "" );
        $videoStyles = array_merge( [
            'width'        => '100%',
            'aspect-ratio' => "{$playerData->width}/{$playerData->height}",
        ], $videoStyles );
        $vstyles = array_map( function ( $k, $v ) {
            return "{$k}: {$v}";
        }, array_keys( $videoStyles ), array_values( $videoStyles ) );
        $videoStylesAttr = ( count( $vstyles ) > 0 ? "style='" . implode( "; ", $vstyles ) . "'" : "" );
        $videoClasses = array_merge( ['mcloud-video'], $videoClasses );
        $videoClassAttr = ( count( $videoClasses ) > 0 ? "class='" . implode( " ", $videoClasses ) . "'" : "" );
        $wrapperTag = '';
        $wrapperClosingTag = '';
        if ( $wrapperEle ) {
            $styles = array_map( function ( $k, $v ) {
                return "{$k}: {$v}";
            }, array_keys( $wrapperStyles ), array_values( $wrapperStyles ) );
            $stylesAttr = ( count( $styles ) > 0 ? "style='" . implode( "; ", $styles ) . "'" : "" );
            $classAttr = ( count( $wrapperClasses ) > 0 ? "class='" . implode( " ", $wrapperClasses ) . "'" : "" );
            $wrapperTag = "<{$wrapperEle} {$classAttr} {$stylesAttr}>";
            $wrapperClosingTag = "</{$wrapperEle}>";
        }
        return <<<PLAYER
\t\t{$wrapperTag}<video {$videoIdAttr} {$tagAttributes} {$posterAttr} {$videoClassAttr} {$videoStylesAttr}>
\t\t\t{$sourcesText}
\t\t</video>{$wrapperClosingTag}
PLAYER;
    }

    static function registerSettings( $optionsPage ) {
    }

}
