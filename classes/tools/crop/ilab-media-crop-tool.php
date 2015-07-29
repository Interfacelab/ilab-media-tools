<?php
class ILabMediaCropTool
{
    public function __construct()
    {

        $this->hookup_ui();

        add_action('admin_enqueue_scripts', [$this,'enqueueTheGoods']);
        add_action('wp_ajax_ilab_crop_image_page',[$this,'displayCropUI']);
        add_action('wp_ajax_ilab_perform_crop',[$this,'performCrop']);
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
        wp_enqueue_style ( 'ilab-crop-css', ILAB_PUB_CSS_URL . '/ilab-crop.css' );
        wp_enqueue_script( 'wp-pointer' );
        wp_enqueue_script ( 'ilab-crop-js', ILAB_PUB_JS_URL. '/ilab-crop.js', ['jquery'], false, true );
    }

    /**
     * Hook up the "Crop Image" links/buttons in the admin ui
     */
    private function hookup_ui()
    {
        // TODO: Still need to hook up the edit attachment page

        add_filter('media_row_actions',function($actions,$post){
            $newaction['ilab_crop_image'] = '<a class="thickbox" href="'.$this->crop_page_url($post->ID).'" title="Crop Image" rel="permalink">' . __('Crop Image') . '</a>';
            return array_merge($actions,$newaction);
        },10,2);

        add_filter('admin_post_thumbnail_html', function($content,$id){
            $content.='<a href="'.$this->crop_page_url($id).'">'.__('Crop Image').'</a>';
            return $content;
        },10,2);

        add_action( 'wp_enqueue_media', function () {
            remove_action('admin_footer', 'wp_print_media_templates');

            add_action('admin_footer', function(){
                ob_start();
                wp_print_media_templates();
                $result=ob_get_clean();
                echo $result;

                ?>
                <script>
                    jQuery(document).ready(function() {
                        attachTemplate=jQuery('#tmpl-image-details');
                        if (attachTemplate)
                            attachTemplate.text(attachTemplate.text().replace(/(<input type="button" class="replace-attachment button")/,'<a href="<?php echo $this->crop_page_url('{{data.attachment.id}}')?>" class="ilab-thickbox button"><?php echo __('Crop Image') ?></a>&nbsp;$1'));

                        attachTemplate=jQuery('#tmpl-attachment-details-two-column');
                        if (attachTemplate)
                        {
                            attachTemplate.text(attachTemplate.text().replace(/(<a class="(?:.*)edit-attachment(?:.*)"[^>]+[^<]+<\/a>)/,'$1&nbsp;<a href="<?php echo $this->crop_page_url('{{data.id}}')?>" class="ilab-thickbox button"><?php echo __('Crop Image') ?></a>'));
                            attachTemplate.text(attachTemplate.text().replace(/(<a class="(?:.*)view-attachment(?:.*)"[^>]+[^<]+<\/a>)/,'$1 | <a class="ilab-thickbox" href="<?php echo $this->crop_page_url('{{data.id}}')?>"><?php echo __('Crop Image') ?></a>'));
                        }

                        attachTemplate=jQuery('#tmpl-attachment-details');
                        if (attachTemplate)
                            attachTemplate.text(attachTemplate.text().replace(/(<a class="(?:.*)edit-attachment(?:.*)"[^>]+[^<]+<\/a>)/,'$1\n<a class="ilab-thickbox edit-attachment" href="<?php echo $this->crop_page_url('{{data.id}}')?>"><?php echo __('Crop Image') ?></a>'));
                    });
                </script>
                <?php
            } );
        } );
    }

    /**
     * Generate the url for the crop UI
     * @param $id
     * @param string $size
     * @return string
     */
    public function crop_page_url($id, $size = 'thumbnail', $partial=false)
    {
        $url=admin_url('admin-ajax.php')."?action=ilab_crop_image_page&post=$id&size=$size";
        if ($partial===true)
            $url.='&partial=1';

        return $url;
    }

    private function get_image_sizes()
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

        return $sizes;
    }

    /**
     * Render the crop ui
     */
    public function displayCropUI()
    {
        $sizes=$this->get_image_sizes();

        $image_id = esc_html($_GET['post']);
        $size = esc_html($_GET['size']);

        $meta = wp_get_attachment_metadata($image_id);

        $attrs = wp_get_attachment_image_src($image_id, 'full');
        $full_width=$attrs[1];
        $full_height=$attrs[2];
        $orientation=($full_width<$full_height) ? 'landscape' : 'portrait';

        $sizeInfo=$sizes[$size];
        $crop_width=$sizeInfo['width'];
        $crop_height=$sizeInfo['height'];
        $ratio=$crop_width/$crop_height;


        $partial = isset($_GET['partial']) && ($_GET['partial'] == '1');
        $data=[
            'partial'=>$partial,
            'image_id'=>$image_id,
            'sizes'=>$sizes,
            'meta'=>$meta,
            'full_width'=>$full_width,
            'full_height'=>$full_height,
            'orientation'=>$orientation,
            'crop_width'=>$crop_width,
            'crop_height'=>$crop_height,
            'ratio'=>$ratio,
            'tool'=>$this,
            'size'=>$size
        ];

        if (current_user_can( 'edit_post', $image_id))
            echo render_view('ilab-crop-ui.php', $data);

        die;
    }

    /**
     * Perform the actual crop
     */
    public function performCrop()
    {

    }
}