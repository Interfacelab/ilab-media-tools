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

if (!defined('ABSPATH')) { header('Location: /'); die; }

require_once(ILAB_CLASSES_DIR.'/ilab-media-tool-base.php');
require_once(ILAB_CLASSES_DIR.'/ilab-media-tool-view.php');

/**
 * Class ILabMediaCropTool
 *
 * Crop tool
 */
class ILabMediaCropTool extends ILabMediaToolBase
{
    public function __construct($toolName, $toolInfo, $toolManager)
    {
        parent::__construct($toolName, $toolInfo, $toolManager);
    }

    /**
     * Setup the plugin
     */
    public function setup()
    {
        if ($this->enabled())
        {
            $this->hookupUI();

            add_action('admin_enqueue_scripts', [$this,'enqueueTheGoods']);
            add_action('wp_ajax_ilab_crop_image_page',[$this,'displayCropUI']);
            add_action('wp_ajax_ilab_perform_crop',[$this,'performCrop']);

            add_filter('ilab-s3-process-crop', function($size, $path, $sizeMeta){
                return $sizeMeta;
            }, 3, 3);
            add_filter('ilab-s3-process-file-name', function($filename) {
                return $filename;
            }, 3, 1);
        }
    }

    /**
     * Enqueue the CSS and JS needed to make the magic happen
     * @param $hook
     */
    public function enqueueTheGoods($hook)
    {
        add_thickbox();

        if ($hook == 'post.php')
            wp_enqueue_media ();
        else if ($hook == 'upload.php')
        {
            $mode = get_user_option('media_library_mode', get_current_user_id()) ? get_user_option('media_library_mode', get_current_user_id()) : 'grid';
            if (isset($_GET['mode']) && in_array($_GET ['mode'], ['grid','list']))
            {
                $mode = $_GET['mode'];
                update_user_option(get_current_user_id(), 'media_library_mode', $mode);
            }

            if ($mode=='list')
            {
                $version = get_bloginfo('version');
                if (version_compare($version, '4.2.2') < 0)
                    wp_dequeue_script ( 'media' );

                wp_enqueue_media ();
            }
        }
        else
            wp_enqueue_style ( 'media-views' );

        wp_enqueue_style( 'wp-pointer' );
        wp_enqueue_style ( 'ilab-modal-css', ILAB_PUB_CSS_URL . '/ilab-modal.min.css' );
        wp_enqueue_style ( 'ilab-media-tools-css', ILAB_PUB_CSS_URL . '/ilab-media-tools.min.css' );
        wp_enqueue_script( 'wp-pointer' );
        wp_enqueue_script ( 'ilab-modal-js', ILAB_PUB_JS_URL. '/ilab-modal.js', ['jquery'], false, true );
        wp_enqueue_script ( 'ilab-media-tools-js', ILAB_PUB_JS_URL. '/ilab-media-tools.js', ['jquery'], false, true );
    }

