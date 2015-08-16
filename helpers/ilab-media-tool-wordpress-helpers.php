<?php

if (!defined('ABSPATH')) { header('Location: /'); die; }

/**
 * Created by PhpStorm.
 * User: jong
 * Date: 7/31/15
 * Time: 10:32 AM
 */
function ilab_get_image_sizes($size=null)
{
    global $_wp_additional_image_sizes;

    $sizes = [];
    $get_intermediate_image_sizes = get_intermediate_image_sizes();

    // Create the full array with sizes and crop info
    foreach( $get_intermediate_image_sizes as $_size )
    {
        if (in_array($_size, ['thumbnail','medium', 'large']))
        {
            $sizes[$_size]['width'] = get_option($_size.'_size_w');
            $sizes[$_size]['height'] = get_option($_size.'_size_h');
            $sizes[$_size]['crop'] = (bool)get_option($_size.'_crop');
        }
        else if (isset($_wp_additional_image_sizes[$_size ]))
        {
            $sizes[$_size] = [
                'width' => $_wp_additional_image_sizes[$_size]['width'],
                'height' => $_wp_additional_image_sizes[$_size]['height'],
                'crop' =>  $_wp_additional_image_sizes[$_size]['crop']
            ];
        }
    }

    if ($size!=null)
    {
        if (isset($sizes[$size]))
            return $sizes[$size];

        return null;
    }

    return $sizes;
}

function relative_admin_url($path = '', $scheme = 'admin' ) {
    $url=get_admin_url( null, $path, $scheme );
    $site=home_url('','admin');
    return str_replace($site,'',$url);
}