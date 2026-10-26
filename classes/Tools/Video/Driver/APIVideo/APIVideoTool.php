<?php

namespace MediaCloud\Plugin\Tools\Video\Driver\APIVideo;

use MediaCloud\Plugin\Tools\Storage\StorageTool;
use MediaCloud\Plugin\Tools\Storage\StorageToolSettings;
use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Tools\Video\Driver\APIVideo\Data\APIVideoDatabase;
use MediaCloud\Plugin\Tools\Video\Driver\APIVideo\Models\APIVideo;
use MediaCloud\Plugin\Tools\Video\VideoEncoderConsts;
use MediaCloud\Plugin\Tools\Video\VideoEncodingInterface;
use MediaCloud\Plugin\Tools\VideoPlayer\VideoPlayerData;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use MediaCloud\Plugin\Utilities\Prefixer;
use MediaCloud\Plugin\Utilities\View;
use MediaCloud\Vendor\GuzzleHttp\Client;
use MediaCloud\Vendor\GuzzleHttp\RequestOptions;
use function MediaCloud\Plugin\Utilities\arrayPath;
use function MediaCloud\Plugin\Utilities\mergeCSSStyles;
use function MediaCloud\Plugin\Utilities\postIdExists;
class APIVideoTool implements VideoEncodingInterface {
    //region Props
    /* @var APIVideoSettings|APIVideoProSettings */
    protected $settings = null;

    protected $apiVideoIconSVG = null;

    //endregion
    //region Constructor/Setup
    public function __construct() {
        $this->settings = APIVideoSettings::instance();
    }

    function validateSettings() {
        return !empty( $this->settings->apiKey );
    }

    function settingsChanged() {
    }

    function enabled() {
        return $this->validateSettings();
    }

    function setup() {
        APIVideoDatabase::init();
        $currentWebhookUrl = get_option( 'mcloud-apivideo-webhook-url' );
        if ( $currentWebhookUrl !== $this->settings->webhookUrl ) {
            $client = new APIVideoClient();
            if ( $client->registerWebhook( $this->settings->webhookUrl ) ) {
                update_option( 'mcloud-apivideo-webhook-url', $this->settings->webhookUrl );
            }
        }
        if ( $this->settings->processUploads ) {
            if ( ToolsManager::instance()->toolEnabled( 'storage' ) ) {
                add_action(
                    'media-cloud/storage/uploaded-attachment',
                    [$this, 'handleUpload'],
                    1000,
                    3
                );
                add_action(
                    'media-cloud/storage/direct-uploaded-attachment',
                    [$this, 'handleDirectUpload'],
                    1000,
                    2
                );
            } else {
                add_filter(
                    'wp_update_attachment_metadata',
                    [$this, 'handleUpdateAttachmentMetadata'],
                    1000,
                    2
                );
            }
        }
        if ( is_admin() ) {
            add_action( 'wp_ajax_mcloud_replace_poster', [$this, 'ajaxReplacePoster'] );
        }
        if ( $this->settings->deleteFromStream ) {
            add_action( 'delete_attachment', [$this, 'deleteAttachment'], 999 );
        }
        add_filter(
            'wp_get_attachment_url',
            [$this, 'getAttachmentURL'],
            2000,
            2
        );
        add_filter( 'template_include', [$this, 'handleWebhook'] );
        add_filter(
            'media-cloud/video-player/player-data',
            [$this, 'playerData'],
            2000,
            4
        );
        if ( is_admin() ) {
            $this->integrateWithMediaLibrary();
        }
        $this->integrateREST();
    }

    //endregion
    //region Upload Handling
    function importUrl( $attachmentId, $meta ) {
        $this->doImportUrl( $attachmentId, $meta );
    }

    public function getAttachmentURL( $url, $post_id ) {
        $meta = wp_get_attachment_metadata( $post_id, true );
        if ( !isset( $meta['api-video'] ) ) {
            return $url;
        }
        $mp4 = arrayPath( $meta, 'api-video/mp4', null );
        if ( $mp4 ) {
            return $mp4;
        }
        return $url;
    }

    public function deleteAttachment( $id ) {
        $video = APIVideo::videoForAttachment( $id );
        if ( $video === null ) {
            return $id;
        }
        if ( !$this->settings->deleteFromStream ) {
            return $id;
        }
        $client = new APIVideoClient();
        $client->deleteVideo( $video->video_id );
        $video->delete();
        return $id;
    }