    /**
     * Hook up the "Crop Image" links/buttons in the admin ui
     */
    private function hookupUI()
    {
        // TODO: Still need to hook up the edit attachment page

        add_filter('media_row_actions',function($actions,$post){
            $newaction['ilab_crop_image'] = '<a class="ilab-thickbox" href="'.$this->cropPageURL($post->ID).'" title="Crop Image">' . __('Crop Image') . '</a>';
            return array_merge($actions,$newaction);
        },10,2);

        add_filter('admin_post_thumbnail_html', function($content,$id){
            if (!has_post_thumbnail($id))
                return $content;

            $image_id = get_post_thumbnail_id($id);
            if (!current_user_can('edit_post',$image_id))
                return $content;

            $content.='<a class="ilab-thickbox" href="'.$this->cropPageURL($image_id).'">'.__('Crop Image').'</a>';
            return $content;
        },10,2);

        add_action( 'wp_enqueue_media', function () {
            remove_action('admin_footer', 'wp_print_media_templates');

            add_action('admin_footer', function(){
                ob_start();
                wp_print_media_templates();
                $result=ob_get_clean();
                echo $result;


                $sizes=ilab_get_image_sizes();
                $sizeKeys=array_keys($sizes);

                ?>
                <script>
                    jQuery(document).ready(function() {
                        jQuery('input[type="button"]')
                            .filter(function() {
                                return this.id.match(/imgedit-open-btn-[0-9]+/);
                            })
                            .each(function(){
                                var image_id=this.id.match(/imgedit-open-btn-([0-9]+)/)[1];
                                var button=jQuery('<input type="button" class="button" style="margin-left:5px;" value="Crop Image">');
                                jQuery(this).after(button);

                                button.on('click',function(e){
                                    e.preventDefault();

                                    ILabModal.loadURL("<?php echo relative_admin_url('admin-ajax.php')?>?action=ilab_crop_image_page&size=<?php echo $sizeKeys[0]?>&post="+image_id,false,null);

                                    return false;
                                });
                            });

                        attachTemplate=jQuery('#tmpl-image-details');
                        if (attachTemplate)
                            attachTemplate.text(attachTemplate.text().replace(/(<input type="button" class="replace-attachment button")/,'<a href="<?php echo $this->cropPageURL('{{data.attachment.id}}')?>" class="ilab-thickbox button"><?php echo __('Crop Image') ?></a>&nbsp;$1'));

                        attachTemplate=jQuery('#tmpl-attachment-details-two-column');
                        if (attachTemplate)
                        {
                            attachTemplate.text(attachTemplate.text().replace(/(<button(?:.*)class="(?:.*)edit-attachment(?:.*)"[^>]*[^<]+<\/button>)/,'$1&nbsp;<a href="<?php echo $this->cropPageURL('{{data.id}}')?>" class="ilab-thickbox button"><?php echo __('Crop Image') ?></a>'));
                            attachTemplate.text(attachTemplate.text().replace(/(<a(?:.*)class="(?:.*)edit-imgix(?:.*)"[^>]*[^<]+<\/a>)/,'$1&nbsp;<a href="<?php echo $this->cropPageURL('{{data.id}}')?>" class="ilab-thickbox button"><?php echo __('Crop Image') ?></a>'));
                            attachTemplate.text(attachTemplate.text().replace(/(<a class="(?:.*)edit-attachment(?:.*)"[^>]+[^<]+<\/a>)/,'$1&nbsp;<a href="<?php echo $this->cropPageURL('{{data.id}}')?>" class="ilab-thickbox button"><?php echo __('Crop Image') ?></a>'));
                            attachTemplate.text(attachTemplate.text().replace(/(<a class="(?:.*)view-attachment(?:.*)"[^>]+[^<]+<\/a>)/,'$1 | <a class="ilab-thickbox" href="<?php echo $this->cropPageURL('{{data.id}}')?>"><?php echo __('Crop Image') ?></a>'));
                        }

                        attachTemplate=jQuery('#tmpl-attachment-details');
                        if (attachTemplate)
                        {
                            attachTemplate.text(attachTemplate.text().replace(/(<a(?:.*)class="(?:.*)edit-imgix(?:.*)"[^>]*[^<]+<\/a>)/, '$1<br><a class="ilab-thickbox" href="<?php echo $this->cropPageURL('{{data.id}}')?>"><?php echo __('Crop Image') ?></a>'));
                            attachTemplate.text(attachTemplate.text().replace(/(<a class="(?:.*)edit-attachment(?:.*)"[^>]+[^<]+<\/a>)/, '$1\n<a class="ilab-thickbox" href="<?php echo $this->cropPageURL('{{data.id}}')?>"><?php echo __('Crop Image') ?></a>'));
                        }
                    });
                </script>
                <?php
            },1000);
        } );
    }

    /**
     * Generate the url for the crop UI
     * @param $id
     * @param string $size
     * @return string
     */
    public function cropPageURL($id, $size = 'thumbnail', $partial=false)
    {
        $url=relative_admin_url('admin-ajax.php')."?action=ilab_crop_image_page&post=$id&size=$size";
        if ($partial===true)
            $url.='&partial=1';

        return $url;
    }


