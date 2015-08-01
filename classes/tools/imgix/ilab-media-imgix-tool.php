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
}