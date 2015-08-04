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

    private function buildImgixParams($params)
    {
        if ($this->autoFormat)
            $auto=['format'];
        else
            $auto=[];

        if (isset($params['enhance']))
            $auto[]='enhance';

        if (isset($params['redeye']))
            $auto[]='redeye';

        unset($params['enhance']);
        unset($params['redeye']);

        $params['auto']=implode(',',$auto);

        if ($this->imageQuality)
            $params['q']=$this->imageQuality;

        if (isset($params['media']))
        {
            $media_id=$params['media'];
            unset($params['media']);
            $markMeta=wp_get_attachment_metadata($media_id);
            $params['mark']='/'.$markMeta['file'];
        }

        if (isset($params['mark']))
        {
            if (($params['mark']=='/') || ($params['mark']=='') || (isset($params['markscale']) && ($params['markscale']==0)))
            {
                unset($params['mark']);
                unset($params['markalign']);
                unset($params['markalpha']);
                unset($params['markscale']);
            }
        }
        else
        {
            unset($params['mark']);
            unset($params['markalign']);
            unset($params['markalpha']);
            unset($params['markscale']);
        }

        if (isset($params['border-width']) && isset($params['border-color']))
        {
            $params['border']=$params['border-width'].','.str_replace('#','',$params['border-color']);
        }

        unset($params['border-width']);
        unset($params['border-color']);

        if (isset($params['padding-width']))
        {
            $params['pad']=$params['padding-width'];

            if (isset($params['padding-color']))
                $params['bg']=$params['padding-color'];
        }

        unset($params['padding-width']);
        unset($params['padding-color']);

        return $params;
    }

    private function buildImgixImage($id,$size, $params=null, $skipParams=false)
    {
        if (is_array($size))
            return false;

        $meta=wp_get_attachment_metadata($id);

        $imgix=new Imgix\UrlBuilder($this->imgixDomains);

        if ($this->signingKey)
            $imgix->setSignKey($this->signingKey);

        if ($size=='full')
        {
            if (!$params)
            {
                if (isset($meta['imgix-params']))
                    $params=$meta['imgix-params'];
                else
                    $params=[];
            }

            $params=$this->buildImgixParams($params);

            $result=[
                $imgix->createURL($meta['file'],($skipParams) ? [] : $params),
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

        $metaSize=null;
        if (isset($meta['sizes'][$size]))
        {
            $metaSize = $meta['sizes'][$size];
        }

        if (!$params)
        {
            if (isset($meta['imgix-size-params'][$size]))
            {
                $params=$meta['imgix-size-params'][$size];
            }
            else if (isset($meta['imgix-size-presets'][$size]))
            {
                $preset=$meta['imgix-size-presets'][$size];
                $presets=get_option('ilab-imgix-presets');
                if ($presets && isset($presets[$preset]))
                    $params=$presets[$preset];
            }

            if ((!$params) && (isset($meta['imgix-params'])))
                $params=$meta['imgix-params'];
            else if (!$params)
                $params=[];
        }

        if ($sizeInfo['crop'])
        {
            $params['w']=$sizeInfo['width'];
            $params['h']=$sizeInfo['height'];
            $params['fit']='crop';

            if ($metaSize)
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

        $params=$this->buildImgixParams($params);

        $result=[
            $imgix->createURL($meta['file'],$params),
            $params['w'],
            $params['h'],
            false
        ];

        return $result;
    }

    public function imageDownsize($fail,$id,$size)
    {
        $result=$this->buildImgixImage($id,$size);

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
    public function editPageURL($id, $size = 'full', $partial=false)
    {
        $url=admin_url('admin-ajax.php')."?action=ilab_imgix_edit_page&post=$id";

        if ($size!='full')
            $url.="&size=$size";

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

        $partial=(isset($_GET['partial']) && ($_GET['partial']==1));

        if (isset($_GET['size']))
            $size=esc_html($_GET['size']);
        else
            $size='full';

        $meta = wp_get_attachment_metadata($image_id);

        $attrs = wp_get_attachment_image_src($image_id, $size);
        list($full_src,$full_width,$full_height,$full_cropped)=$attrs;

        $imgix_settings=[];

        if ($size=='full')
        {
            if (!$params)
            {
                if (isset($meta['imgix-params']))
                    $imgix_settings=$meta['imgix-params'];
            }
        }
        else
        {
            if (isset($meta['imgix-size-params'][$size]))
            {
                $imgix_settings=$meta['imgix-size-params'][$size];
            }
            else if (isset($meta['imgix-size-presets'][$size]))
            {
                $preset=$meta['imgix-size-presets'][$size];
                $presets=get_option('ilab-imgix-presets');
                if ($presets && isset($presets[$preset]))
                    $imgix_settings=$presets[$preset];
            }

            if ((!$imgix_settings) && (isset($meta['imgix-params'])))
                $imgix_settings=$meta['imgix-params'];
        }

        $data=[
            'partial'=>$partial,
            'image_id'=>$image_id,
            'size'=>$size,
            'sizes'=>ilab_get_image_sizes(),
            'meta'=>$meta,
            'full_width'=>$full_width,
            'full_height'=>$full_height,
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
     * Save The Parameters
     */
    public function saveAdjustments()
    {
        $image_id = esc_html( $_POST['image_id'] );
        $size=esc_html($_POST['size']);
        $params=(isset($_POST['settings'])) ? $_POST['settings'] : [];

        if (!current_user_can('edit_post', $image_id))
            json_response([
                              'status'=>'error',
                              'message'=>'You are not strong enough, smart enough or fast enough.'
                          ]);


        $meta = wp_get_attachment_metadata( $image_id );
        if (!$meta)
            json_response([
                              'status'=>'error',
                              'message'=>'Invalid image id.'
                          ]);

        if ($size=='full')
        {
            $meta['imgix-params']=$params;
        }
        else
        {
            $meta['imgix-size-params'][$size]=$params;
        }

        wp_update_attachment_metadata($image_id, $meta);

        json_response([
                          'status'=>'ok'
                      ]);
    }

    public function previewAdjustments()
    {
        $image_id = esc_html( $_POST['image_id'] );
        $size=esc_html($_POST['size']);

        if (!current_user_can('edit_post', $image_id))
            json_response([
                              'status'=>'error',
                              'message'=>'You are not strong enough, smart enough or fast enough.'
                          ]);


        $params=(isset($_POST['settings'])) ? $_POST['settings'] : [];
        $result=$this->buildImgixImage($image_id,$size,$params);

        json_response(['status'=>'ok','src'=>$result[0]]);
    }
}