<?php

// Copyright (c) 2016 Interfacelab LLC. All rights reserved.
//
// Released under the GPLv3 license
// http://www.gnu.org/licenses/gpl-3.0.html
//
// Uses code from:
// Persist Admin Notices Dismissal
// by Agbonghama Collins and Andy Fragen
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************
namespace MediaCloud\Plugin\Tools\Video\CLI;

use  MediaCloud\Plugin\CLI\Command ;
use  MediaCloud\Plugin\Tools\Storage\StorageConstants ;
use  MediaCloud\Plugin\Tools\Storage\StorageTool ;
use  MediaCloud\Plugin\Tools\ToolsManager ;
use  MediaCloud\Plugin\Tools\Video\Driver\Mux\Models\MuxAsset ;
use  MediaCloud\Plugin\Utilities\Logging\Logger ;
use  MediaCloud\Vendor\Chrisyue\PhpM3u8\Facade\DumperFacade ;
use  MediaCloud\Vendor\Chrisyue\PhpM3u8\Facade\ParserFacade ;
use  MediaCloud\Vendor\Chrisyue\PhpM3u8\Stream\TextStream ;
use function  MediaCloud\Plugin\Utilities\arrayPath ;
use function  MediaCloud\Plugin\Utilities\ilab_stream_download ;

if ( !defined( 'ABSPATH' ) ) {
    header( 'Location: /' );
    die;
}

/**
 * Import to Cloud Storage, rebuild thumbnails, etc.
 * @package MediaCloud\Plugin\CLI\Storage
 */
class VideoCommands extends Command
{
    private  $debugMode = false ;
    /**
     * Transfers a mux encoded video to cloud storage
     *
     * ## OPTIONS
     *
     *
     * [--limit=<number>]
     * : The maximum number of items to process, default is infinity.
     *
     * [--offset=<number>]
     * : The starting offset to process.  Cannot be used with page.
     *
     * [--page=<number>]
     * : The starting offset to process.  Page numbers start at 1.  Cannot be used with offset.
     *
     * [--dest=<string>]
     * : The destination path on cloud storage to transfer to, for example `/video/`.
     *
     * [--delete]
     * : Deletes the video from Mux after the transfer is complete.
     *
     * [--local-only]
     * : Saves the HLS encoded video to the local server only.
     *
     * [--skip-transferred]
     * : Skips videos that have already been transferred from Mux.
     *
     * [--order-by=<string>]
     * : The field to sort the items to be imported by. Valid values are 'date', 'title' and 'filename'.
     * ---
     * options:
     *   - date
     *   - title
     *   - filename
     * ---
     *
     * [--order=<string>]
     * : The sort order. Valid values are 'asc' and 'desc'.
     * ---
     * default: asc
     * options:
     *   - asc
     *   - desc
     * ---
     *
     * @when after_wp_load
     *
     * @param $args
     * @param $assoc_args
     *
     * @throws \Exception
     */
    public function transfer( $args, $assoc_args )
    {
        /** @var \Freemius $media_cloud_licensing */
        global  $media_cloud_licensing ;
        self::Error( "Only available in the Premium version.  To upgrade: https://mediacloud.press/pricing/" );
    }
    
    /**
     * Relinks Mux videos that were transferred to the local server or cloud storage.
     *
     * ## OPTIONS
     *
     *
     * [--limit=<number>]
     * : The maximum number of items to process, default is infinity.
     *
     * [--offset=<number>]
     * : The starting offset to process.  Cannot be used with page.
     *
     * [--page=<number>]
     * : The starting offset to process.  Page numbers start at 1.  Cannot be used with offset.
     *
     * [--order-by=<string>]
     * : The field to sort the items to be imported by. Valid values are 'date', 'title' and 'filename'.
     * ---
     * options:
     *   - date
     *   - title
     *   - filename
     * ---
     *
     * [--order=<string>]
     * : The sort order. Valid values are 'asc' and 'desc'.
     * ---
     * default: asc
     * options:
     *   - asc
     *   - desc
     * ---
     *
     * @when after_wp_load
     *
     * @param $args
     * @param $assoc_args
     *
     * @throws \Exception
     */
    public function relink( $args, $assoc_args )
    {
        /** @var \Freemius $media_cloud_licensing */
        global  $media_cloud_licensing ;
        self::Error( "Only available in the Premium version.  To upgrade: https://mediacloud.press/pricing/" );
    }
    
    public static function Register()
    {
        \WP_CLI::add_command( 'mediacloud:video', __CLASS__ );
    }

}