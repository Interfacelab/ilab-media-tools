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
namespace MediaCloud\Plugin\Tools\VideoPlayer;

use Elementor\Elements_Manager;
use Elementor\Plugin;
use MediaCloud\Plugin\Tools\Tool;
use MediaCloud\Plugin\Tools\VideoPlayer\Players\Native\NativeVideoPlayer;
use MediaCloud\Plugin\Tools\VideoPlayer\Widgets\Bricks\MediaCloudVideoElement;
use MediaCloud\Plugin\Tools\VideoPlayer\Widgets\Elementor\MediaCloudVideoWidget;
use function MediaCloud\Plugin\Utilities\arrayPath;
use function MediaCloud\Plugin\Utilities\postIdExists;
class VideoPlayerTool extends Tool {
    /** @var null|VideoPlayerToolSettings */
    protected $settings = null;

    /** @var null|VideoPlayerShortcode */
    protected $shortCode = null;

    protected static $enqueued = false;

    /** @var VideoPlayerInterface|null  */
    protected $player = null;

    /** @var null|VideoPlayerInterface  */
    protected $nativePlayer = null;

    /** @var StoryboardTool */
    private $storyboardTool;

    public function __construct( $toolName, $toolInfo, $toolManager ) {
        $this->settings = VideoPlayerToolSettings::instance();
        if ( !empty( $toolInfo['videoPlayers'] ) ) {
            foreach ( $toolInfo['videoPlayers'] as $key => $data ) {
                if ( empty( $data['name'] ) || empty( $data['class'] ) || empty( $data['config'] ) ) {
                    throw new \Exception("Video Player Tool configuration file is malformed.  Players are missing required information.");
                }
                $configFile = ILAB_CONFIG_DIR . $data['config'];
                if ( !file_exists( $configFile ) ) {
                    throw new \Exception("Missing driver config file '{$configFile}'. ");
                }
                $config = (include $configFile);
                VideoPlayerToolSettings::registerPlayer(
                    $key,
                    $data['name'],
                    $data['class'],
                    $config,
                    arrayPath( $data, 'help', null )
                );
            }
        }
        do_action( 'media-cloud/video-player/register-players' );
        $playerConfigs = [];
        foreach ( VideoPlayerToolSettings::players() as $key => $player ) {
            $playerConfigs[$key] = $player['config'];
        }
        $toolInfo = $this->mergeSettings( $toolInfo, $playerConfigs );
        parent::__construct( $toolName, $toolInfo, $toolManager );
        $this->player = VideoPlayerToolSettings::playerInstance();
        if ( is_admin() ) {
            foreach ( $toolInfo['videoPlayers'] as $key => $data ) {
                $class = $data['class'];
                if ( class_exists( $class ) ) {
                    call_user_func( [$class, 'registerSettings'], $this->options_page );
                }
            }
        }
    }

    //region Tool Overrides
    public function hasSettings() {
        return true;
    }

    public function setup() {
        if ( $this->enabled() ) {
            $this->shortCode = new VideoPlayerShortcode($this->player);
            add_filter(
                'render_block',
                [$this, 'filterBlocks'],
                PHP_INT_MAX - 1,
                2
            );
            if ( is_admin() || !empty( $this->settings->alwaysIncludeJS ) || class_exists( 'Elementor\\Plugin' ) ) {
                $this->player->enqueueScripts( is_admin() );
            } else {
                if ( isset( $_GET['elementor-preview'] ) ) {
                    $this->player->enqueueScripts( false );
                }
            }
            if ( is_admin() ) {
                $this->integrateWithAdmin();
            }
            $this->initBlocks();
            $this->initShortcodeOverride();
        }
    }

    //endregion
    //region UI
    private function integrateWithAdmin() {
        add_action( 'admin_enqueue_scripts', function () {
            wp_enqueue_script(
                'mcloud-video-player-admin-js',
                ILAB_PUB_JS_URL . '/mcloud-video-player-admin.js',
                null,
                MEDIA_CLOUD_VERSION,
                true
            );
            wp_enqueue_style(
                'mcloud-video-player-admin-css',
                ILAB_PUB_CSS_URL . '/mcloud-video-player-admin.css',
                null,
                MEDIA_CLOUD_VERSION
            );
        } );
    }