    /**
     * Render the crop ui
     */
    public function displayCropUI()
    {
        $sizes=ilab_get_image_sizes();

        $image_id = esc_html($_GET['post']);
        $size = esc_html($_GET['size']);

        $meta = wp_get_attachment_metadata($image_id);

        $attrs = wp_get_attachment_image_src($image_id, 'full');
        list($full_src,$full_width,$full_height,$full_cropped)=$attrs;
        $orientation=($full_width<$full_height) ? 'landscape' : 'portrait';

        $sizeInfo=$sizes[$size];
        $crop_width=$sizeInfo['width'];
        $crop_height=$sizeInfo['height'];
        $ratio=$crop_width/$crop_height;

        $crop_exists = !empty($meta['sizes'][$size]['file']);
        $crop_attrs = (!$crop_exists) ? null : wp_get_attachment_image_src($image_id, $size);

        $cropped_src=null; $cropped_width=null; $cropped_height=null;
        if ($crop_attrs)
            list($cropped_src,$cropped_width,$cropped_height,$cropped_cropped)=$crop_attrs;

        if (!$cropped_src)
        {
            $forcedCropAttrs = wp_get_attachment_image_src($image_id, $size);
            if ($forcedCropAttrs && (count($forcedCropAttrs) > 2))
            {
                $cropped_src = $forcedCropAttrs[0];
                $cropped_width = $forcedCropAttrs[1];
                $cropped_height = $forcedCropAttrs[2];
            }
        }

        $partial = isset($_GET['partial']) && ($_GET['partial'] == '1');

        $prev_crop_x=null;
        $prev_crop_y=null;
        $prev_crop_w=null;
        $prev_crop_h=null;

        if (isset($meta['sizes'][$size]['crop']))
        {
            $prev_crop_x=$meta['sizes'][$size]['crop']['x'];
            $prev_crop_y=$meta['sizes'][$size]['crop']['y'];
            $prev_crop_w=$meta['sizes'][$size]['crop']['w'];
            $prev_crop_h=$meta['sizes'][$size]['crop']['h'];
        }

        $data=[
            'partial'=>$partial,
            'image_id'=>$image_id,
            'modal_id'=>gen_uuid(8),
            'size'=>$size,
            'sizes'=>$sizes,
            'meta'=>$meta,
            'full_width'=>$full_width,
            'full_height'=>$full_height,
            'orientation'=>$orientation,
            'crop_width'=>$crop_width,
            'crop_height'=>$crop_height,
            'crop_exists'=>($cropped_src!=null),
            'crop_attrs'=>$crop_attrs,
            'ratio'=>$ratio,
            'tool'=>$this,
            'size'=>$size,
            'cropped_src'=>$cropped_src,
            'cropped_width'=>$cropped_width,
            'cropped_height'=>$cropped_height,
            'prev_crop_x' => $prev_crop_x,
            'prev_crop_y' => $prev_crop_y,
            'prev_crop_width' => $prev_crop_w,
            'prev_crop_height' => $prev_crop_h,
            'src'=>$full_src
        ];

        if (current_user_can( 'edit_post', $image_id))
        {
            if (!$partial)
                echo ILabMediaToolView::render_view('crop/ilab-crop-ui.php', $data);
            else
            {
                json_response([
                                  'status'=>'ok',
                                  'image_id'=>$image_id,
                                  'size'=>$size,
                                  'size_title'=>(ucwords(str_replace('-', ' ', $size))),
                                  'min_width'=>$crop_width,
                                  'min_height'=>$crop_height,
                                  'aspect_ratio'=>$ratio,
                                  'prev_crop_x'=>($prev_crop_x!==null) ? (int)$prev_crop_x : null,
                                  'prev_crop_y'=>($prev_crop_y!==null) ? (int)$prev_crop_y : null,
                                  'prev_crop_width'=>($prev_crop_w!==null) ? (int)$prev_crop_w : null,
                                  'prev_crop_height'=>($prev_crop_h!==null) ? (int)$prev_crop_h : null,
                                  'cropped_src'=>$cropped_src,
                                  'cropped_width'=>$cropped_width,
                                  'cropped_height'=>$cropped_height
                              ]);
            }
        }

        die;
    }

