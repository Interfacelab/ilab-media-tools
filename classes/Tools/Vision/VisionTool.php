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
namespace MediaCloud\Plugin\Tools\Vision;

use  MediaCloud\Plugin\Tasks\TaskManager ;
use  MediaCloud\Plugin\Tasks\TaskSchedule ;
use  MediaCloud\Plugin\Tools\Tool ;
use  MediaCloud\Plugin\Tools\ToolsManager ;
use  MediaCloud\Plugin\Tools\Vision\Tasks\ProcessVisionTask ;
use  MediaCloud\Plugin\Utilities\Environment ;
use  MediaCloud\Plugin\Utilities\Logging\Logger ;
use  MediaCloud\Plugin\Utilities\NoticeManager ;
use  MediaCloud\Plugin\Tools\Vision\VisionDriver ;
use  MediaCloud\Plugin\Tools\Vision\VisionManager ;
use  MediaCloud\Plugin\Tools\Vision\VisionToolSettings ;
use function  MediaCloud\Plugin\Utilities\arrayPath ;

if ( !defined( 'ABSPATH' ) ) {
    header( 'Location: /' );
    die;
}

/**
 * Class VisionTool
 *
 * Vision tool.
 */
class VisionTool extends Tool
{
    //region Class Variables
    /** @var VisionToolSettings  */
    private  $settings = null ;
    /** @var VisionDriver|null  */
    private  $driver = null ;
    /** @var bool Controls if vision processing for uploads is performed as a background task. */
    private  $alwaysBackground = false ;
    //endregion
    //region Constructor
    public function __construct( $toolName, $toolInfo, $toolManager )
    {
        $this->settings = VisionToolSettings::instance();
        if ( !empty($toolInfo['visionDrivers']) ) {
            foreach ( $toolInfo['visionDrivers'] as $key => $data ) {
                if ( empty($data['name']) || empty($data['class']) || empty($data['config']) ) {
                    throw new \Exception( "Vision Tool configuration file is malformed.  Storage drivers are missing required information." );
                }
                $configFile = ILAB_CONFIG_DIR . $data['config'];
                if ( !file_exists( $configFile ) ) {
                    throw new \Exception( "Missing driver config file '{$configFile}'. " );
                }
                $config = (include $configFile);
                VisionManager::registerDriver(
                    $key,
                    $data['name'],
                    $data['class'],
                    $config,
                    $data['help']
                );
            }
        }
        global  $media_cloud_licensing ;
        $driverConfigs = [];
        foreach ( VisionManager::drivers() as $key => $driver ) {
            $driverConfigs[$key] = $driver['config'];
        }
        $toolInfo = $this->mergeSettings( $toolInfo, $driverConfigs );
        parent::__construct( $toolName, $toolInfo, $toolManager );
        $this->driver = VisionManager::visionInstance();
        add_filter( 'media-cloud/vision/detect-faces', function ( $enabled ) {
            return $this->enabled() && ($this->settings->detectFaces || $this->settings->detectCelebrities);
        } );
        $this->alwaysBackground = Environment::Option( 'mcloud-vision-always-background', null, false );
    }
    
    public function setup()
    {
        parent::setup();
        if ( $this->haveSettingsChanged() ) {
            $this->settingsChanged();
        }
        $this->testForBadPlugins();
        $this->testForUselessPlugins();
        
        if ( $this->enabled() ) {
            $this->settings->associateTax();
            TaskManager::registerTask( ProcessVisionTask::class );
            
            if ( VisionManager::driver() == 'rekognition' ) {
                add_filter(
                    'media-cloud/storage/after-upload',
                    function ( $data, $id ) {
                    $allowBackground = apply_filters( 'media-cloud/vision/allow-background', true );
                    
                    if ( $this->alwaysBackground && !empty($allowBackground) ) {
                        $this->addToBackgroundTask( $id );
                        return $data;
                    } else {
                        return $this->processImageMeta( $data, $id );
                    }
                
                },
                    1000,
                    2
                );
            } else {
                add_filter(
                    'wp_update_attachment_metadata',
                    function ( $data, $id ) {
                    $allowBackground = apply_filters( 'media-cloud/vision/allow-background', true );
                    
                    if ( $this->alwaysBackground && !empty($allowBackground) ) {
                        $this->addToBackgroundTask( $id );
                        return $data;
                    } else {
                        return $this->processImageMeta( $data, $id );
                    }
                
                },
                    1000,
                    2
                );
                add_filter(
                    'media-cloud/vision/process-meta',
                    function ( $data, $id ) {
                    $allowBackground = apply_filters( 'media-cloud/vision/allow-background', true );
                    
                    if ( $this->alwaysBackground && !empty($allowBackground) ) {
                        $this->addToBackgroundTask( $id );
                        return $data;
                    } else {
                        return $this->processImageMeta( $data, $id );
                    }
                
                },
                    10,
                    2
                );
            }
            
            add_filter(
                'media-cloud/direct-uploads/max-uploads',
                function ( $maxUploads ) {
                $allowBackground = apply_filters( 'media-cloud/vision/allow-background', true );
                
                if ( $this->alwaysBackground && !empty($allowBackground) ) {
                    return $maxUploads;
                } else {
                    return min( $maxUploads, 2 );
                }
            
            },
                10000,
                1
            );
            add_action( 'media-cloud/direct-uploads/process-batch', function ( $postIds ) {
                $allowBackground = apply_filters( 'media-cloud/vision/allow-background', true );
                
                if ( $this->alwaysBackground && !empty($allowBackground) ) {
                    $task = new ProcessVisionTask();
                    $task->prepare( [], $postIds );
                    TaskManager::instance()->queueTask( $task );
                }
            
            } );
        }
    
    }
    