    public function doImportUrl( $attachmentId, $meta, $doUpdate = true ) {
        $client = new APIVideoClient();
        if ( ToolsManager::instance()->toolEnabled( 'storage' ) ) {
            /** @var StorageTool $storageTool */
            $storageTool = ToolsManager::instance()->tools['storage'];
            if ( isset( $meta['s3'] ) ) {
                $url = $storageTool->client()->presignedUrl( $meta['s3']['key'], 30 );
            } else {
                $otherMeta = wp_get_attachment_metadata( $attachmentId, true );
                if ( isset( $otherMeta['s3'] ) ) {
                    $url = $storageTool->client()->presignedUrl( $otherMeta['s3']['key'], 30 );
                } else {
                    $url = wp_get_attachment_url( $attachmentId );
                }
            }
        } else {
            $url = wp_get_attachment_url( $attachmentId );
            if ( defined( 'MEDIACLOUD_DEV_MODE' ) && defined( 'MEDIACLOUD_VIDEO_SERVER' ) && !empty( constant( 'MEDIACLOUD_VIDEO_SERVER' ) ) ) {
                // DEBUG ONLY
                $url = str_replace( home_url(), constant( 'MEDIACLOUD_VIDEO_SERVER' ), $url );
            }
        }
        $client->importVideo( $url, $attachmentId );
        return $meta;
    }

    public function handleDirectUpload( $attachmentId, $meta ) {
        $type = arrayPath( $meta, 'type', null );
        if ( empty( $type ) ) {
            $type = arrayPath( $meta, 'mime_type', null );
            if ( empty( $type ) ) {
                $type = arrayPath( $meta, 's3/mime-type', null );
                if ( empty( $type ) ) {
                    return;
                }
            }
        }
        if ( strpos( $type, 'video' ) !== 0 || !isset( $meta['s3'] ) ) {
            return;
        }
        $this->doImportUrl( $attachmentId, $meta );
    }

    public function handleUpload( $attachmentId, $file, $meta ) {
        $this->handleDirectUpload( $attachmentId, $meta );
    }

    public function handleUpdateAttachmentMetadata( $data, $id ) {
        if ( isset( $data['mime_type'] ) && strpos( $data['mime_type'], 'video/' ) === 0 ) {
            return $this->doImportUrl( $id, $data, false );
        }
        return $data;
    }

