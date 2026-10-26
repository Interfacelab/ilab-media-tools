<?php

namespace MediaCloud\Plugin\Tools\Video;

use MediaCloud\Plugin\Tasks\TaskManager;
use MediaCloud\Plugin\Tools\Tool;
use MediaCloud\Plugin\Tools\Video\Tasks\EncodeExistingVideosTask;
use MediaCloud\Plugin\Utilities\Environment;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use MediaCloud\Plugin\Utilities\NoticeManager;
use MediaCloud\Plugin\Utilities\View;
use function MediaCloud\Plugin\Utilities\anyEmpty;
use function MediaCloud\Plugin\Utilities\arrayPath;
class VideoEncodingTool extends Tool {
    /** @var VideoEncodingToolSettings  */
    private $settings;

    /** @var VideoEncodingInterface  */
    private $client;

    //region Constructor
    public function __construct( $toolName, $toolInfo, $toolManager ) {
        $this->settings = VideoEncodingToolSettings::instance();
        if ( !empty( $toolInfo['videoEncodingDrivers'] ) ) {
            foreach ( $toolInfo['videoEncodingDrivers'] as $key => $data ) {
                if ( empty( $data['name'] ) || empty( $data['class'] ) || empty( $data['config'] ) ) {
                    throw new \Exception("Video Encoding Tool configuration file is malformed.  Drivers are missing required information.");
                }
                if ( media_cloud_licensing()->is_plan__premium_only( 'pro' ) && isset( $data['pro-config'] ) && file_exists( ILAB_CONFIG_DIR . $data['pro-config'] ) ) {
                    $configFile = ILAB_CONFIG_DIR . $data['pro-config'];
                    $config = (include $configFile);
                } else {
                    $configFile = ILAB_CONFIG_DIR . $data['config'];
                    if ( !file_exists( $configFile ) ) {
                        throw new \Exception("Missing driver config file '{$configFile}'. ");
                    }
                    $config = (include $configFile);
                }
                VideoEncodingToolSettings::registerDriver(
                    $key,
                    $data['name'],
                    $data['class'],
                    $config,
                    arrayPath( $data, 'help', null )
                );
            }
        }
        do_action( 'media-cloud/video-encoding/register-drivers' );
        $driverConfigs = [];
        foreach ( VideoEncodingToolSettings::drivers() as $key => $driver ) {
            $driverConfigs[$key] = $driver['config'];
        }
        $toolInfo = $this->mergeSettings( $toolInfo, $driverConfigs );
        parent::__construct( $toolName, $toolInfo, $toolManager );
        $this->client = VideoEncodingToolSettings::encodingInstance();
        if ( $this->haveSettingsChanged() ) {
            $this->settingsChanged();
        }
        $this->testForBadPlugins();
        $this->testForUselessPlugins();
        add_action(
            'media-cloud/tools/register-setting-type',
            [$this, 'registerMuxSettingTypes'],
            10,
            5
        );
        add_action(
            'media-cloud/tools/register-setting-type',
            [$this, 'registerWatermarkEditorSettings'],
            10,
            5
        );
    }

    //endregion
    //region Setup and Validation
    public function settingsChanged() {
        $error = empty( $this->client );
        if ( !$error ) {
            try {
                $this->client->validateSettings();
                $this->client->settingsChanged();
            } catch ( VideoEncoderException $ex ) {
                $error = true;
            }
        }
        if ( $error && current_user_can( 'manage_options' ) ) {
            NoticeManager::instance()->displayAdminNotice( 'error', 'There is a serious issue with your video encoding settings.  Please check them and try again.' );
        }
    }

    public function enabled() {
        $enabled = parent::enabled();
        if ( $enabled && $this->client ) {
            return $this->client->enabled();
        }
        return false;
    }

    public function hasSettings() {
        return true;
    }

    public function setup() {
        if ( $this->enabled() ) {
            $this->client->setup();
            add_action( 'wp_ajax_mcloud-reset-watermark', [$this, 'resetWatermark'] );
        }
    }

    protected function actionCaptionDelete() {
        $nonce = arrayPath( $_POST, 'nonce' );
        if ( empty( $nonce ) || !wp_verify_nonce( $nonce, 'mux-delete-caption' ) ) {
            wp_send_json( [
                'status'  => 'error',
                'message' => 'Missing nonce.',
            ], 400 );
        }
        $attachmentId = (int) arrayPath( $_POST, 'aid', null );
        $lc = arrayPath( $_POST, 'language_code', null );
        if ( anyEmpty( $attachmentId, $lc ) ) {
            wp_send_json( [
                'status'  => 'error',
                'message' => 'Missing parameters.',
            ], 400 );
        }
        if ( $this->client ) {
            $result = $this->client->deleteSubtitles( $attachmentId, $lc );
            wp_send_json( [
                'status'  => ( $result['error'] ? 'error' : 'ok' ),
                'message' => $result['message'],
            ], $result['status'] );
        }
        wp_send_json( [
            'status'  => 'error',
            'message' => 'Unknown error.',
        ], 400 );
    }