    //endregion
    //region Shortcode Override
    private function initShortcodeOverride() {
        if ( !is_admin() && $this->settings->playerOverrideShortcode ) {
            add_filter(
                'wp_video_shortcode',
                function (
                    $output,
                    $atts,
                    $video,
                    $post_id,
                    $library
                ) {
                    $attachmentId = arrayPath( $atts, 'id', null );
                    if ( empty( $attachmentId ) ) {
                        $mp4 = arrayPath( $atts, 'mp4', null );
                        if ( empty( $mp4 ) ) {
                            return $output;
                        }
                        global $wpdb;
                        $query = $wpdb->prepare( "select id from {$wpdb->prefix}posts where guid = %s", $mp4 );
                        $attachmentId = $wpdb->get_var( $query );
                        if ( empty( $attachmentId ) ) {
                            $parsedPath = ltrim( parse_url( $mp4, PHP_URL_PATH ), '/' );
                            $newUrl = str_replace( $parsedPath, str_replace( '/', '%2F', $parsedPath ), $mp4 );
                            $query = $wpdb->prepare( "select id from {$wpdb->prefix}posts where guid = %s", $newUrl );
                            $attachmentId = $wpdb->get_var( $query );
                        }
                        if ( empty( $attachmentId ) ) {
                            return $output;
                        }
                    }
                    $meta = wp_get_attachment_metadata( $attachmentId );
                    $playerOverride = apply_filters(
                        'media-cloud/video-player/override',
                        null,
                        $attachmentId,
                        $meta,
                        [
                            'container-style' => [
                                'max-height'       => '82vh',
                                'background-color' => '#555',
                            ],
                        ]
                    );
                    if ( !empty( $playerOverride ) ) {
                        return $playerOverride;
                    }
                    /** @var VideoPlayerData|null $playerData */
                    $playerData = apply_filters(
                        'media-cloud/video-player/player-data',
                        null,
                        null,
                        $attachmentId,
                        $meta
                    );
                    if ( empty( $playerData ) ) {
                        return $output;
                    }
                    $posterUrl = get_the_post_thumbnail_url( $attachmentId, 'full' );
                    return $this->player->renderPlayer(
                        $attachmentId,
                        $playerData,
                        [
                            'inline'   => true,
                            'controls' => true,
                            'autoplay' => false,
                            'muted'    => false,
                            'preload'  => 'metadata',
                        ],
                        [],
                        $posterUrl,
                        "mcloud-player-{$attachmentId}",
                        [],
                        [],
                        'div'
                    );
                },
                PHP_INT_MAX,
                5
            );
        }
    }

