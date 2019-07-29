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
namespace ILAB\MediaCloud\Tools\Vision;

use  ILAB\MediaCloud\Tasks\BatchManager ;
use  ILAB\MediaCloud\Tools\Tool ;
use  ILAB\MediaCloud\Tools\Vision\Batch\ImportVisionBatchProcess ;
use  ILAB\MediaCloud\Tools\Vision\Batch\ImportVisionBatchTool ;
use function  ILAB\MediaCloud\Utilities\arrayPath ;
use  ILAB\MediaCloud\Utilities\Environment ;
use  ILAB\MediaCloud\Utilities\NoticeManager ;
use  ILAB\MediaCloud\Vision\VisionDriver ;
use  ILAB\MediaCloud\Vision\VisionManager ;

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
    /** @var VisionDriver|null  */
    private  $driver = null ;
    /** @var bool Controls if vision processing for uploads is performed as a background task. */
    private  $alwaysBackground = false ;
    //endregion
    //region Constructor
    public function __construct( $toolName, $toolInfo, $toolManager )
    {
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
        new ImportVisionBatchProcess();
        $this->driver = VisionManager::visionInstance();
        if ( is_admin() ) {
            BatchManager::instance()->displayAnyErrors( 'rekognizer' );
        }
        add_filter( 'media-cloud/vision/detect-faces', function ( $enabled ) {
            return $this->enabled() && ($this->driver->config()->detectFaces() || $this->driver->config()->detectCelebrities());
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
            
            if ( VisionManager::driver() == 'rekognition' ) {
                add_filter(
                    'media-cloud/storage/after-upload',
                    function ( $data, $id ) {
                    
                    if ( $this->alwaysBackground ) {
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
                    
                    if ( $this->alwaysBackground ) {
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
                    
                    if ( $this->alwaysBackground ) {
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
                
                if ( $this->alwaysBackground ) {
                    return $maxUploads;
                } else {
                    return min( $maxUploads, 2 );
                }
            
            },
                10000,
                1
            );
            add_action( 'media-cloud/direct-uploads/process-batch', function ( $postIds ) {
                if ( $this->alwaysBackground ) {
                    BatchManager::instance()->addToBatchAndRun( ImportVisionBatchTool::BatchIdentifier(), $postIds, [] );
                }
            } );
        }
    
    }
    
    private function settingsChanged()
    {
        if ( !$this->driver->enabled() ) {
            if ( !empty($this->driver->enabledError()) ) {
                NoticeManager::instance()->displayAdminNotice( 'error', $this->driver->enabledError() );
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