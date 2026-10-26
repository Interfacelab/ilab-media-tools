<?php

namespace MediaCloud\Plugin\Tools\Video\Driver\Bunny;

use MediaCloud\Plugin\Tools\Storage\StorageTool;
use MediaCloud\Plugin\Tools\Storage\StorageToolSettings;
use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Tools\Video\Driver\Bunny\Data\BunnyStreamDatabase;
use MediaCloud\Plugin\Tools\Video\Driver\Bunny\Models\BunnyVideo;
use MediaCloud\Plugin\Tools\Video\VideoEncoderConsts;
use MediaCloud\Plugin\Tools\Video\VideoEncodingInterface;
use MediaCloud\Plugin\Tools\VideoPlayer\VideoPlayerData;
use MediaCloud\Plugin\Utilities\Bunny\BunnyTokenAuth;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use MediaCloud\Plugin\Utilities\Prefixer;
use MediaCloud\Plugin\Utilities\View;
use MediaCloud\Vendor\GuzzleHttp\Client;
use MediaCloud\Vendor\GuzzleHttp\RequestOptions;
use function MediaCloud\Plugin\Utilities\anyNull;
use function MediaCloud\Plugin\Utilities\arrayPath;
use function MediaCloud\Plugin\Utilities\mergeCSSStyles;
use function MediaCloud\Plugin\Utilities\postIdExists;
class BunnyStreamTool implements VideoEncodingInterface {
    //region Props
    /* @var BunnyStreamToolSettings|BunnyStreamToolProSettings */
    protected $settings = null;

    protected $bunnyIconSVG = null;

    //endregion
    //region Constructor/Setup
    public function __construct() {
        $this->settings = BunnyStreamToolSettings::instance();
    }

    function validateSettings() {
        return !anyNull( $this->settings->libraryId, $this->settings->apiKey, $this->settings->cdnUrl );
    }

    function settingsChanged() {
    }

    function enabled() {
        return $this->validateSettings();
    }

    function setup() {
        BunnyStreamDatabase::init();
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
        $video = BunnyVideo::videoForAttachment( $id );
        if ( $video === null ) {
            return $id;
        }
        if ( !$this->settings->deleteFromStream ) {
            return $id;
        }
        $client = new BunnyStreamClient();
        $client->deleteVideo( $video->video_id );
        $video->delete();
        return $id;
    }