    //endregion
    //region Blocks
    protected function initBlocks() {
        add_action( 'init', function () {
            register_block_type( ILAB_BLOCKS_DIR . '/mediacloud-video-block' );
            register_block_type( ILAB_BLOCKS_DIR . '/media-cloud-player' );
        } );
        add_filter(
            'block_categories_all',
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
        add_action( 'init', function () {
            if ( class_exists( '\\Bricks\\Elements' ) ) {
                \Bricks\Elements::register_element( ILAB_CLASSES_DIR . '/Tools/VideoPlayer/Widgets/Bricks/MediaCloudVideoElement.php' );
                add_filter(
                    'bricks/frontend/render_data',
                    function ( $content, $post, $area ) {
                        if ( $area !== 'content' ) {
                            return $content;
                        }
                        return MediaCloudVideoElement::filterContent( $content, $post, $area );
                    },
                    PHP_INT_MAX,
                    3
                );
            }
        }, 11 );
        if ( class_exists( 'Elementor\\Plugin' ) ) {
            add_action( 'elementor/widgets/widgets_registered', function () {
                Plugin::instance()->widgets_manager->register( new MediaCloudVideoWidget() );
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
            //			if (VideoEncodingToolSettings::driver() === 'mux') {
            add_filter(
                'the_content',
                function ( $content ) {
                    return MediaCloudVideoWidget::filterContent( $content );
                },
                PHP_INT_MAX,
                1
            );
            //			}
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
    function filterBlocks( $block_content, $block ) {
        if ( isset( $block['blockName'] ) ) {
            if ( $block['blockName'] === 'media-cloud/mux-video-block' ) {
                return $this->filterVideoBlock( $block_content, $block );
            } else {
                if ( $block['blockName'] === 'media-cloud/media-cloud-player' ) {
                    return $this->filterVideoBlock( $block_content, $block );
                }
            }
        }
        return $block_content;
    }

    protected function filterVideoBlock( $block_content, $block ) {
        $attachmentId = arrayPath( $block, 'attrs/id', null );
        if ( empty( $attachmentId ) || !postIdExists( $attachmentId ) ) {
            return '';
        }
        $classRegex = '/\\s+class\\s*\\=\\s*["|\']([^"|\']*)["|\']/m';
        preg_match_all(
            $classRegex,
            arrayPath( $block, 'innerHTML', '' ),
            $matches,
            PREG_SET_ORDER,
            0
        );
        $classList = [];
        $posterUrl = arrayPath( $block, 'attrs/poster', null );
        if ( count( $matches ) > 0 ) {
            $classList = explode( " ", $matches[0][1] );
        }
        $videoAttributes = [
            'autoplay' => arrayPath( $block, 'attrs/autoplay', false ),
            'controls' => arrayPath( $block, 'attrs/controls', true ),
            'loop'     => arrayPath( $block, 'attrs/loop', false ),
            'muted'    => arrayPath( $block, 'attrs/muted', false ),
            'inline'   => arrayPath( $block, 'attrs/playsInline', true ),
            'preload'  => arrayPath( $block, 'attrs/preload', 'metadata' ),
        ];
        $downloads = [
            'allow'    => arrayPath( $block, 'attrs/allowDownload', 'inherit' ),
            'original' => arrayPath( $block, 'attrs/allowDownloadOriginal', 'inherit' ),
        ];
        $player = $this->renderPlayer(
            $attachmentId,
            $videoAttributes,
            $downloads,
            $posterUrl
        );
        $classAttr = ( count( $classList ) > 0 ? "class='" . implode( " ", $classList ) . "'" : "" );
        return "<div {$classAttr} style='line-height:0'>{$player}</div>";
    }

    public function player() {
        return $this->player;
    }

    /**
     * Renders video player using supplied data
     *
     * @param int $attachmentId
     * @param array $videoAttributes
     * @param array{ allow: string, original: string, } $downloads
     * @param string|null $posterUrl
     * @param string[] $videoClasses
     * @param string|null $wrapper
     * @param string[] $wrapperClasses
     * @param string|null $videoId
     *
     * @return string|null
     */
    public function renderPlayer(
        $attachmentId,
        $videoAttributes = [
            'inline'   => true,
            'controls' => true,
        ],
        $downloads = [
            'allow'    => 'inherit',
            'original' => 'inherit',
        ],
        $posterUrl = null,
        $videoClasses = [],
        $wrapper = null,
        $wrapperClasses = [],
        $videoId = null
    ) {
        if ( empty( $attachmentId ) || !postIdExists( $attachmentId ) ) {
            return null;
        }
        $meta = wp_get_attachment_metadata( $attachmentId );
        $playerOverride = apply_filters(
            'media-cloud/video-player/override',
            null,
            $attachmentId,
            $meta,
            []
        );
        if ( !empty( $playerOverride ) ) {
            return $playerOverride;
        }
        /** @var VideoPlayerData|null $playerData */
        $playerData = apply_filters(
            'media-cloud/video-player/player-data',
            null,
            [],
            $attachmentId,
            $meta
        );
        if ( empty( $playerData ) ) {
            return null;
        }
        if ( empty( $posterUrl ) ) {
            $posterUrl = get_the_post_thumbnail_url( $attachmentId, 'full' );
        }
        return $this->player->renderPlayer(
            $attachmentId,
            $playerData,
            $videoAttributes,
            $downloads,
            $posterUrl,
            $videoId,
            $videoClasses,
            [],
            $wrapper,
            $wrapperClasses
        );
    }

    //endregion
    //region Provider Options/Help
    public function providerOptions() {
        $providers = [];
        foreach ( VideoPlayerToolSettings::players() as $id => $player ) {
            $providers[$id] = $player['name'];
        }
        return $providers;
    }

    public function providerHelp() {
        $help = [];
        foreach ( VideoPlayerToolSettings::players() as $id => $player ) {
            $helpData = arrayPath( $player, 'help', null );
            if ( !empty( $helpData ) ) {
                $help[$id] = $helpData;
            }
        }
        return $help;
    }

    //region
}
