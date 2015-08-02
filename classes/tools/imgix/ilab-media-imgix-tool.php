<?php
require_once(ILAB_CLASSES_DIR.'/ilab-media-tool-base.php');
require_once(ILAB_VENDOR_DIR.'/autoload.php');

class ILabMediaImgixTool extends ILabMediaToolBase
{
    protected $imgixDomains;
    protected $signingKey;
    protected $imageQuality;
    protected $autoFormat;

    public function __construct($toolName, $toolInfo, $toolManager)
    {
        parent::__construct($toolName, $toolInfo, $toolManager);
    }

    public function enabled()
    {
        $enabled=parent::enabled();

        if (!get_option('ilab-media-imgix-domains'))
        {
            $this->displayAdminNotice('error',"To start using Imgix, you will need to <a href='admin.php?page=media-tools-imgix'>set it up</a>.");
            return false;
        }

        return $enabled;
    }

    public function setup()
    {
        if (!$this->enabled())
            return;

        $this->imgixDomains=[];
        $domains=get_option('ilab-media-imgix-domains');
        $domain_lines=explode("\n",$domains);
        foreach($domain_lines as $d)
            if (!empty($d))
                $this->imgixDomains[]=$d;

        $this->signingKey=get_option('ilab-media-imgix-signing-key');

        $this->imageQuality=get_option('ilab-media-imgix-default-quality');
        $this->autoFormat=get_option('ilab-media-imgix-auto-format');

        add_filter('wp_get_attachment_url', [$this, 'getAttachmentURL'], 1000, 2 );
        add_filter('image_downsize', [$this, 'imageDownsize'], 1000, 3 );

        $this->hookupUI();

        add_action('admin_enqueue_scripts', [$this,'enqueueTheGoods']);
        add_action('wp_ajax_ilab_imgix_edit_page',[$this,'displayEditUI']);
        add_action('wp_ajax_ilab_imgix_save',[$this,'saveAdjustments']);
        add_action('wp_ajax_ilab_imgix_preview',[$this,'previewAdjustments']);
    }

    public function getAttachmentURL($url, $post_id)
    {
        //error_log('getAttachmentURL - '.$url);
        return $url;
    }

    public function imageDownsize($fail,$id,$size)
    {
        if (is_array($size))
            return false;

        $meta=wp_get_attachment_metadata($id);

        $imgix=new Imgix\UrlBuilder($this->imgixDomains);

        if ($this->signingKey)
            $imgix->setSignKey($this->signingKey);

        if ($size=='full')
        {
            $result=[
                $imgix->createURL($meta['file']),
                $meta['width'],
                $meta['height'],
                false
            ];
            error_log('imageDownsize - '.json_encode($result, JSON_PRETTY_PRINT));
            return $result;
        }

        $sizeInfo=ilab_get_image_sizes($size);
        if (!$sizeInfo)
            return false;

        $params=[];

        if ($this->autoFormat)
            $params['auto']='format';
        if ($this->imageQuality)
            $params['q']=$this->imageQuality;

        if ($sizeInfo['crop'])
        {
            $params['w']=$sizeInfo['width'];
            $params['h']=$sizeInfo['height'];
            $params['fit']='crop';

            if (isset($meta['sizes'][$size]))
            {
                $metaSize=$meta['sizes'][$size];
                if (isset($metaSize['crop']))
                {
                    $metaSize['crop']['x']=round($metaSize['crop']['x']);
                    $metaSize['crop']['y']=round($metaSize['crop']['y']);
                    $metaSize['crop']['w']=round($metaSize['crop']['w']);
                    $metaSize['crop']['h']=round($metaSize['crop']['h']);
                    $params['rect']=implode(',',$metaSize['crop']);
                }
            }
        }
        else
        {
            $newSize=sizeToFitSize($meta['width'],$meta['height'],$sizeInfo['width'],$sizeInfo['height']);
            $params['w']=$newSize[0];
            $params['h']=$newSize[1];
            $params['fit']='scale';
        }

        $result=[
            $imgix->createURL($meta['file'],$params),
            $params['w'],
            $params['h'],
            false
        ];

        error_log('imageDownsize - '.json_encode($result, JSON_PRETTY_PRINT));
        return $result;
    }


