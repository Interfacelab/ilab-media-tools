<?php

namespace MediaCloud\Plugin\Tools\Video\Driver\Cloudflare;

use MediaCloud\Plugin\Tools\Storage\StorageTool;
use MediaCloud\Plugin\Tools\Storage\StorageToolSettings;
use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Tools\Video\VideoEncoderConsts;
use MediaCloud\Plugin\Tools\Video\VideoEncodingInterface;
use MediaCloud\Plugin\Tools\VideoPlayer\VideoPlayerData;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use MediaCloud\Plugin\Utilities\NoticeManager;
use MediaCloud\Plugin\Utilities\Prefixer;
use MediaCloud\Plugin\Utilities\View;
use MediaCloud\Vendor\GuzzleHttp\Client;
use MediaCloud\Vendor\GuzzleHttp\RequestOptions;
use function MediaCloud\Plugin\Utilities\anyEmpty;
use function MediaCloud\Plugin\Utilities\anyNull;
use function MediaCloud\Plugin\Utilities\arrayPath;
use function MediaCloud\Plugin\Utilities\mergeCSSStyles;
use function MediaCloud\Plugin\Utilities\postIdExists;
class CloudflareStreamTool implements VideoEncodingInterface {
    /* @var CloudflareStreamToolSettings|CloudflareStreamToolProSettings */
    protected $settings = null;

    protected $cloudflareIconSVG = null;

    //region Constructor/Setup
    public function __construct() {
        $this->settings = CloudflareStreamToolSettings::instance();
    }

    function validateSettings() {
        return !anyNull( $this->settings->accountId, $this->settings->apiKey, $this->settings->cdnUrl );
    }

    function settingsChanged() {
    }

    function enabled() {
        return $this->validateSettings();
    }

