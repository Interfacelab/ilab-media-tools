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

use MediaCloud\Plugin\Tasks\TaskManager;
use MediaCloud\Plugin\Tools\Storage\StorageTool;
use MediaCloud\Plugin\Tools\Video\Driver\Mux\Data\MuxDatabase;
use MediaCloud\Plugin\Tools\Video\Driver\Mux\Models\MuxAsset;
use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Tools\Video\Driver\Mux\Tasks\RegenerateFilmstripTask;
use MediaCloud\Plugin\Tools\Video\Driver\Mux\Tasks\RelinkTask;
use MediaCloud\Plugin\Tools\Video\Driver\Mux\Tasks\TransferTask;
use MediaCloud\Plugin\Tools\Video\VideoEncodingInterface;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use MediaCloud\Plugin\Utilities\NoticeManager;
use MediaCloud\Plugin\Utilities\View;
use MediaCloud\Vendor\MuxPhp\Models\CreatePlaybackRestrictionRequest;
use function MediaCloud\Plugin\Utilities\anyEmpty;
use function MediaCloud\Plugin\Utilities\arrayPath;
use function MediaCloud\Plugin\Utilities\ilab_set_time_limit;
class MuxTool implements VideoEncodingInterface {
    /** @var null|MuxToolSettings|MuxToolProSettings */
    protected $settings = null;

    /** @var MuxHooks */
    protected $hooks = null;

    private $muxIconSVG = null;

    private $hlsIconSVG = null;

    public function __construct() {
        $this->settings = MuxToolSettings::instance();
    }

    //region Tool Overrides
    public function enabled() {
        if ( empty( $this->settings->tokenID ) || empty( $this->settings->tokenSecret ) ) {
            return false;
        }
        if ( empty( $this->settings->webhookSecret ) ) {
            return false;
        }
        return true;
    }

    public function validateSettings() {
        return !anyEmpty( $this->settings->tokenID, $this->settings->tokenSecret, $this->settings->webhookSecret );
    }

    function settingsChanged() {
    }

    public function setup() {
        MuxDatabase::init();
        $this->hooks = new MuxHooks();
        if ( is_admin() ) {
            $this->integrateWithMediaLibrary();
        }
        $this->integrateREST();
        if ( $this->settings->deleteFromMux ) {
            add_action( 'delete_attachment', [$this, 'deleteAttachment'], 999 );
        }
    }

    //endregion
    //region Properties
    /**
     * @return MuxHooks
     */
    public function hooks() {
        return $this->hooks;
    }

    //endregion
    //region Integration
    protected function integrateWithMediaLibrary() {
        add_filter(
            'wp_prepare_attachment_for_js',
            [$this, 'prepareAttachmentForJS'],
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
                        echo "<img src='{$muxIcon}' width='24'></a>";
                    } else {
                        echo "<img src='{$hlsIcon}' width='35'></a>";
                    }
                }
            },
            PHP_INT_MAX - 8,
            2
        );
        $this->hookMediaDetails();
    }

    private function muxIcon() {
        if ( $this->muxIconSVG === null ) {
            $svg = file_get_contents( ILAB_PUB_IMG_DIR . '/logo-mux-white.svg' );
            $this->muxIconSVG = 'data:image/svg+xml;base64,' . base64_encode( $svg );
        }
        return $this->muxIconSVG;
    }

    private function hlsIcon() {
        if ( $this->hlsIconSVG === null ) {
            $svg = file_get_contents( ILAB_PUB_IMG_DIR . '/logo-hls.svg' );
            $this->hlsIconSVG = 'data:image/svg+xml;base64,' . base64_encode( $svg );
        }
        return $this->hlsIconSVG;
    }

    private function hookMediaLibraryGrid() {
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

    private function hookMediaDetails() {
        add_filter(
            'media_row_actions',
            function ( $actions, $post ) {
                if ( strpos( $post->post_mime_type, 'video' ) === 0 ) {
                    $nonce = wp_create_nonce( 'media-cloud-mux-replace-poster' );
                    $newaction['mcloud_mux_replace_poster'] = '<a data-nonce="' . $nonce . '" data-attachment-id="' . $post->ID . '" class="replace-video-poster" href="#" title="Replace Poster">' . __( 'Replace Poster', 'ilab-media-tools' ) . '</a>';
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
                'label'    => __( 'Replace Poster', 'ilab-media-tools' ),
                'url'      => '#',
            ];
            return $buttons;
        }, 2 );
        add_filter( 'mediacloud/ui/media-detail-links', function ( $links ) {
            $links[] = [
                'type'     => 'video',
                'class'    => 'replace-video-poster',
                'thickbox' => false,
                'label'    => __( 'Replace Poster', 'ilab-media-tools' ),
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
    public function posterSelectURL( $id, $partial = false ) {
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
    public function prepareAttachmentForJS( $response, $attachment, $meta ) {
        if ( empty( $meta ) || !isset( $meta['mux'] ) ) {
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
        if ( !empty( $_REQUEST['post_id'] ) ) {
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

    public function deleteAttachment( $id ) {
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
    public function integrateREST() {
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

    //endregion
    //region VideoEncodingInterface
    public function importUrl( $attachmentId, $meta ) {
        $this->hooks->importUrl( $attachmentId, $meta );
    }

    function registerMetabox() {
        add_action( 'admin_init', function () {
            add_meta_box(
                'mcloud-mux-meta',
                'Mux Info',
                function ( $post ) {
                    /** @var \WP_Post $post */
                    $asset = MuxAsset::assetForAttachment( $post->ID );
                    echo View::render_view( 'admin.mux-properties', [
                        'asset' => $asset,
                    ] );
                },
                'attachment',
                'side',
                'low'
            );
        } );
    }

    function addSubtitles(
        $attachmentId,
        $language_code,
        $name,
        $cc,
        $url,
        $subtitleContents,
        $subtitlesFile
    ) {
        $asset = MuxAsset::assetForAttachment( $attachmentId );
        if ( empty( $asset ) ) {
            return [
                'error'   => true,
                'status'  => 400,
                'message' => 'Missing mux aset.',
            ];
        }
        if ( $asset->addCaptions( $language_code, $url, $cc ) ) {
            return [
                'error'   => false,
                'status'  => 200,
                'message' => null,
            ];
        }
        return [
            'error'   => true,
            'status'  => 500,
            'message' => 'Unknown error.',
        ];
    }

    /**
     * @param $attachmentId
     * @param $language_code
     *
     * @return array{ error: bool, status:int, message:string|null }
     * @throws \Exception
     */
    function deleteSubtitles( $attachmentId, $language_code ) {
        $asset = MuxAsset::assetForAttachment( $attachmentId );
        if ( empty( $asset ) ) {
            return [
                'error'   => true,
                'status'  => 400,
                'message' => 'Missing mux aset.',
            ];
        }
        $trackId = null;
        if ( $asset->subtitles ) {
            foreach ( $asset->subtitles as $subtitle ) {
                if ( $subtitle['language_code'] === $language_code ) {
                    $trackId = $subtitle['id'];
                    break;
                }
            }
        }
        if ( !$trackId ) {
            return [
                'error'   => true,
                'status'  => 400,
                'message' => 'Missing parameters.',
            ];
        }
        if ( $asset->deleteCaptions( $trackId ) ) {
            return [
                'error'   => false,
                'status'  => 200,
                'message' => null,
            ];
        }
        return [
            'error'   => true,
            'status'  => 500,
            'message' => 'Unknown error.',
        ];
    }

    //endregion
    //region Watermark
    function resetWatermark() {
        return true;
    }

    //endregion
}