    /**
     * Perform the actual crop
     */
    public function performCrop()
    {
        $req_post = esc_html( $_POST['post'] );
        if (!current_user_can('edit_post', $req_post))
            json_response([
                'status'=>'error',
                'message'=>'You are not strong enough, smart enough or fast enough.'
                          ]);


        $size = esc_html($_POST['size']);
        $crop_width = esc_html($_POST['width']);
        $crop_height = esc_html($_POST['height']);
        $crop_x = esc_html($_POST['x']);
        $crop_y = esc_html($_POST['y']);

        $img_path = _load_image_to_edit_path( $req_post );
        $meta = wp_get_attachment_metadata( $req_post );
        $img_editor = wp_get_image_editor( $img_path );
        if (is_wp_error($img_editor))
            json_response([
                              'status'=>'error',
                              'message'=>'Could not create image editor.'
                          ]);

        $crop_size=ilab_get_image_sizes($size);
        $dest_width = $crop_size['width'];
        $dest_height = $crop_size['height'];

        $img_editor->crop($crop_x, $crop_y, $crop_width, $crop_height, $dest_width, $dest_height, false );
        $img_editor->set_quality(get_option('ilab-media-crop-quality',92));
        $save_path_parts = pathinfo($img_path);

        $path_url=parse_url($img_path);
        if (($path_url!==false) && (isset($path_url['scheme'])))
        {
            $parsed_path=pathinfo($path_url['path']);
            $img_subpath=apply_filters('ilab-s3-process-file-name',$parsed_path['dirname']);

            $upload_dir=wp_upload_dir();
            $save_path=$upload_dir['basedir'].$img_subpath;
            @mkdir($save_path,0777,true);
        }
        else
            $save_path=$save_path_parts['dirname'];

        $extension=$save_path_parts['extension'];
        $filename=preg_replace('#^(IL[0-9-]*)#','',$save_path_parts['filename']);
        $filename='IL'.date("YmdHis").'-'.$filename.'-'.$dest_width.'x'.$dest_height.'.'.$extension;

        if (isset($meta['sizes'][$size]))
        {
            $meta['sizes'][$size]['file']=$filename;
            $meta['sizes'][$size]['width']=$dest_width;
            $meta['sizes'][$size]['height']=$dest_height;
            $meta['sizes'][$size]['crop']=[
                'x'=>round($crop_x),
                'y'=>round($crop_y),
                'w'=>round($crop_width),
                'h'=>round($crop_height)
            ];
        }
        else
        {
            $meta['sizes'][$size] = array(
                'file' => $filename,
                'width' => $dest_width,
                'height' => $dest_height,
                'mime-type' => $meta['sizes']['thumbnail']['mime-type'],
                'crop'=> [
                    'x'=>round($crop_x),
                    'y'=>round($crop_y),
                    'w'=>round($crop_width),
                    'h'=>round($crop_height)
                ]
            );
        }

        $img_editor->save($save_path . '/' . $filename);

        // Let S3 upload the new crop
        $processedSize = apply_filters('ilab-s3-process-crop', $size, $filename, $meta['sizes'][$size]);
        if ($processedSize)
            $meta['sizes'][$size] = $processedSize;

        wp_update_attachment_metadata($req_post, $meta);

        $attrs = wp_get_attachment_image_src($req_post, $size);
        list($full_src,$full_width,$full_height,$full_cropped)=$attrs;

        json_response([
            'status'=>'ok',
            'src'=>$full_src,
            'width'=>$full_width,
            'height'=>$full_height
        ]);
    }
}