    //endregion
    //region Webhook
    /**
     * @param APIVideo $video
     */
    protected function handleFinished( $video ) {
        if ( empty( $video->attachment_id ) ) {
            return;
        }
        $client = new APIVideoClient();
        $encodingInfo = $client->videoEncodingInfo( $video->video_id );
        $qualities = arrayPath( $encodingInfo, 'encoding/qualities', [] );
        if ( count( $qualities ) === 0 ) {
            return;
        }
        $finishedCount = 0;
        foreach ( $qualities as $quality ) {
            if ( $quality['status'] === 'encoded' ) {
                $finishedCount++;
            }
        }
        if ( $finishedCount < count( $qualities ) ) {
            return;
        }
        $meta = wp_get_attachment_metadata( $video->attachment_id, true );
        $meta['api-video'] = [
            'videoId' => $video->video_id,
            'status'  => APIVideo::STATUS_READY,
            'private' => false,
        ];
        $generatedThumbnail = null;
        $info = $client->videoInfo( $video->video_id );
        if ( $info ) {
            $public = arrayPath( $info, 'public', true );
            $generatedThumbnail = arrayPath( $info, 'assets/thumbnail', null );
            $hls = arrayPath( $info, 'assets/hls', null );
            $mp4 = arrayPath( $info, 'assets/mp4', null );
            $player = arrayPath( $info, 'assets/player', null );
            $meta['api-video']['private'] = !$public;
            $meta['api-video']['hls'] = $hls;
            $meta['api-video']['mp4'] = $mp4;
            $meta['api-video']['player'] = $player;
        }
        if ( $generatedThumbnail ) {
            $video->refreshData();
            if ( !empty( $video->thumb_id ) ) {
                Logger::warning( 'Video already has thumbnail assigned.' );
                return;
            }
            $video->thumb_id = 1;
            $video->save();
        }
        Logger::info( "Updating meta for {$video->attachment_id}: " . json_encode( $meta, JSON_PRETTY_PRINT ) );
        wp_update_attachment_metadata( $video->attachment_id, $meta );
        if ( has_post_thumbnail( $video->attachment_id ) ) {
            Logger::warning( 'Video already has thumbnail assigned.' );
            return;
        }
        Logger::info( "Creating thumbnail for video." );
        $video->thumb_id = 1;
        $video->save();
        $uploadDirInfo = wp_get_upload_dir();
        if ( ToolsManager::instance()->toolEnabled( 'storage' ) ) {
            Prefixer::setType( 'image/jpeg' );
            $prefix = StorageToolSettings::prefix();
            $uploadDir = trailingslashit( $uploadDirInfo['basedir'] ) . $prefix;
            @mkdir( $uploadDir, 0777, true );
        } else {
            $uploadDir = trailingslashit( $uploadDirInfo['basedir'] );
        }
        $filePath = trailingslashit( $uploadDir ) . $video->attachment_id . '-thumb.jpg';
        $httpClient = new Client([
            RequestOptions::HEADERS => [
                'Referer' => get_home_url(),
            ],
        ]);
        try {
            $res = $httpClient->get( $generatedThumbnail );
            $body = $res->getBody();
            file_put_contents( $filePath, $body );
        } catch ( \Exception $ex ) {
            Logger::error( "Error download thumbnail: " . $ex->getMessage() );
        }
        if ( !file_exists( $filePath ) ) {
            Logger::error(
                "Could not download image {$generatedThumbnail}.",
                [],
                __METHOD__,
                __LINE__
            );
            return;
        }
        $thumbId = wp_insert_attachment( [
            'post_mime_type' => 'image/jpeg',
            'post_title'     => 'Poster',
            'post_content'   => '',
            'post_status'    => 'inherit',
        ] );
        Logger::info(
            "Created thumbnail attachment {$thumbId}.",
            [],
            __METHOD__,
            __LINE__
        );
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $thumbAttachmentMeta = wp_generate_attachment_metadata( $thumbId, $filePath );
        update_post_meta( $thumbId, '_wp_attached_file', $thumbAttachmentMeta['file'] );
        //		update_post_meta($video->attachment_id, '_thumbnail_id', $thumbId);
        set_post_thumbnail( $video->attachment_id, $thumbId );
        wp_update_attachment_metadata( $thumbId, $thumbAttachmentMeta );
    }

    public function handleWebhook( $template ) {
        if ( strpos( $_SERVER['REQUEST_URI'], '/__api-video/webhook' ) === 0 ) {
            $body = file_get_contents( 'php://input' );
            $json = json_decode( $body, true );
            $videoId = arrayPath( $json, 'videoId', null );
            if ( empty( $videoId ) ) {
                wp_send_json( [
                    'status' => 'missing video guid',
                ], 400 );
            }
            $video = APIVideo::findOrCreate( $videoId );
            if ( empty( $video ) ) {
                wp_send_json( [
                    'status' => 'missing video',
                ], 400 );
            }
            $this->handleFinished( $video );
            wp_send_json( [
                'status' => 'ok',
            ], 200 );
        }
        return $template;
    }

    //endregion
    //region Player
    public function playerData(
        $existingData,
        $block,
        $attachmentId,
        $meta
    ) {
        if ( !isset( $meta['api-video'] ) ) {
            return $existingData;
        }
        $videoId = arrayPath( $meta, 'api-video/videoId', null );
        if ( empty( $videoId ) ) {
            return $existingData;
        }
        $video = APIVideo::video( $videoId );
        if ( empty( $video ) ) {
            return $existingData;
        }
        $playlistUrl = arrayPath( $meta, 'api-video/hls', null );
        $originalUrl = arrayPath( $meta, 'api-video/mp4', null );
        if ( empty( $originalUrl ) ) {
            $originalUrl = wp_get_attachment_url( $attachmentId );
        }
        $w = arrayPath( $meta, 'width', 0 );
        $h = arrayPath( $meta, 'height', 0 );
        return new VideoPlayerData(
            $videoId,
            $w,
            $h,
            arrayPath( $meta, 'length', 0 ),
            $originalUrl,
            $originalUrl,
            $playlistUrl ?? $originalUrl,
            ( $playlistUrl ? 'application/vnd.apple.mpegurl' : 'video/mp4' ),
            null,
            $video->subtitlesData
        );
    }