    /**
     * Enqueue the CSS and JS needed to make the magic happen
     * @param $hook
     */
    public function enqueueTheGoods($hook)
    {
        add_thickbox();

        if ($hook == 'post.php')
            wp_enqueue_media();
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

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_style('wp-pointer');
        wp_enqueue_style('ilab-modal-css', ILAB_PUB_CSS_URL . '/ilab-modal.css' );
        wp_enqueue_style('ilab-imgix-css', ILAB_PUB_CSS_URL . '/ilab-imgix.css' );
        wp_enqueue_script('wp-pointer');
        wp_enqueue_script('ilab-modal-js', ILAB_PUB_JS_URL. '/ilab-modal.js', ['jquery','wp-color-picker'], false, true );
        wp_enqueue_script('ilab-imgix-js', ILAB_PUB_JS_URL. '/ilab-imgix.js', ['ilab-modal-js'], false, true );
    }

    /**
     * Hook up the "Edit Image" links/buttons in the admin ui
     */
    private function hookupUI()
    {

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
                        attachTemplate=jQuery('#tmpl-attachment-details-two-column');
                        if (attachTemplate)
                        {
                            attachTemplate.text(attachTemplate.text().replace(/(<a class="(?:.*)edit-attachment(?:.*)"[^>]+[^<]+<\/a>)/,'<a href="<?php echo $this->editPageURL('{{data.id}}')?>" class="ilab-thickbox button edit-imgix"><?php echo __('Edit Image') ?></a>'));
                        }

                        attachTemplate=jQuery('#tmpl-attachment-details');
                        if (attachTemplate)
                            attachTemplate.text(attachTemplate.text().replace(/(<a class="(?:.*)edit-attachment(?:.*)"[^>]+[^<]+<\/a>)/,'<a class="ilab-thickbox edit-imgix" href="<?php echo $this->editPageURL('{{data.id}}')?>"><?php echo __('Edit Image') ?></a>'));
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
    public function editPageURL($id, $size = 'thumbnail', $partial=false)
    {
        $url=admin_url('admin-ajax.php')."?action=ilab_imgix_edit_page&post=$id";
        if ($partial===true)
            $url.='&partial=1';

        return $url;
    }


    /**
     * Render the edit ui
     */
    public function displayEditUI()
    {
        $image_id = esc_html($_GET['post']);

        $meta = wp_get_attachment_metadata($image_id);

        $attrs = wp_get_attachment_image_src($image_id, 'full');
        list($full_src,$full_width,$full_height,$full_cropped)=$attrs;
        $orientation=($full_width<$full_height) ? 'landscape' : 'portrait';

        $imgix_settings=(isset($meta['imgix-settings']) ? $meta['imgix-settings'] : []);

        $data=[
            'image_id'=>$image_id,
            'meta'=>$meta,
            'full_width'=>$full_width,
            'full_height'=>$full_height,
            'orientation'=>$orientation,
            'tool'=>$this,
            'settings'=>$imgix_settings,
            'src'=>$full_src,
            'params'=>$this->toolInfo['settings']['params']
        ];

        if (current_user_can( 'edit_post', $image_id))
            echo \ILab\Stem\View::render_view('imgix/ilab-imgix-ui.php', $data);

        die;
    }

    /**
     * Perform the actual crop
     */
    public function saveAdjustments()
    {
        $req_post = esc_html( $_POST['post'] );
        if (!current_user_can('edit_post', $req_post))
            json_response([
                              'status'=>'error',
                              'message'=>'You are not strong enough, smart enough or fast enough.'
                          ]);


        $meta = wp_get_attachment_metadata( $req_post );
        wp_update_attachment_metadata($req_post, $meta);

        $attrs = wp_get_attachment_image_src($req_post, 'full');
        list($full_src,$full_width,$full_height,$full_cropped)=$attrs;

        json_response([
                          'status'=>'ok',
                          'src'=>$full_src,
                          'width'=>$full_width,
                          'height'=>$full_height
                      ]);
    }

    public function previewAdjustments()
    {
        $image_id = esc_html( $_POST['image_id'] );
        if (!current_user_can('edit_post', $image_id))
            json_response([
                              'status'=>'error',
                              'message'=>'You are not strong enough, smart enough or fast enough.'
                          ]);

        $meta = wp_get_attachment_metadata( $image_id );
        $imgix=new Imgix\UrlBuilder($this->imgixDomains);

        if ($this->signingKey)
            $imgix->setSignKey($this->signingKey);

        $params=(isset($_POST['settings'])) ? $_POST['settings'] : [];

        if (isset($params['media']))
        {
            $media_id=$params['media'];
            unset($params['media']);
            $markMeta=wp_get_attachment_metadata($media_id);
            $params['mark']='/'.$markMeta['file'];
        }


        json_response(['status'=>'ok','src'=>$imgix->createURL($meta['file'],$params)]);
    }
}