    public function doImportUrl( $attachmentId, $meta, $doUpdate = true ) {
        $client = new BunnyStreamClient();
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
     * @param BunnyVideo $video
     */
    protected function handleFinished( $video ) {
        if ( empty( $video->attachment_id ) ) {
            return;
        }
        Logger::info( "Video private: " . $video->private );
        $meta = wp_get_attachment_metadata( $video->attachment_id, true );
        $meta['bunny-stream'] = [
            'library' => $this->settings->libraryId,
            'videoId' => $video->video_id,
            'status'  => $video->status,
            'private' => $video->private == 1,
        ];
        $bunnyClient = new BunnyStreamClient();
        $info = $bunnyClient->videoInfo( $video->video_id );
        if ( $info ) {
            $resolutionsText = arrayPath( $info, 'availableResolutions', "" );
            $resolutions = explode( ',', $resolutionsText );
            usort( $resolutions, function ( $a, $b ) {
                return strcmp( $b, $a );
            } );
            $meta['bunny-stream']['resolutions'] = $resolutions;
            $meta['bunny-stream']['thumbnail-count'] = arrayPath( $info, 'thumbnailCount', 1 );
            $meta['bunny-stream']['storage-size'] = arrayPath( $info, 'storageSize', arrayPath( $meta, 'filesize', 0 ) );
        }
        Logger::info( "Updating meta for {$video->attachment_id}: " . json_encode( $meta, JSON_PRETTY_PRINT ) );
        wp_update_attachment_metadata( $video->attachment_id, $meta );
        if ( has_post_thumbnail( $video->attachment_id ) ) {
            Logger::warning( 'Bunny video already has thumbnail assigned.' );
            return;
        }
        $url = "{$this->settings->cdnUrl}/{$video->video_id}/thumbnail.jpg";
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
        update_post_meta( $video->attachment_id, '_thumbnail_id', $thumbId );
        wp_update_attachment_metadata( $thumbId, $thumbAttachmentMeta );
    }

    public function handleWebhook( $template ) {
        if ( strpos( $_SERVER['REQUEST_URI'], '/__bunnycdn/webhook' ) === 0 ) {
            $body = file_get_contents( 'php://input' );
            $json = json_decode( $body, true );
            $videoId = arrayPath( $json, 'VideoGuid', null );
            $status = arrayPath( $json, 'Status', null );
            Logger::info( "Bunny webhook: " . BunnyVideo::statusString( $status ) );
            if ( empty( $videoId ) || $status === null ) {
                wp_send_json( [
                    'status' => 'missing video guid',
                ], 400 );
            }
            try {
                $video = BunnyVideo::findOrCreate( $videoId );
                $video->status = intval( $status );
                $video->save();
                if ( $status === BunnyVideo::STATUS_FINISHED ) {
                    $this->handleFinished( $video );
                }
                wp_send_json( [
                    'status' => 'ok',
                ], 200 );
            } catch ( \Exception $ex ) {
                Logger::error( "Error fetching video: " . $ex->getMessage() );
                wp_send_json( [
                    'status' => 'error locating video',
                ], 500 );
            }
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
        if ( !isset( $meta['bunny-stream'] ) ) {
            return $existingData;
        }
        $videoId = arrayPath( $meta, 'bunny-stream/videoId', null );
        if ( empty( $videoId ) ) {
            return $existingData;
        }
        $video = BunnyVideo::video( $videoId );
        if ( empty( $video ) ) {
            return $existingData;
        }
        if ( empty( $meta['bunny-stream']['resolutions'] ) ) {
            $originalUrl = wp_get_attachment_url( $attachmentId );
        } else {
            $res = $meta['bunny-stream']['resolutions'][0];
            $originalUrl = "{$this->settings->cdnUrl}/{$meta['bunny-stream']['videoId']}/play_{$res}.mp4";
        }
        $w = arrayPath( $meta, 'width', 0 );
        $h = arrayPath( $meta, 'height', 0 );
        $storyboardUrl = get_post_meta( $attachmentId, 'storyboard_vtt', true ) ?? null;
        $hls = "{$this->settings->cdnUrl}/{$meta['bunny-stream']['videoId']}/playlist.m3u8";
        return new VideoPlayerData(
            $videoId,
            $w,
            $h,
            arrayPath( $meta, 'length', 0 ),
            $originalUrl,
            $originalUrl,
            $hls,
            'application/vnd.apple.mpegurl',
            null,
            $video->subtitlesData,
            null,
            null,
            null,
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
    //region Admin UI
    function driverIcon() {
        if ( $this->bunnyIconSVG === null ) {
            $svg = file_get_contents( ILAB_PUB_IMG_DIR . '/icon-bunnycdn.svg' );
            $this->bunnyIconSVG = 'data:image/svg+xml;base64,' . base64_encode( $svg );
        }
        return $this->bunnyIconSVG;
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
                return '<# if (data.hasOwnProperty("hls")) {#>has-bunny-stream<#}#>' . $additionalClasses;
            } );
            add_filter( 'media-cloud/media-library/attachment-icons', function ( $additionalIcons ) {
                $hlsIcon = '<i class="bunny-stream-icon"></i>';
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
                $video = BunnyVideo::videoForAttachment( $id );
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
						var replace = '<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }} <# if (data.hasOwnProperty("hls")) {#>has-bunny-stream<#}#>"><i class="bunny-stream-icon"></i>';
						txt = txt.replace(search, replace);
						attachTemplate.text(txt);
					}

					var attachTemplate = jQuery('#tmpl-attachment-grid-view');
					if (attachTemplate) {
						var txt = attachTemplate.text();

						var search = '<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }}">';
						var replace = '<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }} <# if (data.hasOwnProperty("hls")) {#>has-bunny-stream<#}#>"><i class="bunny-stream-icon"></i>';
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
        if ( empty( $meta ) || !isset( $meta['bunny-stream'] ) ) {
            return $response;
        }
        $videoId = arrayPath( $meta, 'bunny-stream/videoId', null );
        if ( !$videoId ) {
            return $response;
        }
        $video = BunnyVideo::video( $videoId );
        if ( !$video ) {
            return $response;
        }
        if ( !empty( $meta['bunny-stream']['resolutions'] ) ) {
            $res = $meta['bunny-stream']['resolutions'][0];
            $mp4 = "{$this->settings->cdnUrl}/{$meta['bunny-stream']['videoId']}/play_{$res}.mp4";
            $response['mp4'] = [
                'url'       => $mp4,
                'subtitles' => $video->subtitlesData,
            ];
        }
        $hls = "{$this->settings->cdnUrl}/{$meta['bunny-stream']['videoId']}/playlist.m3u8";
        $response['hls'] = [
            'url'       => $hls,
            'subtitles' => $video->subtitlesData,
        ];
        return $response;
    }

    function registerMetabox() {
        add_action( 'admin_init', function () {
            add_meta_box(
                'mcloud-mux-meta',
                'Bunny Stream Info',
                function ( $post ) {
                    $video = BunnyVideo::videoForAttachment( $post->ID );
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
        $video = BunnyVideo::videoForAttachment( $attachmentId );
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
        $client = new BunnyStreamClient();
        $client->addSubtitles(
            $video->video_id,
            $language_code,
            $name,
            $subtitleContents
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
        $video = BunnyVideo::videoForAttachment( $attachmentId );
        if ( !$video ) {
            return [
                'error'   => true,
                'status'  => 500,
                'message' => 'Video not found',
            ];
        }
        $client = new BunnyStreamClient();
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
    //region REST Integration
    function integrateREST() {
        add_action( 'rest_api_init', function () {
            register_rest_field( 'attachment', 'hls', [
                'get_callback'    => function ( $data ) {
                    $attachment = $data['id'];
                    $video = BunnyVideo::videoForAttachment( $attachment );
                    if ( empty( $video ) ) {
                        return null;
                    }
                    $meta = wp_get_attachment_metadata( $attachment );
                    if ( !isset( $meta['bunny-stream'] ) ) {
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
                    $private = arrayPath( $meta, 'bunny-stream/private' );
                    return [
                        'playlist'  => ( !empty( $private ) && $this->settings->excludePrivateREST ? null : $data->playlistUrl ),
                        'poster'    => get_the_post_thumbnail_url( $attachment, 'full' ),
                        'filmstrip' => null,
                        'mp4'       => ( !empty( $private ) && $this->settings->excludePrivateREST ? null : $data->renditionUrl ),
                        'subtitles' => ( !empty( $private ) && $this->settings->excludePrivateREST ? [] : $video->subtitlesData ),
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
    //region Watermark
    function resetWatermark() {
        return true;
    }

    //endregion
}