    public function overridePlayerWithEmbed(
        $currentPlayerCode,
        $attachmentId,
        $meta,
        $options
    ) {
        return $currentPlayerCode;
    }

    //endregion
    //region Admin UI
    function driverIcon() {
        if ( $this->apiVideoIconSVG === null ) {
            $svg = file_get_contents( ILAB_PUB_IMG_DIR . '/icon-api-video.svg' );
            $this->apiVideoIconSVG = 'data:image/svg+xml;base64,' . base64_encode( $svg );
        }
        return $this->apiVideoIconSVG;
    }

    function integrateWithMediaLibrary() {
        add_filter(
            'wp_prepare_attachment_for_js',
            [$this, 'prepareAttachmentForJS'],
            1000,
            3
        );
        if ( ToolsManager::instance()->toolEnabled( 'storage' ) ) {
            add_filter( 'media-cloud/media-library/attachment-classes', function ( $additionalClasses ) {
                return '<# if (data.hasOwnProperty("hls")) {#>has-apivideo<#}#>' . $additionalClasses;
            } );
            add_filter( 'media-cloud/media-library/attachment-icons', function ( $additionalIcons ) {
                $hlsIcon = '<i class="apivideo-icon"></i>';
                return $hlsIcon . $additionalIcons;
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
                $video = APIVideo::videoForAttachment( $id );
                if ( !$video ) {
                    return;
                }
                if ( $column_name == "cloud" ) {
                    $icon = $this->driverIcon();
                    echo "<img src='{$icon}' width='24'></a>";
                }
            },
            PHP_INT_MAX - 8,
            2
        );
        $this->hookMediaDetails();
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
						var replace = '<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }} <# if (data.hasOwnProperty("hls")) {#>has-apivideo<#}#>"><i class="apivideo-icon"></i>';
						txt = txt.replace(search, replace);
						attachTemplate.text(txt);
					}

					var attachTemplate = jQuery('#tmpl-attachment-grid-view');
					if (attachTemplate) {
						var txt = attachTemplate.text();

						var search = '<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }}">';
						var replace = '<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }} <# if (data.hasOwnProperty("hls")) {#>has-apivideo<#}#>"><i class="apivideo-icon"></i>';
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
                    $newaction['mcloud_mux_replace_poster'] = '<a data-nonce="' . $nonce . '" data-attachment-id="' . $post->ID . '" class="replace-video-poster" href="#" title="Replace Poster">' . __( 'Replace Poster', 'media-cloud' ) . '</a>';
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
                'label'    => __( 'Replace Poster', 'media-cloud' ),
                'url'      => '#',
            ];
            return $buttons;
        }, 2 );
        add_filter( 'mediacloud/ui/media-detail-links', function ( $links ) {
            $links[] = [
                'type'     => 'video',
                'class'    => 'replace-video-poster',
                'thickbox' => false,
                'label'    => __( 'Replace Poster', 'media-cloud' ),
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
     * Filters the attachment data prepared for JavaScript. (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/media.php#L3279)
     *
     * @param array $response
     * @param int|object $attachment
     * @param array $meta
     *
     * @return array
     */
    public function prepareAttachmentForJS( $response, $attachment, $meta ) {
        if ( empty( $meta ) || !isset( $meta['api-video'] ) ) {
            return $response;
        }
        $video = APIVideo::videoForAttachment( $attachment->ID );
        if ( !$video ) {
            return $response;
        }
        $hls = arrayPath( $meta, 'api-video/hls', null );
        if ( !empty( $hls ) ) {
            $response['hls'] = [
                'url'       => $hls,
                'subtitles' => $video->subtitlesData,
            ];
        }
        $mp4 = arrayPath( $meta, 'api-video/mp4', null );
        if ( $mp4 ) {
            $response['mp4'] = [
                'url'       => $mp4,
                'subtitles' => $video->subtitlesData,
            ];
        }
        return $response;
    }

    function registerMetabox() {
        add_action( 'admin_init', function () {
            add_meta_box(
                'mcloud-mux-meta',
                'api.video Info',
                function ( $post ) {
                    $video = APIVideo::videoForAttachment( $post->ID );
                    echo View::render_view( 'admin.encoder-properties', [
                        'hasVideo'     => $video !== null,
                        'attachmentId' => $post->ID,
                        'subtitles'    => $video->subtitlesData,
                    ] );
                },
                'attachment',
                'side',
                'low'
            );
        } );
    }

    //endregion
    //region Replace Poster
    public function ajaxReplacePoster() {
        wp_verify_nonce( $_POST['nonce'], 'media-cloud-mux-replace-poster' );
        $attachmentId = arrayPath( $_POST, 'attachmentId', null );
        if ( empty( $attachmentId ) ) {
            wp_send_json_error( 'Missing attachment ID', 400 );
        }
        $newPosterId = arrayPath( $_POST, 'posterId', null );
        if ( empty( $newPosterId ) ) {
            wp_send_json_error( 'Missing poster ID', 400 );
        }
        if ( !postIdExists( $attachmentId ) ) {
            wp_send_json_error( 'Invalid attachment ID', 400 );
        }
        if ( !postIdExists( $newPosterId ) ) {
            wp_send_json_error( 'Invalid poster ID', 400 );
        }
        update_post_meta( $attachmentId, '_thumbnail_id', $newPosterId );
        wp_send_json( [
            'success' => true,
        ] );
    }

    //endregion
    //region Subtitle Handling
    /**
     *
     * @param int $attachmentId
     * @param string $language_code
     * @param string $name
     * @param bool $cc
     * @param string $url
     *
     * @return array{ error: bool, status:int, message:string }
     */
    function addSubtitles(
        $attachmentId,
        $language_code,
        $name,
        $cc,
        $url,
        $subtitleContents,
        $subtitlesFile
    ) {
        $video = APIVideo::videoForAttachment( $attachmentId );
        if ( !$video ) {
            return [
                'error'   => true,
                'status'  => 500,
                'message' => 'Video not found',
            ];
        }
        if ( $name === $language_code && isset( VideoEncoderConsts::$simpleSubtitleLanguages[$language_code] ) ) {
            $name = VideoEncoderConsts::$simpleSubtitleLanguages[$language_code];
        }
        $client = new APIVideoClient();
        $client->addSubtitles(
            $video->video_id,
            $language_code,
            $name,
            $subtitlesFile
        );
        $video->subtitlesData = array_merge( $video->subtitlesData, [[
            'local'         => true,
            'cc'            => $cc,
            'name'          => $name,
            'language_code' => $language_code,
            'url'           => $url,
        ]] );
        $video->save();
        return [
            'error'   => false,
            'status'  => 200,
            'message' => null,
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
        $video = APIVideo::videoForAttachment( $attachmentId );
        if ( !$video ) {
            return [
                'error'   => true,
                'status'  => 500,
                'message' => 'Video not found',
            ];
        }
        $client = new APIVideoClient();
        $client->deleteSubtitles( $video->video_id, $language_code );
        $newSubtitles = [];
        foreach ( $video->subtitlesData as $subtitlesDatum ) {
            if ( $subtitlesDatum['language_code'] === $language_code ) {
                continue;
            }
            $newSubtitles[] = $subtitlesDatum;
        }
        $video->subtitlesData = $newSubtitles;
        $video->save();
        return [
            'error'   => false,
            'status'  => 200,
            'message' => null,
        ];
    }

    //endregion
    //region REST
    /**
     * @inheritDoc
     */
    function integrateREST() {
        add_action( 'rest_api_init', function () {
            register_rest_field( 'attachment', 'hls', [
                'get_callback'    => function ( $data ) {
                    $attachment = $data['id'];
                    $meta = wp_get_attachment_metadata( $attachment );
                    if ( !isset( $meta['api-video'] ) ) {
                        return null;
                    }
                    $data = $this->playerData(
                        [],
                        [],
                        $attachment,
                        $meta
                    );
                    if ( empty( $data ) ) {
                        return null;
                    }
                    return [
                        'playlist'  => $data->playlistUrl,
                        'poster'    => get_the_post_thumbnail_url( $attachment, 'full' ),
                        'filmstrip' => null,
                        'mp4'       => $data->renditionUrl,
                        'subtitles' => $data->subtitles ?? [],
                        'width'     => (int) arrayPath( $meta, 'width', 0 ),
                        'height'    => (int) arrayPath( $meta, 'height', 0 ),
                        'duration'  => (int) arrayPath( $meta, 'length', 0 ),
                    ];
                },
                'update_callback' => null,
                'schema'          => null,
            ] );
        } );
    }

    //endregion
    //region Watermark
    function resetWatermark() {
        return true;
    }

    //endregion
}