    protected function actionCaptionUpload() {
        if ( !$this->client ) {
            wp_send_json( [
                'status'  => 'error',
                'message' => 'No video encoding client.',
            ], 500 );
        }
        $nonce = arrayPath( $_POST, 'nonce' );
        if ( empty( $nonce ) || !wp_verify_nonce( $nonce, 'mux-upload-caption' ) ) {
            wp_send_json( [
                'status'  => 'error',
                'message' => 'Missing nonce.',
            ], 400 );
        }
        $attachmentId = (int) arrayPath( $_POST, 'aid', null );
        if ( empty( $attachmentId ) ) {
            wp_send_json( [
                'status'  => 'error',
                'message' => 'Missing asset ID.',
            ], 400 );
        }
        $language = arrayPath( $_POST, 'language', null );
        if ( empty( $language ) ) {
            wp_send_json( [
                'status'  => 'error',
                'message' => 'Missing language.',
            ], 400 );
        }
        $cc = !empty( (int) arrayPath( $_POST, 'cc', null ) );
        $finfo = new \finfo(FILEINFO_MIME);
        $info = $finfo->file( $_FILES['file']['tmp_name'] );
        $infoParts = explode( ';', $info );
        $subtitleContents = file_get_contents( $_FILES['file']['tmp_name'] );
        $uploadedFile = wp_upload_bits( $_FILES['file']['name'], null, $subtitleContents );
        if ( isset( $uploadedFile['error'] ) && !empty( $uploadedFile['error'] ) ) {
            Logger::error(
                "Error importing caption: {$uploadedFile['error']}",
                [],
                __METHOD__,
                __LINE__
            );
            wp_send_json( [
                'status'  => 'error',
                'message' => 'Error importing captions',
            ], 400 );
        }
        $result = $this->client->addSubtitles(
            $attachmentId,
            $language,
            $language,
            $cc,
            $uploadedFile['url'],
            $subtitleContents,
            $_FILES['file']['tmp_name']
        );
        wp_send_json( [
            'status'  => ( $result['error'] ? 'error' : 'ok' ),
            'message' => $result['message'],
        ], $result['status'] );
    }

    //region
    // region Settings Fields
    public function registerMuxSettingTypes(
        $option,
        $optionInfo,
        $group,
        $groupInfo,
        $conditions
    ) {
        if ( $optionInfo['type'] === 'mux-webhook' ) {
            $description = arrayPath( $optionInfo, 'description', null );
            $webhookPath = arrayPath( $optionInfo, 'webhook-path', '/__mux/webhook' );
            $webhookUrl = home_url( $webhookPath );
            add_settings_field(
                $option,
                $optionInfo['title'],
                [$this, 'renderWebhookField'],
                $this->options_page,
                $group,
                [
                    'option'      => $option,
                    'description' => $description,
                    'conditions'  => $conditions,
                    'webhookUrl'  => $webhookUrl,
                ]
            );
        }
    }

    public function renderWebhookField( $args ) {
        echo View::render_view( 'settings.fields.mux-webhook', [
            'name'        => $args['option'],
            'conditions'  => $args['conditions'],
            'webhookUrl'  => $args['webhookUrl'],
            'description' => ( isset( $args['description'] ) ? $args['description'] : false ),
        ] );
    }

    public function registerWatermarkEditorSettings(
        $option,
        $optionInfo,
        $group,
        $groupInfo,
        $conditions
    ) {
        if ( $optionInfo['type'] === 'watermark-editor' ) {
            if ( is_admin() ) {
                add_filter(
                    'wp_script_attributes',
                    function ( $attributes ) {
                        if ( key_exists( 'id', $attributes ) && in_array( $attributes['id'], ['mcloud_settings_components_js-js'] ) ) {
                            $attributes['type'] = 'module';
                        }
                        return $attributes;
                    },
                    10,
                    1
                );
                wp_enqueue_script(
                    'mcloud_settings_components_js',
                    ILAB_PUB_URL . '/components/settings/assets/js/index.js',
                    null,
                    MEDIA_CLOUD_VERSION,
                    true
                );
                wp_enqueue_style(
                    'mcloud_settings_components_css',
                    ILAB_PUB_URL . '/components/settings/assets/css/index.css',
                    null,
                    MEDIA_CLOUD_VERSION
                );
            }
            $description = arrayPath( $optionInfo, 'description', null );
            $cardinal = arrayPath( $optionInfo, 'cardinal', true );
            add_settings_field(
                $option,
                $optionInfo['title'],
                [$this, 'renderWatermarkEditor'],
                $this->options_page,
                $group,
                [
                    'option'      => $option,
                    'description' => $description,
                    'conditions'  => $conditions,
                    'cardinal'    => ( empty( $cardinal ) ? 'false' : 'true' ),
                ]
            );
        }
    }

    public function renderWatermarkEditor( $args ) {
        $value = Environment::Option( $args['option'] );
        if ( empty( $value ) ) {
            $value = '';
        }
        echo View::render_view( 'base.fields.watermark-editor', [
            'value'       => $value,
            'name'        => $args['option'],
            'conditions'  => $args['conditions'],
            'cardinal'    => $args['cardinal'],
            'description' => ( isset( $args['description'] ) ? $args['description'] : false ),
        ] );
    }

    //endregion
    //region Provider Options/Help
    public function providerOptions() {
        $providers = [];
        foreach ( VideoEncodingToolSettings::drivers() as $id => $driver ) {
            $providers[$id] = $driver['name'];
        }
        return $providers;
    }

    public function providerHelp() {
        $help = [];
        foreach ( VideoEncodingToolSettings::drivers() as $id => $driver ) {
            $helpData = arrayPath( $driver, 'help', null );
            if ( !empty( $helpData ) ) {
                $help[$id] = $helpData;
            }
        }
        return $help;
    }

    //region
    //region Import
    public function importUrl( $attachmentId, $meta ) {
        if ( $this->client ) {
            $this->client->importUrl( $attachmentId, $meta );
        }
    }

    //endregion
    //region Watermark
    public function resetWatermark() {
        wp_verify_nonce( 'mcloud-reset-watermark' );
        if ( $this->client ) {
            if ( !$this->client->resetWatermark() ) {
                wp_send_json( [
                    'status' => 'error',
                ], 500 );
            }
        }
        wp_send_json( [
            'status' => 'ok',
        ], 200 );
    }

    //endregion
}
