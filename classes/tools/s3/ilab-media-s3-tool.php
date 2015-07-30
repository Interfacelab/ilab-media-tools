<?php
require_once(ILAB_VENDOR_DIR.'/autoload.php');
require_once(ILAB_CLASSES_DIR.'/ilab-media-tool-base.php');
require_once('ilab-media-s3-credentials-provider.php');

class ILabMediaS3Tool extends ILabMediaToolBase {

    private $bucket;

    public function __construct($toolInfo, $toolManager)
    {
        parent::__construct($toolInfo, $toolManager);

        $this->bucket=get_option('ilab-media-s3-bucket', getenv('ILAB_AWS_S3_BUCKET'));

        add_filter('wp_update_attachment_metadata', [$this,'updateAttachmentMetadata'], 31337, 2 );
        add_filter('delete_attachment', [$this,'deleteAttachment'], 20 );
        add_filter('wp_get_attachment_url', [$this, 'getAttachmentURL'], 31337, 2 );
    }

    private function s3Client($insure_bucket=false)
    {
        $region=get_option('ilab-media-s3-region', getenv('ILAB_AWS_S3_REGION'));

        if ($region && $this->bucket)
        {
            $s3=new Aws\S3\S3Client([
                                        'version' => 'latest',
                                        'region'  => $region,
                                        'credentials' => ILabMediaS3CredentialsProvider::ilab()
                                    ]);

            if ($insure_bucket && (!$s3->doesBucketExist($this->bucket)))
                return null;

            return $s3;
        }
        else
            return null;
    }

    /**
     * Filter for when attachments are updated
     *
     * @param $data
     * @param $id
     * @return mixed
     */
    public function updateAttachmentMetadata($data,$id)
    {
        $s3=$this->s3Client(true);
        if ($s3)
        {
            $upload_info=wp_upload_dir();
            $upload_path=$upload_info['basedir'];
            $path_base=pathinfo($data['file'])['dirname'];

            $data=$this->process_file($s3,$upload_path,$data['file'],$data);

            foreach($data['sizes'] as $key => $size)
            {
                $file=$path_base.'/'.$size['file'];
                $data['sizes'][$key]=$this->process_file($s3,$upload_path,$file,$size);
            }
        }

        error_log('updateAttachmentMetadata - '.json_encode($data,JSON_PRETTY_PRINT));
        error_log('updateAttachmentMetadata - '.$id);

        return $data;
    }

    private function process_file($s3,$upload_path,$filename,$data)
    {
        if (!file_exists($upload_path.'/'.$filename))
            return $data;

        if (isset($data['s3']))
        {
            $key = $data['s3']['key'];

            if ($key == $filename)
                return $data;

            $this->delete_file($s3,$key);
        }

        $file=fopen($upload_path.'/'.$filename,'r');
        try
        {
            $s3->upload($this->bucket,$filename,$file,'public-read');
            $data['s3']=[
              'bucket'=>$this->bucket,
              'key'=>$filename
            ];
        }
        catch (\Aws\Exception\AwsException $ex)
        {
            error_log($ex->getMessage());
        }

        return $data;
    }

    /**
     * Filters for when attachments are deleted
     * @param $id
     * @return mixed
     */
    public function deleteAttachment($id)
    {
        $s3=$this->s3Client(true);
        if ($s3)
        {
            $data=wp_get_attachment_metadata($id);

            $path_base=pathinfo($data['file'])['dirname'];

            $this->delete_file($s3,$data['file']);

            foreach($data['sizes'] as $key => $size)
            {
                $file=$path_base.'/'.$size['file'];
                $this->delete_file($s3,$file);
            }
        }

        error_log('deleteAttachment - '.$id);
        return $id;
    }

    private function delete_file($s3,$file)
    {
        try
        {
            if ($s3->doesObjectExist($this->bucket,$file))
            {
                $s3->deleteObject(array(
                                      'Bucket' => $this->bucket,
                                      'Key'    => $file
                                  ));
            }
        }
        catch (\Aws\Exception\AwsException $ex)
        {
            error_log($ex->getMessage());
        }
    }

    public function getAttachmentURL($url, $post_id)
    {
        $meta=wp_get_attachment_metadata($post_id);
        if (isset($meta['s3']))
        {
            $cdn=get_option('ilab-media-s3-cdn-base', getenv('ILAB_AWS_S3_CDN_BASE'));
            return $cdn.'/'.$meta['file'];
        }

        return $url;
    }
}