    private function addToBackgroundTask( $postId )
    {
        if ( empty(apply_filters( 'media-cloud/vision/allow-background-processing', true )) ) {
            return;
        }
        $task = TaskSchedule::nextScheduledTaskOfType( ProcessVisionTask::identifier() );
        
        if ( !empty($task) ) {
            Logger::info(
                "Adding to existing vision task",
                [],
                __METHOD__,
                __LINE__
            );
            $task->selection = array_merge( $task->selection, [ $postId ] );
            Logger::info(
                "Selection length: " . count( $task->selection ),
                [],
                __METHOD__,
                __LINE__
            );
            $task->save();
        } else {
            Logger::info(
                "Creating vision task",
                [],
                __METHOD__,
                __LINE__
            );
            ProcessVisionTask::scheduleIn( 2, [], [ $postId ] );
        }
    
    }
    
    private function settingsChanged()
    {
        if ( ToolsManager::instance()->toolEnvEnabled( 'vision' ) ) {
            if ( !$this->driver->enabled() ) {
                if ( !empty($this->driver->enabledError()) ) {
                    NoticeManager::instance()->displayAdminNotice( 'error', $this->driver->enabledError() );
                }
            }
        }
    }
    
    //endregion
    //region Tool Overrides
    public function hasSettings()
    {
        return true;
    }
    
    public function enabled()
    {
        if ( !parent::enabled() ) {
            return false;
        }
        if ( empty($this->driver) ) {
            return false;
        }
        
        if ( !$this->driver->enabled() ) {
            
            if ( !empty($this->driver->enabledError()) ) {
                NoticeManager::instance()->displayAdminNotice( 'error', $this->driver->enabledError() );
            } else {
                if ( !$this->driver->minimumOptionsEnabled() ) {
                    NoticeManager::instance()->displayAdminNotice( 'warning', "You have enabled the Vision tool, but none of the options are on.", false );
                }
            }
            
            return false;
        }
        
        return true;
    }
    
    //endregion
    //region Settings Helpers
    /**
     * Returns a list of taxonomies for Attachments, used in the Rekognition settings page.
     * @return array
     */
    public function attachmentTaxonomies()
    {
        $taxonomies = [
            'category' => 'Category',
            'post_tag' => 'Tag',
        ];
        $attachTaxes = get_object_taxonomies( 'attachment' );
        if ( !empty($attachTaxes) ) {
            foreach ( $attachTaxes as $attachTax ) {
                if ( !in_array( $attachTax, [ 'post_tag', 'category' ] ) ) {
                    $taxonomies[$attachTax] = ucwords( str_replace( '_', ' ', $attachTax ) );
                }
            }
        }
        return $taxonomies;
    }
    
    //endregion
    //region Processing
    /**
     * Process an image through Rekognition
     *
     * @param array $meta
     * @param int $postID
     *
     * @return array
     */
    public function processImageMeta( $meta, $postID )
    {
        return $this->driver->processImage( $postID, $meta );
    }
    
    //endregion
    //region Settings
    public function providerOptions()
    {
        $providers = [];
        foreach ( VisionManager::drivers() as $id => $driver ) {
            $providers[$id] = $driver['name'];
        }
        return $providers;
    }
    
    public function providerHelp()
    {
        $help = [];
        foreach ( VisionManager::drivers() as $id => $driver ) {
            $helpData = arrayPath( $driver, 'help', null );
            if ( !empty($helpData) ) {
                $help[$id] = $helpData;
            }
        }
        return $help;
    }

}