    function setup() {
        $currentWebhookUrl = get_option( 'mcloud-cloudflare-webhook-url' );
        if ( $currentWebhookUrl !== $this->settings->webhookUrl ) {
            $client = new CloudflareStreamClient();
            if ( $client->registerWebhook( $this->settings->webhookUrl ) ) {
                update_option( 'mcloud-cloudflare-webhook-url', $this->settings->webhookUrl );
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

    public function deleteAttachment( $id ) {
        if ( !$this->settings->deleteFromStream ) {
            return $id;
        }
        $meta = wp_get_attachment_metadata( $id, true );
        $videoId = arrayPath( $meta, 'cloudflare-stream/videoId', null );
        if ( empty( $videoId ) ) {
            return $id;
        }
        $client = new CloudflareStreamClient();
        $client->deleteVideo( $videoId );
        return $id;
    }

    public function doImportUrl( $attachmentId, $meta, $doUpdate = true ) {
        $client = new CloudflareStreamClient();
        if ( ToolsManager::instance()->toolEnabled( 'storage' ) ) {
            $url = wp_get_attachment_url( $attachmentId );
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
    //region Admin UI
    function driverIcon() {
        if ( $this->cloudflareIconSVG === null ) {
            $svg = file_get_contents( ILAB_PUB_IMG_DIR . '/icon-cloudflare.svg' );
            $this->cloudflareIconSVG = 'data:image/svg+xml;base64,' . base64_encode( $svg );
        }
        return $this->cloudflareIconSVG;
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
                return '<# if (data.hasOwnProperty("hls")) {#>has-cloudflare-stream<#}#>' . $additionalClasses;
            } );
            add_filter( 'media-cloud/media-library/attachment-icons', function ( $additionalIcons ) {
                $hlsIcon = '<i class="cloudflare-stream-icon"></i>';
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
                $meta = wp_get_attachment_metadata( $id );
                if ( !isset( $meta['cloudflare-stream'] ) ) {
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
						var replace = '<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }} <# if (data.hasOwnProperty("hls")) {#>has-cloudflare-stream<#}#>"><i class="cloudflare-stream-icon"></i>';
						txt = txt.replace(search, replace);
						attachTemplate.text(txt);
					}

					var attachTemplate = jQuery('#tmpl-attachment-grid-view');
					if (attachTemplate) {
						var txt = attachTemplate.text();

						var search = '<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }}">';
						var replace = '<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }} <# if (data.hasOwnProperty("hls")) {#>has-cloudflare-stream<#}#>"><i class="cloudflare-stream-icon"></i>';
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
     * Filters the attachment data prepared for JavaScript. (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/media.php#L3279)
     *
     * @param array $response
     * @param int|object $attachment
     * @param array $meta
     *
     * @return array
     */
    public function prepareAttachmentForJS( $response, $attachment, $meta ) {
        if ( empty( $meta ) || !isset( $meta['cloudflare-stream'] ) ) {
            return $response;
        }
        $videoId = arrayPath( $meta, 'cloudflare-stream/videoId', null );
        if ( !$videoId ) {
            return $response;
        }
        $private = arrayPath( $meta, 'cloudflare-stream/private', false );
        if ( !empty( $private ) ) {
            $client = new CloudflareStreamClient();
            $token = $client->token( $videoId, (int) arrayPath( $meta, 'length', 0 ) );
            $hls = trailingslashit( $this->settings->cdnUrl ) . ($token ?? $videoId) . '/manifest/video.m3u8';
        } else {
            $hls = trailingslashit( $this->settings->cdnUrl ) . $videoId . '/manifest/video.m3u8';
        }
        $subtitles = arrayPath( $meta, 'cloudflare-stream/subtitles', [] );
        $response['hls'] = [
            'url'       => $hls,
            'subtitles' => $subtitles,
        ];
        return $response;
    }

    function registerMetabox() {
        add_action( 'admin_init', function () {
            add_meta_box(
                'mcloud-mux-meta',
                'Cloudflare Stream Info',
                function ( $post ) {
                    $meta = wp_get_attachment_metadata( $post->ID );
                    $subtitles = arrayPath( $meta, 'cloudflare-stream/subtitles', [] );
                    echo View::render_view( 'admin.encoder-properties', [
                        'hasVideo'     => isset( $meta['cloudflare-stream'] ),
                        'attachmentId' => $post->ID,
                        'subtitles'    => $subtitles,
                    ] );
                },
                'attachment',
                'side',
                'low'
            );
        } );
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
        $meta = wp_get_attachment_metadata( $attachmentId );
        $videoId = arrayPath( $meta, 'cloudflare-stream/videoId', null );
        if ( empty( $videoId ) ) {
            return [
                'error'   => true,
                'status'  => 500,
                'message' => 'Video not found',
            ];
        }
        if ( $name === $language_code && isset( VideoEncoderConsts::$simpleSubtitleLanguages[$language_code] ) ) {
            $name = VideoEncoderConsts::$simpleSubtitleLanguages[$language_code];
        }
        $client = new CloudflareStreamClient();
        $client->addSubtitles(
            $videoId,
            $language_code,
            $name,
            $subtitlesFile
        );
        $subtitles = arrayPath( $meta, 'cloudflare-stream/subtitles', [] );
        $subtitles[] = [
            'local'         => true,
            'cc'            => $cc,
            'name'          => $name,
            'language_code' => $language_code,
            'url'           => $url,
        ];
        $meta['cloudflare-stream']['subtitles'] = $subtitles;
        wp_update_attachment_metadata( $attachmentId, $meta );
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
        $meta = wp_get_attachment_metadata( $attachmentId );
        $videoId = arrayPath( $meta, 'cloudflare-stream/videoId', null );
        if ( empty( $videoId ) ) {
            return [
                'error'   => true,
                'status'  => 500,
                'message' => 'Video not found',
            ];
        }
        $client = new CloudflareStreamClient();
        $client->deleteSubtitles( $videoId, $language_code );
        $subtitles = arrayPath( $meta, 'cloudflare-stream/subtitles', [] );
        $newSubtitles = [];
        foreach ( $subtitles as $subtitlesDatum ) {
            if ( $subtitlesDatum['language_code'] === $language_code ) {
                continue;
            }
            $newSubtitles[] = $subtitlesDatum;
        }
        $meta['cloudflare-stream']['subtitles'] = $newSubtitles;
        wp_update_attachment_metadata( $attachmentId, $meta );
        return [
            'error'   => false,
            'status'  => 200,
            'message' => null,
        ];
    }

    //endregion
    //region REST Integration
    function integrateREST() {
        add_action( 'rest_api_init', function () {
            register_rest_field( 'attachment', 'hls', [
                'get_callback'    => function ( $data ) {
                    $attachment = $data['id'];
                    $meta = wp_get_attachment_metadata( $attachment );
                    if ( !isset( $meta['cloudflare-stream'] ) ) {
                        return null;
                    }
                    $videoId = arrayPath( $meta, 'cloudflare-stream/videoId' );
                    if ( empty( $videoId ) ) {
                        return null;
                    }
                    $private = arrayPath( $meta, 'cloudflare-stream/private' );
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
                        'playlist'  => ( !empty( $private ) && $this->settings->excludePrivateREST ? null : $data->playlistUrl ),
                        'poster'    => get_the_post_thumbnail_url( $attachment, 'full' ),
                        'filmstrip' => null,
                        'mp4'       => ( !empty( $private ) && $this->settings->excludePrivateREST ? null : $data->renditionUrl ),
                        'subtitles' => ( !empty( $private ) && $this->settings->excludePrivateREST ? [] : arrayPath( $meta, 'cloudflare-stream/subtitles', [] ) ),
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
    //region Webhook
    protected function handleFinished( $videoId, $attachmentId ) {
        $private = get_post_meta( $attachmentId, '_cloudflare_stream_private', true );
        $meta = wp_get_attachment_metadata( $attachmentId, true );
        $bunnyClient = new CloudflareStreamClient();
        $info = $bunnyClient->videoInfo( $videoId );
        if ( !$info ) {
            Logger::error( "Missing video info." );
            return;
        }
        //		$hls = arrayPath($info, 'result/playback/hls', null);
        //		$dash = arrayPath($info, 'result/playback/dash', null);
        $meta['cloudflare-stream']['videoId'] = $videoId;
        $meta['cloudflare-stream']['status'] = 'ready';
        $meta['cloudflare-stream']['private'] = !empty( $private );
        wp_update_attachment_metadata( $attachmentId, $meta );
        if ( has_post_thumbnail( $attachmentId ) ) {
            Logger::warning( 'Bunny video already has thumbnail assigned.' );
            return;
        }
        if ( empty( $private ) ) {
            $url = arrayPath( $info, 'result/thumbnail', null );
        } else {
            $client = new CloudflareStreamClient();
            $token = $client->token( $videoId, 60 );
            $url = trailingslashit( $this->settings->cdnUrl ) . $token . '/thumbnails/thumbnail.jpg';
        }
        if ( empty( $url ) ) {
            Logger::warning( "No thumbnail found in video info." );
            return;
        }
        Logger::info(
            "Downloading thumbnail: {$url}",
            [],
            __METHOD__,
            __LINE__
        );
        $uploadDirInfo = wp_get_upload_dir();
        if ( ToolsManager::instance()->toolEnabled( 'storage' ) ) {
            Prefixer::setType( 'image/jpeg' );
            $prefix = StorageToolSettings::prefix();
            $uploadDir = trailingslashit( $uploadDirInfo['basedir'] ) . $prefix;
            @mkdir( $uploadDir, 0777, true );
        } else {
            $uploadDir = trailingslashit( $uploadDirInfo['basedir'] );
        }
        $filePath = trailingslashit( $uploadDir ) . $attachmentId . '-thumb.jpg';
        $client = new Client([
            RequestOptions::HEADERS => [
                'Referer' => get_home_url(),
            ],
        ]);
        try {
            $res = $client->get( $url );
            $body = $res->getBody();
            file_put_contents( $filePath, $body );
        } catch ( \Exception $ex ) {
            Logger::error( "Error download thumbnail: " . $ex->getMessage() );
        }
        if ( !file_exists( $filePath ) ) {
            Logger::error(
                "Could not download image {$url}.",
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
        update_post_meta( $attachmentId, '_thumbnail_id', $thumbId );
        wp_update_attachment_metadata( $thumbId, $thumbAttachmentMeta );
    }

    public function handleWebhook( $template ) {
        if ( strpos( $_SERVER['REQUEST_URI'], '/__cloudflare-stream/webhook' ) === 0 ) {
            $body = file_get_contents( 'php://input' );
            $json = json_decode( $body, true );
            Logger::info( $body );
            $videoId = arrayPath( $json, 'uid', null );
            $attachmentId = arrayPath( $json, 'meta/aid', null );
            Logger::info( "{$videoId} => {$attachmentId}" );
            if ( anyEmpty( $videoId, $attachmentId ) ) {
                wp_send_json( [
                    'status' => 'missing attachment id',
                ], 400 );
            }
            $status = arrayPath( $json, 'status/state', null );
            if ( $status === 'ready' ) {
                $this->handleFinished( $videoId, $attachmentId );
            }
            wp_send_json( [
                'status' => 'ok',
            ], 200 );
        }
        return $template;
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
    //region Player
    public function playerData(
        $existingData,
        $block,
        $attachmentId,
        $meta
    ) {
        if ( !isset( $meta['cloudflare-stream'] ) ) {
            return $existingData;
        }
        $videoId = arrayPath( $meta, 'cloudflare-stream/videoId', null );
        if ( empty( $videoId ) ) {
            return $existingData;
        }
        $originalUrl = wp_get_attachment_url( $attachmentId );
        $w = arrayPath( $meta, 'width', 0 );
        $h = arrayPath( $meta, 'height', 0 );
        $storyboardUrl = get_post_meta( $attachmentId, 'storyboard_vtt', true ) ?? null;
        $private = arrayPath( $meta, 'cloudflare-stream/private', false );
        if ( !empty( $private ) ) {
            $client = new CloudflareStreamClient();
            $token = $client->token( $videoId, (int) arrayPath( $meta, 'length', 0 ) );
            $hls = trailingslashit( $this->settings->cdnUrl ) . ($token ?? $videoId) . '/manifest/video.m3u8';
            $chromePlayerUrl = 'https://watch.videodelivery.net/' . $token;
        } else {
            $hls = trailingslashit( $this->settings->cdnUrl ) . $videoId . '/manifest/video.m3u8';
            $chromePlayerUrl = 'https://watch.videodelivery.net/' . $videoId;
        }
        $isAdmin = is_admin() || current_user_can( 'manage_options' );
        return new VideoPlayerData(
            '',
            $w,
            $h,
            arrayPath( $meta, 'length', 0 ),
            ( empty( $private ) || !empty( $isAdmin ) ? $originalUrl : null ),
            ( empty( $private ) || !empty( $isAdmin ) ? $originalUrl : null ),
            $hls,
            'application/vnd.apple.mpegurl',
            null,
            [],
            null,
            $chromePlayerUrl,
            'cloudflare',
            $storyboardUrl
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
    //region Watermark
    function resetWatermark() {
        return true;
    }

    //endregion
}
