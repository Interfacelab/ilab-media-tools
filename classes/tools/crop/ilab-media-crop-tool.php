<?php
class ILabMediaCropTool
{
    public function __construct()
    {
        add_filter('media_row_actions',function($actions,$post){
            $newaction['crop_iamge'] = '<a href="" title="Crop Image" rel="permalink">' . __('Crop Image') . '</a>';
            return array_merge($actions,$newaction);
        },10,2);

        add_filter( 'admin_post_thumbnail_html', function($content,$id){
            $content.='<a>hello</a>';
            return $content;
        }, 10, 2 );

        add_action( 'wp_enqueue_media', function () {
            if ( ! remove_action( 'admin_footer', 'wp_print_media_templates' ) ) {
                error_log("remove_action fail");
            }
            add_action( 'admin_footer', function(){
                ob_start();
                wp_print_media_templates();
                $result=ob_get_clean();
                echo $result;

?>
                <script>
                    jQuery(document).ready(function() {
                        attachTemplate= jQuery('#tmpl-image-details');
                        attachTemplate.text(attachTemplate.text().replace(/(<input type="button" class="replace-attachment button")/,'<a href="<?php echo $this->crop_page_url('{{data.attachment.id}}')?>" class="button">Crop Image</a>&nbsp;$1'));

                        attachTemplate=jQuery('#tmpl-attachment-details-two-column');
                        attachTemplate.text(attachTemplate.text().replace(/(<a class="(?:.*)edit-attachment(?:.*)"[^>]+[^<]+<\/a>)/,'$1&nbsp;<a href="<?php echo $this->crop_page_url('{{data.id}}')?>" class="button">Crop Image</a>'));
                        attachTemplate.text(attachTemplate.text().replace(/(<a class="(?:.*)view-attachment(?:.*)"[^>]+[^<]+<\/a>)/,'$1 | <a href="<?php echo $this->crop_page_url('{{data.id}}')?>">Crop Image</a>'));

                        attachTemplate=jQuery('#tmpl-attachment-details');
                        attachTemplate.text(attachTemplate.text().replace(/(<a class="(?:.*)edit-attachment(?:.*)"[^>]+[^<]+<\/a>)/,'$1\n<a class="edit-attachment" href="<?php echo $this->crop_page_url('{{data.id}}')?>">Crop Image</a>'));
                    });
                </script>
<?php
            } );
        } );

    }

    private function crop_page_url($id, $size = 'thumbnail')
    {
        return admin_url( 'admin-ajax.php' ) . '?action=ilab_crop_image_page&post=' . $id . '&size=' . $size;
    }
}