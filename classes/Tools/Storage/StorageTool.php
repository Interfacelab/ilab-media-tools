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

namespace ILAB\MediaCloud\Tools\Storage;

use FasterImage\FasterImage;
use ILAB\MediaCloud\Tools\ToolBase;
use ILAB\MediaCloud\Utilities\ToolView;
use ILAB\MediaCloud\Tasks\StorageImportProcess;
use ILAB\MediaCloud\Utilities\Logger;
use ILAB_Aws\Exception\AwsException;
use ILAB_Aws\S3\PostObjectV4;
use ILAB_Aws\S3\S3Client;
use ILAB_Aws\S3\S3MultiRegionClient;
use Smalot\PdfParser\Parser;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * Class ILabMediaS3Tool
 *
 * S3 Tool.
 */
class StorageTool extends ToolBase {
	//region Properties/Class Variables
	private $key = null;
	private $secret = null;
	private $bucket = null;
	private $endpoint = null;
	private $endPointPathStyle = true;
	private $docCdn = null;
	private $cdn = null;
	private $deleteOnUpload = false;
	private $deleteFromS3 = false;
	private $prefixFormat = '';
	private $skipBucketCheck = false;
	private $region = false;

	private $settingsError = false;

	private $uploadedDocs = [];
	private $pdfInfo = [];

	private $cacheControl = null;
	private $expires = null;
	private $versionedIds = [];

	private $ignoredMimeTypes = [];
	private $uploadDocs = true;

	private $privacy = 'public-read';

	private $skipUpdate = false;

	private $displayBadges = true;

	private $useTransferAcceleration = false;
	//endregion


    //region Constructor
	public function __construct($toolName, $toolInfo, $toolManager)
	{
		parent::__construct($toolName, $toolInfo, $toolManager);

		new StorageImportProcess();

		$this->bucket = $this->getOption('ilab-media-s3-bucket', 'ILAB_AWS_S3_BUCKET');
		$this->key = $this->getOption('ilab-media-s3-access-key', 'ILAB_AWS_S3_ACCESS_KEY');
		$this->secret = $this->getOption('ilab-media-s3-secret', 'ILAB_AWS_S3_ACCESS_SECRET');
		$this->endpoint = $this->getOption('ilab-media-s3-endpoint', 'ILAB_AWS_S3_ENDPOINT');
		$this->deleteOnUpload = $this->getOption('ilab-media-s3-delete-uploads');
		$this->deleteFromS3 = $this->getOption('ilab-media-s3-delete-from-s3');
		$this->prefixFormat = $this->getOption('ilab-media-s3-prefix', '');
		$this->uploadDocs = $this->getOption('ilab-media-s3-upload-documents', null, true);
		$this->displayBadges = $this->getOption('ilab-media-s3-display-s3-badge', null, true);
		$this->privacy = $this->getOption('ilab-media-s3-privacy', null, "public-read");
		$this->useTransferAcceleration = $this->getOption('ilab-media-s3-use-transfer-acceleration','ILAB_AWS_S3_TRANSFER_ACCELERATION', false);
		$this->endPointPathStyle = $this->getOption('ilab-media-s3-use-path-style-endpoint', 'ILAB_AWS_S3_ENDPOINT_PATH_STYLE', true);

		$region = $this->getOption('ilab-media-s3-region', 'ILAB_AWS_S3_REGION', 'auto');
		if ($region != 'auto'){
		    $this->region = $region;
        }

		if (!in_array($this->privacy, ['public-read', 'authenticated-read'])) {
			$this->displayAdminNotice('error', "Your AWS S3 settings are incorrect.  The ACL '{$this->privacy}' is not valid.  Defaulting to 'public-read'.");
			$this->privacy = 'public-read';
		}

		$ignored = $this->getOption('ilab-media-s3-ignored-mime-types',null,'');
		$ignored_lines = explode("\n",$ignored);
		if (count($ignored_lines)<=1) {
			$ignored_lines = explode(',', $ignored);
		}
		foreach($ignored_lines as $d) {
			if (!empty($d)) {
				$this->ignoredMimeTypes[]=trim($d);
			}
		}

		$this->cdn = $this->getOption('ilab-media-s3-cdn-base', 'ILAB_AWS_S3_CDN_BASE');
		if ($this->cdn) {
			$this->cdn=rtrim($this->cdn,'/');
		}

		$this->docCdn = $this->getOption('ilab-doc-s3-cdn-base', 'ILAB_AWS_S3_DOC_CDN_BASE', $this->cdn);

		$this->settingsError = get_option('ilab-s3-settings-error', false);

		$this->cacheControl = $this->getOption('ilab-media-s3-cache-control', 'ILAB_AWS_S3_CACHE_CONTROL');

		$expires = $this->getOption('ilab-media-s3-expires', 'ILAB_AWS_S3_EXPIRES');
		if (!empty($expires)) {
			$this->expires = gmdate('D, d M Y H:i:s \G\M\T', time() + ($expires * 60));
		}

		$this->skipBucketCheck = $this->getOption('ilab-media-s3-skip-bucket-check', 'ILAB_AWS_S3_SKIP_BUCKET_CHECK');

		if ($this->haveSettingsChanged()) {
			$this->settingsChanged();
		}

		if ($this->settingsError) {
			$this->displayAdminNotice('error', 'Your AWS S3 settings are incorrect or the bucket does not exist.  Please verify your settings and update them.');
		}

		if (is_admin()) {
			add_action('wp_ajax_ilab_s3_import_media', [$this,'importMedia']);
			add_action('wp_ajax_ilab_s3_import_progress', [$this,'importProgress']);
			add_action('wp_ajax_ilab_s3_cancel_import', [$this,'cancelImportMedia']);
		}

		$this->hookMediaGrid();
	}
    //endregion



	public function enabled()
	{
		$enabled = $this->s3enabled();

		if (!$enabled)
			return false;

		return parent::enabled();
	}

	public function setup()
	{
		parent::setup();

		if ($this->enabled()) {
			add_filter('wp_update_attachment_metadata', [$this, 'updateAttachmentMetadata'], 1000, 2);
			add_filter('delete_attachment', [$this, 'deleteAttachment'], 1000);
			add_filter('wp_handle_upload', [$this, 'handleUpload'], 10000);
			add_filter('ilab_s3_process_crop', [$this, 'processCrop'], 10000, 4);
			add_filter('get_attached_file', [$this, 'getAttachedFile'], 10000, 2);
			add_filter('image_downsize', [$this, 'imageDownsize'], 999, 3 );
			add_filter('wp_prepare_attachment_for_js', [$this, 'prepareAttachmentForJS'], 999, 3);

			add_filter('ilab_s3_process_file_name', function($filename) {
				if (strpos($filename,'/'.$this->bucket) === 0)
					return str_replace('/'.$this->bucket, '', $filename);

				return $filename;
			}, 10000, 1);
			add_action('add_attachment',function($post_id){
				$file = get_post_meta( $post_id, '_wp_attached_file', true );
				if (isset($this->uploadedDocs[$file])) {
					add_post_meta($post_id, 'ilab_s3_info', $this->uploadedDocs[$file]);
					do_action('ilab_s3_uploaded_attachment', $post_id, $file, $this->uploadedDocs[$file]);
				}
			}, 1000);

			add_filter('wp_calculate_image_srcset',[$this,'calculateSrcSet'], 10000, 5);
		}

		add_filter('wp_get_attachment_url', [$this, 'getAttachmentURL'], 1000, 2 );

		$this->hookupUI();
	}

	public function registerMenu($top_menu_slug) {
		parent::registerMenu($top_menu_slug); // TODO: Change the autogenerated stub

		if (!$this->settingsError && $this->enabled()) {
			add_submenu_page( $top_menu_slug, 'Storage Importer', 'Storage Importer', 'manage_options', 'media-tools-s3-importer', [$this,'renderImporter']);
		}
	}

	private function hookupUI() {
		add_action( 'wp_enqueue_media', function () {
			add_action('admin_footer', function(){
				?>
				<script>
                    jQuery(document).ready(function() {
                        var attachTemplate = jQuery('#tmpl-attachment-details-two-column');
                        if (attachTemplate) {
                            var txt = attachTemplate.text();
                            var idx = txt.indexOf('<div class="compat-meta">');
                            txt = txt.slice(0, idx) + '<# if ( data.s3 ) { #><div><strong>Bucket:</strong> {{data.s3.bucket}}</div><div><strong>Path:</strong> {{data.s3.key}}</div><div><strong>Access:</strong> {{data.s3.privacy}}</div><# if ( data.s3.options && data.s3.options.params ) { #><# if (data.s3.options.params.CacheControl) { #><div><strong>S3 Cache-Control:</strong> {{data.s3.options.params.CacheControl}}</div><# } #><# if (data.s3.options.params.Expires) { #><div><strong>S3 Expires:</strong> {{data.s3.options.params.Expires}}</div><# } #><# } #><div><a href="{{data.s3.url}}" target="_blank">Original S3 URL</a></div><# } #>' + txt.slice(idx);
                            attachTemplate.text(txt);
                        }
                    });
				</script>
				<?php

			} );

			add_action('admin_head', function(){

				if (get_current_screen()->base == 'upload') {
					?>
                    <style>
                        th.column-s3, td.column-s3 {
                            width:60px !important;
                            max-width: 60px !important;
                        }
                    </style>
					<?php
				}
            });
		} );

		add_action('admin_init',function(){
			add_meta_box('ilab-s3-info-meta','S3 Info',[$this,'renderS3InfoMeta'], 'attachment', 'side', 'low');

			add_action('edit_attachment', [$this, 'editAttachment']);

			add_filter('manage_media_columns', function($cols) {
			    $cols["s3"] = 'S3';
			    return $cols;
            });

			add_action('manage_media_custom_column', function($column_name, $id) {
                $meta = wp_get_attachment_metadata($id);
                if (!empty($meta) && isset($meta['s3'])) {
	                echo "<a href='".$meta['s3']['url']."' target=_blank>View</a>";
                }
            }, 10, 2);

			if ($this->enabled()) {
				add_filter('bulk_actions-upload', function($actions){
					$actions['ilab_s3_import'] = 'Import to S3';
					return $actions;
				});

				add_filter('handle_bulk_actions-upload', function($redirect_to, $action_name, $post_ids) {
					if ('ilab_s3_import' === $action_name) {
						$posts_to_import = [];
						if (count($post_ids) > 0) {
							foreach($post_ids as $post_id) {
								$meta = wp_get_attachment_metadata($post_id);
								if (!empty($meta) && isset($meta['s3'])) {
									continue;
								}

								$posts_to_import[] = $post_id;
							}
						}

						if (count($posts_to_import) > 0) {
							update_option('ilab_s3_import_status', true);
							update_option('ilab_s3_import_total_count', count($posts_to_import));
							update_option('ilab_s3_import_current', 1);
							update_option('ilab_s3_import_should_cancel', false);

							$process = new StorageImportProcess();

							for($i = 0; $i < count($posts_to_import); ++$i) {
								$process->push_to_queue(['index' => $i, 'post' => $posts_to_import[$i]]);
							}

							$process->save();
							$process->dispatch();

							return 'admin.php?page=media-tools-s3-importer';
						}
					}

					return $redirect_to;
				}, 1000, 3);
			}
		});
	}

	public function editAttachment($post_id) {
		$meta = wp_get_attachment_metadata($post_id);
		if (!isset($meta['s3'])) {
			$this->editDocumentAttachment();
			return;
		}

		$meta = $this->updateAttachmentS3Props($post_id, $meta);
		wp_update_attachment_metadata($post_id, $meta);
	}

	private function editDocumentAttachment() {
		global $post;

		if (empty($post)) {
		    return;
        }

		$meta = get_post_meta($post->ID, 'ilab_s3_info', true);
		if (empty($meta) || !isset($meta['s3'])) {
			return;
		}

		$meta = $this->updateAttachmentS3Props($post->ID, $meta);
		update_post_meta($post->ID, 'ilab_s3_info', $meta);
	}

	private function updateAttachmentS3Props($id, $meta) {
		if (isset($_POST['s3-access-acl']) || isset($_POST['s3-cache-control']) || isset($_POST['s3-expires'])) {
			$mime = get_post_mime_type($id);

			$s3 = $this->s3Client(false);

			$params = [];

			$acl = (isset($meta['s3']['privacy'])) ? $meta['s3']['privacy'] : $this->privacy;
			$acl = (isset($_POST['s3-access-acl'])) ? $_POST['s3-access-acl'] : $acl;
			$meta['s3']['privacy'] = $acl;

			if (isset($_POST['s3-cache-control'])) {
				$cc = $_POST['s3-cache-control'];
				if (!empty($cc)) {
					$params['CacheControl'] = $cc;
				}
			}

			if (isset($_POST['s3-expires'])) {
				$expires = $_POST['s3-expires'];
				if (!empty($expires)) {
					if (!is_numeric($expires)) {
						$expires = strtotime($expires) - time();
						if ($expires !== false) {
							$expires = round($expires / 60);
						}
					}

					if (($expires !== false) && is_numeric($expires)) {
						$expires = gmdate('D, d M Y H:i:00 \G\M\T', time() + ($expires * 60));
						$params['Expires'] = $expires;
					}
				}
			}

			$copyOptions = $params;
			$copyOptions['MetadataDirective'] = 'REPLACE';
			$copyOptions['Bucket'] = $meta['s3']['bucket'];
			$copyOptions['Key'] = $meta['s3']['key'];
			$copyOptions['CopySource'] = $meta['s3']['bucket'].'/'.$meta['s3']['key'];
			$copyOptions['ACL'] = $acl;
			if ($mime) {
				$copyOptions['ContentType'] = $mime;
			}

			try
			{
				$s3->copyObject($copyOptions);

				if (isset($params['CacheControl'])) {
					if (!isset($meta['s3']['options'])) {
						$meta['s3']['options']=[];
					}

					if (!isset($meta['s3']['options']['params'])) {
						$meta['s3']['options']['params']=[];
					}

					$meta['s3']['options']['params']['CacheControl'] = $params['CacheControl'];
				}

				if (isset($params['Expires'])) {
					if (!isset($meta['s3']['options'])) {
						$meta['s3']['options']=[];
					}

					if (!isset($meta['s3']['options']['params'])) {
						$meta['s3']['options']['params']=[];
					}

					$meta['s3']['options']['params']['Expires'] = $params['Expires'];
				}
			}
			catch (AwsException $ex)
			{
			    Logger::error( 'S3 Error Copying Object', [ 'exception' =>$ex->getMessage(), 'options' =>$copyOptions]);
			}
		}

		return $meta;
	}

	public function renderS3InfoMeta() {
		global $post;

		$meta = wp_get_attachment_metadata($post->ID);
		if (empty($meta)) {
		    return;
        }

		if (!isset($meta['s3'])) {
			$meta = get_post_meta($post->ID, 'ilab_s3_info', true);
		}

		if (empty($meta) || !isset($meta['s3'])) {
			?>
			Not uploaded to S3.
			<?php
		} else {
			?>
            <div class="misc-pub-section">
                Bucket: <a href="https://console.aws.amazon.com/s3/buckets/<?php echo $meta['s3']['bucket']?>" target="_blank"><?php echo $meta['s3']['bucket']?></a>
            </div>
            <div class="misc-pub-section">
                Path: <a href="https://console.aws.amazon.com/s3/buckets/<?php echo $meta['s3']['bucket']?>/<?php echo $meta['s3']['key']?>/details" target="_blank"><?php echo $meta['s3']['key']?></a>
            </div>
            <div class="misc-pub-section">
                <label for="s3-access-acl">Access:</label>
                <select id="s3-access-acl" name="s3-access-acl">
                    <option value="public-read" <?php echo (isset($meta['s3']['privacy']) && ($meta['s3']['privacy']=='public-read')) ? 'selected' : '' ?>>Public</option>
                    <option value="authenticated-read" <?php echo (isset($meta['s3']['privacy']) && ($meta['s3']['privacy']=='authenticated-read')) ? 'selected' : '' ?>>Authenticated Users</option>
                </select>
            </div>
            <div class="misc-pub-section">
                <label for="s3-cache-control">Cache-Control:</label>
                <input type="text" class="widefat" name="s3-cache-control" id="s3-cache-control" value="<?php echo (isset($meta['s3']['options']) && isset($meta['s3']['options']['params']['CacheControl'])) ? $meta['s3']['options']['params']['CacheControl'] : '' ?>">
            </div>
            <div class="misc-pub-section">
                <label for="s3-expires">Expires:</label>
                <input type="text" class="widefat" name="s3-expires" id="s3-expires" value="<?php echo (isset($meta['s3']['options']) && isset($meta['s3']['options']['params']['Expires'])) ? $meta['s3']['options']['params']['Expires'] : '' ?>">
            </div>
            <div class="misc-pub-section">
                <a href="<?php echo $meta['s3']['url']?>" target="_blank">View S3 URL</a></strong>
            </div>
			<?php
        }

	}

	//region S3 Client Methods
	public function s3enabled() {
		if (!($this->key && $this->secret && $this->bucket))
		{
			$this->displayAdminNotice('error',"To start using Cloud Storage, you will need to <a href='admin.php?page={$this->options_page}'>supply your AWS credentials.</a>.");
			return false;
		}

		$penabled = parent::enabled();
		if (!$penabled) {
			$this->displayAdminNotice('error',"To start using Cloud Storage, you will need to <a href='admin.php?page=media-tools-top'>enable it</a>.");
			return false;
		}

		if ($this->settingsError)
			return false;

		return true;
	}

	private function getBucketRegion() {
        if (!$this->s3enabled()) {
            return false;
        }

		$config = [
			'version' => 'latest',
			'credentials' => [
				'key'    => $this->key,
				'secret' => $this->secret
			]
		];

		if (!empty($this->endpoint)) {
			$config['endpoint'] = $this->endpoint;
			if ($this->endPointPathStyle) {
				$config['use_path_style_endpoint'] = true;
			}
		}

		$s3=new S3MultiRegionClient($config);
		$region = false;
		try {
			$region = $s3->determineBucketRegion($this->bucket);
        } catch (AwsException $ex) {
		    Logger::error( "AWS Error fetching region", [ 'exception' => $ex->getMessage()]);
        }

		return $region;
    }

	private function getS3MultiRegionClient() {
		if (!$this->s3enabled())
			return null;

		$config = [
			'version' => 'latest',
			'credentials' => [
				'key'    => $this->key,
				'secret' => $this->secret
			]
		];

		if (!empty($this->endpoint)) {
			$config['endpoint'] = $this->endpoint;

			if ($this->endPointPathStyle) {
				$config['use_path_style_endpoint'] = true;
            }
		}

		if ($this->useTransferAcceleration) {
			$config['use_accelerate_endpoint'] = true;
		}

		$s3=new S3MultiRegionClient($config);
		return $s3;
	}

    private function getS3Client($region = false) {
	    if (!$this->s3enabled())
		    return null;

	    if (empty($region)) {
	        if (empty($this->region)) {
	            $this->region = $this->getBucketRegion($this->bucket);
	            if (empty($this->region)) {
	                Logger::info( "Could not get region from server.");
	                return null;
                }

                update_option('ilab-media-s3-region', $this->region);
            }

		    $region = $this->region;
        }

        if (empty($region)) {
	        return null;
        }

	    $config = [
		    'version' => 'latest',
		    'credentials' => [
			    'key'    => $this->key,
			    'secret' => $this->secret
		    ],
            'region' => $region
	    ];

	    if (!empty($this->endpoint)) {
		    $config['endpoint'] = $this->endpoint;
		    if ($this->endPointPathStyle) {
			    $config['use_path_style_endpoint'] = true;
		    }
	    }

	    if ($this->useTransferAcceleration) {
		    $config['use_accelerate_endpoint'] = true;
	    }

	    $s3=new S3Client($config);
	    return $s3;
    }


	public function s3Client($insure_bucket=false)
	{
		if (!$this->s3enabled())
			return null;

		$s3 = $this->getS3Client();
		if (!$s3) {
		    Logger::info( 'Could not create regular client, creating multi-region client instead.');
		    $s3 = $this->getS3MultiRegionClient();
        }

		if ($insure_bucket && !$this->skipBucketCheck) {
			if (!$s3->doesBucketExist($this->bucket)) {
			    try {
				    Logger::info( "Bucket does not exist, trying to list buckets.");

				    $result = $s3->listBuckets();
				    $buckets = $result->get('Buckets');
				    if (!empty($buckets)) {
					    foreach($buckets as $bucket) {
						    if ($bucket['Name'] == $this->bucket) {
							    return $s3;
						    }
					    }
                    }

				    Logger::info( "Bucket does not exist.");
				    return null;
                } catch (AwsException $ex) {
			        Logger::error( "Error insuring bucket exists.", [ 'exception' => $ex->getMessage()]);
			        return null;
                }

				return null;
            }
        }

		return $s3;
	}

	//endregion

	/**
	 * Filter for when attachments are updated
	 *
	 * @param $data
	 * @param $id
	 * @return mixed
	 */
	public function updateAttachmentMetadata($data,$id)
	{
	    if ($this->skipUpdate) {
	        return $data;
        }

		if (!$data) {
			return $data;
		}

		$mime = (isset($data['ilab-mime'])) ? $data['ilab-mime'] : null;
	    if ($mime) {
	        unset($data['ilab-mime']);
        }

		if (!isset($data['file'])) {
	        if (!$mime) {
		        $mime = get_post_mime_type($id);
            }

			if ($mime == 'application/pdf') {
				$renderPDF = apply_filters('ilab_imgix_render_pdf', false);

			    if (!$renderPDF) {
				    unset($data['sizes']);
			    }

                $s3Info = get_post_meta($id, 'ilab_s3_info', true);
                if ($s3Info) {
                    $pdfInfo = $this->pdfInfo[$s3Info['file']];
                    $data['width'] = $pdfInfo['width'];
                    $data['height'] = $pdfInfo['height'];
                    $data['file'] = $s3Info['s3']['key'];
                    $data['s3'] = $s3Info['s3'];
                    if ($renderPDF) {
                        $data['sizes']['full']['file'] = $s3Info['s3']['key'];
                        $data['sizes']['full']['width'] = $data['width'];
                        $data['sizes']['full']['height'] = $data['height'];
                    }
                }
			}
			return $data;
		}

		$upload_info=wp_upload_dir();
		$upload_path=$upload_info['basedir'];
		$path_base=pathinfo($data['file'])['dirname'];

		if (!file_exists($upload_path.'/'.$data['file'])) {
			return $data;
		}

		if (!$mime) {
			$mime = wp_get_image_mime($upload_path.'/'.$data['file']);
        }

		if ($mime && in_array($mime, $this->ignoredMimeTypes)) {
			return $data;
		}

		$s3=$this->s3Client(true);
		if ($s3)
		{
			if (!isset($data['s3'])) {
				$data=$this->processFile($s3,$upload_path,$data['file'],$data,$id);

				if (isset($data['sizes'])) {
					foreach($data['sizes'] as $key => $size)
					{
						if (!is_array($size))
							continue;

						$file=$path_base.'/'.$size['file'];
						if ($file == $data['file']) {
							$data['sizes'][$key]['s3']=$data['s3'];
						} else {
							$data['sizes'][$key]=$this->processFile($s3,$upload_path,$file,$size,$id);
						}
					}
				}

				if (isset($data['s3'])) {
					$data = apply_filters('ilab_s3_after_upload', $id, $data);
                }
			}


		}

		return $data;
	}

	public function processCrop($size, $upload_path, $file, $sizeMeta) {
		$upload_info=wp_upload_dir();
		$subdir = trim(str_replace($upload_info['basedir'], '', $upload_path), '/');
		$upload_path = rtrim(str_replace($subdir, '', $upload_path), '/');

		$s3=$this->s3Client(true);
		if ($s3) {
			$sizeMeta = $this->processFile($s3, $upload_path, $subdir.'/'.$file, $sizeMeta);
		}

		return $sizeMeta;
	}

	public function handleUpload($upload, $context='upload') {
		if (!isset($upload['file']))
			return $upload;

		if (isset($upload['type']) && in_array($upload['type'],$this->ignoredMimeTypes)) {
			return $upload;
		}

		if (file_is_displayable_image($upload['file']))
			return $upload;

		if (isset($_REQUEST["action"]) && ($_REQUEST["action"]=="upload-plugin")) {
			return $upload;
		}

		$shouldHandle = apply_filters('ilab_s3_should_handle_upload', false, $upload);

		if (!$shouldHandle && !$this->uploadDocs) {
			return $upload;
		}

		$s3=$this->s3Client(true);
		if ($s3)
		{
			$pi = pathinfo($upload['file']);

			$upload_info=wp_upload_dir();
			$upload_path=$upload_info['basedir'];

			$file = trim(str_replace($upload_path,'',$pi['dirname']),'/').'/'.$pi['basename'];

			if (($upload['type']=='application/pdf') && file_exists($upload_path.'/'.$file)) {
				set_error_handler(function($errno, $errstr, $errfile, $errline){
					throw new \Exception($errstr);
				}, E_RECOVERABLE_ERROR);

			    try {
				    $parser = new Parser();
				    $pdf = $parser->parseFile($upload_path.'/'.$file);
				    $pages = $pdf->getPages();
				    if (count($pages)>0) {
					    $page = $pages[0];
					    $details = $page->getDetails();
					    if (isset($details['MediaBox'])) {
						    $data = [];
						    $data['width'] = $details['MediaBox'][2];
						    $data['height'] = $details['MediaBox'][3];
						    $this->pdfInfo[$upload_path.'/'.$file] = $data;
					    }
				    }
                } catch (\Exception $ex) {
				    Logger::error( 'PDF Parsing Error', [ 'exception' =>$ex->getMessage()]);
                }

                restore_error_handler();
            }

			$upload = $this->processFile($s3, $upload_path, $file, $upload);
			if (isset($upload['s3'])) {
				if ($this->docCdn)
					$upload['url'] = trim($this->docCdn, '/').'/'.$file;
				else if (isset($upload['s3']['url']))
					$upload['url'] = $upload['s3']['url'];
			}

			$this->uploadedDocs[$file] = $upload;
		}

		return $upload;
	}

	private function genUUID() {
		return sprintf('%04x%04x%04x%03x4%04x%04x%04x%04x',
		               mt_rand(0, 65535),
		               mt_rand(0, 65535),
		               mt_rand(0, 65535),
		               mt_rand(0, 4095),
		               bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
		               mt_rand(0, 65535),
		               mt_rand(0, 65535),
		               mt_rand(0, 65535)
		);
	}

	private function genUUIDPath() {
		$uid = $this->genUUID();
		$result='/';

		$segments = 8;
		if ($segments>strlen($uid)/2)
			$segments=strlen($uid)/2;
		for($i=0; $i<$segments; $i++)
			$result.=substr($uid,$i*2,2).'/';

		return $result;
	}
	private function get_object_version_string($id=null) {

		if (!empty($id) && !empty($this->versionedIds[$id])) {
			return $this->versionedIds[$id];
		}

		$date_format = 'dHis';
		// Use current time so that object version is unique
		$time = current_time( 'timestamp' );

		$object_version = date( $date_format, $time ) . '/';
		$object_version = apply_filters( 'as3cf_get_object_version_string', $object_version );

		if (!empty($id)) {
			$this->versionedIds[$id] = $object_version;
		}

		return $object_version;
	}

	private function parsePrefix($prefix, $id=null) {
		$host = parse_url(get_home_url(), PHP_URL_HOST);

		$user = wp_get_current_user();
		$userName = '';
		if ($user->ID != 0) {
			$userName = sanitize_title($user->display_name);
		}

		if ($id) {
			$prefix = str_replace("@{versioning}", $this->get_object_version_string($id), $prefix);
        }

		$prefix = str_replace("@{site-id}", sanitize_title(strtolower(get_current_blog_id())), $prefix);
		$prefix = str_replace("@{site-name}", sanitize_title(strtolower(get_bloginfo('name'))), $prefix);
		$prefix = str_replace("@{site-host}", $host, $prefix);
		$prefix = str_replace("@{user-name}", $userName, $prefix);
		$prefix = str_replace("@{unique-id}", $this->genUUID(), $prefix);
		$prefix = str_replace("@{unique-path}", $this->genUUIDPath(), $prefix);
		$prefix = str_replace("//","/", $prefix);

		$matches = [];
		preg_match_all('/\@\{date\:([^\}]*)\}/', $prefix, $matches);
		if (count($matches)==2) {
			for($i = 0; $i<count($matches[0]); $i++) {
				$prefix = str_replace($matches[0][$i],date($matches[1][$i]), $prefix);
			}
		}

		return trim($prefix, '/').'/';
	}

	private function processFile($s3,$upload_path,$filename,$data,$id=null)
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

		$bucketFilename = $filename;

		$prefix = '';
		if (!empty($this->prefixFormat)) {
			$prefix = $this->parsePrefix($this->prefixFormat, $id);
			$parts= explode('/',$filename);
			$bucketFilename = array_pop($parts);
		}

		$file=fopen($upload_path.'/'.$filename,'r');
		try
		{
			$options = [];
			$params = [];

			if (!empty($this->cacheControl)) {
				$params['CacheControl'] = $this->cacheControl;
			}

			if (!empty($this->expires)) {
				$params['Expires'] = $this->expires;
			}

			if (!empty($params)) {
				$options['params'] = $params;
			}

			$options = apply_filters('ilab_s3_upload_options', $options, $id, $data);

			Logger::startTiming( "Start Upload", [ 'file' => $prefix . $bucketFilename]);
			$result = $s3->upload($this->bucket,$prefix.$bucketFilename,$file, $this->privacy, $options);
			Logger::endTiming( "End Upload", [ 'file' => $prefix . $bucketFilename]);

			$data['s3']=[
				'url' => $result->get('ObjectURL') ,
				'bucket'=>$this->bucket,
				'privacy' => $this->privacy,
				'key'=> $prefix.$bucketFilename,
			    'options' => $options
			];

			if (file_exists($upload_path.'/'.$filename)) {
				$ftype = wp_check_filetype($upload_path.'/'.$filename);
				if (!empty($ftype) && isset($ftype['type'])) {
					$data['s3']['mime-type'] = $ftype['type'];
				}
			}
		}
		catch (AwsException $ex)
		{
			Logger::error( 'S3 Upload Error', [ 'exception'      =>$ex->getMessage(),
			                                    'bucket'         =>$this->bucket,
			                                    'prefix'         =>$prefix,
			                                    'bucketFilename' =>$bucketFilename,
			                                    'privacy'        =>$this->privacy,
			                                    'options'        =>$options]);
		}

		fclose($file);

		if ($this->deleteOnUpload) {
			if (file_exists($upload_path.'/'.$filename)) {
				unlink( $upload_path . '/' . $filename );
			}
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
		if (!$this->deleteFromS3)
			return $id;

		$data=wp_get_attachment_metadata($id);
		if (isset($data['file']) && !isset($data['s3'])) {
			return $id;
		}

		$s3=$this->s3Client(true);
		if ($s3)
		{
			if (!isset($data['file'])) {
				$file = get_attached_file($id);
				if ($file) {
					if (strpos($file,'http')===0) {
						$pi = parse_url($file);
						$file = trim($pi['path'], '/');
						if(0 === strpos($file, $this->bucket)) {
							$file = substr($file, strlen($this->bucket)).'';
							$file = trim($file, '/');
						}
					} else {
						$pi = pathinfo($file);
						$upload_info=wp_upload_dir();
						$upload_path=$upload_info['basedir'];

						$file = trim(str_replace($upload_path,'',$pi['dirname']),'/').'/'.$pi['basename'];
					}

					$this->delete_file($s3, $file);
				}

			} else {
				$this->delete_file($s3,$data['s3']['key']);

				if (isset($data['sizes'])) {
					$pathParts = explode('/',$data['s3']['key']);
					array_pop($pathParts);
					$path_base = implode('/',$pathParts);

					foreach($data['sizes'] as $key => $size) {
						$file=$path_base.'/'.$size['file'];
						try {
							$this->delete_file($s3,$file);
						} catch (\Exception $ex) {
							error_log($ex->getMessage());
						}
					}
				}
			}
		}

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
		catch (AwsException $ex)
		{
			Logger::error( 'S3 Delete File Error', [ 'exception' =>$ex->getMessage(), 'Bucket' =>$this->bucket, 'Key' =>$file]);
		}
	}

	private function getOffloadS3URL($post_id, $info) {

		if (!is_array($info) && (count($info)<1))
			return null;

		$region = $info[0]['region'];
		$bucket = $info[0]['bucket'];
		$file = $info[0]['key'];

		return "http://s3-$region.amazonaws.com/$bucket/$file";
	}

	private function getAttachmentURLFromMeta($meta) {
		if (isset($meta['s3']) && $this->cdn) {
			return $this->cdn.'/'.$meta['s3']['key'];
		}
		else if (isset($meta['s3']) && isset($meta['s3']['url'])) {
			if (isset($meta['file']) && $this->docCdn) {
				$ext = strtolower(pathinfo($meta['file'],PATHINFO_EXTENSION));
				$image_exts = array( 'jpg', 'jpeg', 'jpe', 'gif', 'png' );
				if (!in_array( $ext, $image_exts ))
					return trim($this->docCdn,'/').'/'.$meta['s3']['key'];
			}

			return $meta['s3']['url'];
		}

		return null;
	}

	public function getAttachmentURL($url, $post_id)
	{
		$meta=wp_get_attachment_metadata($post_id);

		$new_url = null;
		if ($meta)
			$new_url = $this->getAttachmentURLFromMeta($meta);

		if (!$new_url) {
			$meta = get_post_meta($post_id, 'ilab_s3_info', true);
			if ($meta) {
				$new_url = $this->getAttachmentURLFromMeta($meta);
			}

			if (!$new_url) {
				$meta = get_post_meta($post_id, 'amazonS3_info');

				if ($meta) {
					$new_url = $this->getOffloadS3URL($post_id, $meta);

					$s3Data=$meta[0];
					$s3Data['url'] = $new_url;
					$s3Data['privacy'] = 'public-read';

					$this->skipUpdate = true;

					$imageMeta = wp_get_attachment_metadata($post_id);
					if ($imageMeta) {
					    $imageMeta['s3'] = $s3Data;
					    wp_update_attachment_metadata($post_id, $imageMeta);
                    } else {
					    update_post_meta($post_id, ['s3' => $s3Data]);
                    }

					$this->skipUpdate = false;
				}
			}

			if (!$meta && $this->docCdn) {
				$post = \WP_Post::get_instance($post_id);
				if ($post && (strpos($post->guid, $this->docCdn) === 0))
					$new_url = $post->guid;
			}
		}

		return $new_url ?: $url;
	}


	public function calculateSrcSet($sources, $size_array, $image_src, $image_meta, $attachment_id) {
		if (!apply_filters('ilab_s3_can_calculate_srcset', true))
			return $sources;

		foreach($image_meta['sizes'] as $sizeName => $sizeData) {
			$width = $sizeData['width'];
			if (isset($sources[$width])) {
				$src = wp_get_attachment_image_src($attachment_id, $sizeName);

				if (is_array($src))
					$sources[$width]['url'] = $src[0];
				else
					unset($sources[$width]);
			}
		}

		if (isset($image_meta['width'])) {
			$width = $image_meta['width'];
			if (isset($sources[$width])) {
				$src = wp_get_attachment_image_src($attachment_id, 'full');

				if (is_array($src))
					$sources[$width]['url'] = $src[0];
				else
					unset($sources[$width]);
			}
		}

		return $sources;
	}

	public function settingsChanged() {
		delete_option('ilab-s3-settings-error');
		$this->settingsError = false;

		if ($this->s3enabled()) {
			$s3 = $this->s3Client(true);
			if ($s3 == null) {
				$this->settingsError = true;
				update_option('ilab-s3-settings-error', true);
			}
		}
	}

	public function renderImporter() {
	    $enabled = $this->s3enabled();

		$shouldCancel = get_option('ilab_s3_import_should_cancel', false);
		$status = get_option('ilab_s3_import_status', false);
		$total = get_option('ilab_s3_import_total_count', 0);
		$current = get_option('ilab_s3_import_current', 1);
		$currentFile = get_option('ilab_s3_import_current_file', '');

		if ($total == 0) {
			$attachments = get_posts([
				                         'post_type'=> 'attachment',
				                         'posts_per_page' => -1
			                         ]);

			$total = count($attachments);
		}

		$progress = 0;

		if ($total > 0) {
			$progress = ($current / $total) * 100;
		}

		echo ToolView::render_view( 's3/ilab-s3-importer.php', [
			'status' => ($status) ? 'running' : 'idle',
			'total' => $total,
			'progress' => $progress,
			'current' => $current,
			'currentFile' => $currentFile,
            'enabled' => $enabled,
            'shouldCancel' => $shouldCancel
		]);
	}

	public function importProgress() {
		$shouldCancel = get_option('ilab_s3_import_should_cancel', false);
		$status = get_option('ilab_s3_import_status', false);
		$total = get_option('ilab_s3_import_total_count', 0);
		$current = get_option('ilab_s3_import_current', 0);
		$currentFile = get_option('ilab_s3_import_current_file', '');

		header('Content-type: application/json');
		echo json_encode([
			                 'status' => ($status) ? 'running' : 'idle',
			                 'total' => (int)$total,
			                 'current' => (int)$current,
                             'currentFile' => $currentFile,
			                 'shouldCancel' => $shouldCancel
		                 ]);
		die;
	}

	public function cancelImportMedia() {
		update_option('ilab_s3_import_should_cancel', 1);
		StorageImportProcess::cancelAll();

		return json_response(['status'=>'ok']);
	}

	public function importMedia() {

		$args = [
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'nopaging'       => true,
			'fields'         => 'ids',
		];

		if (!$this->uploadDocs) {
			$args['post_mime_type'] = 'image';
		}

		$query = new \WP_Query($args);

		if ($query->post_count > 0) {
			update_option('ilab_s3_import_status', true);
			update_option('ilab_s3_import_total_count', $query->post_count);
			update_option('ilab_s3_import_current', 1);
			update_option('ilab_s3_import_should_cancel', false);

			$process = new StorageImportProcess();

			for($i = 0; $i < $query->post_count; ++$i) {
				$process->push_to_queue(['index' => $i, 'post' => $query->posts[$i]]);
			}

			$process->save();
			$process->dispatch();
		} else {
			delete_option('ilab_s3_import_status');
		}

		header('Content-type: application/json');
		echo '{"status":"running"}';
		die;
	}

	public function getAttachedFile($file, $attachment_id) {
		if (!file_exists($file)) {
			$meta=wp_get_attachment_metadata($attachment_id);

			$new_url = null;
			if ($meta)
				$new_url = $this->getAttachmentURLFromMeta($meta);

			if (!$new_url) {
				$meta = get_post_meta($attachment_id, 'ilab_s3_info', true);
				if ($meta) {
					$new_url = $this->getAttachmentURLFromMeta($meta);
				}
				else if (!$meta && $this->docCdn) {
					$post = \WP_Post::get_instance($attachment_id);
					if ($post && (strpos($post->guid, $this->docCdn) === 0))
						$new_url = $post->guid;
				}
			}

			if ($new_url)
				return $new_url;
		}

		return $file;
	}
	public function imageDownsize($fail,$id,$size)
	{
		if (apply_filters('ilab_imgix_enabled', false)) {
			return $fail;
		}

		if (empty($size) || empty($id) || is_array($size)) {
		    return $fail;
        }

		$meta=wp_get_attachment_metadata($id);

		if (empty($meta)) {
		    return $fail;
        }

		if (!isset($meta['sizes'])) {
			return $fail;
		}

		if (!isset($meta['sizes'][$size])) {
		    return $fail;
        }

		$sizeMeta = $meta['sizes'][$size];
		if (!isset($sizeMeta['s3'])) {
			return $fail;
		}

		$url = $sizeMeta['s3']['url'];

		$result=[
			$url,
			$sizeMeta['width'],
			$sizeMeta['height'],
		    true
		];

		return $result;
	}

	public function prepareAttachmentForJS($response, $attachment, $meta ) {
	    if (empty($meta) || !isset($meta['s3'])) {
	        $meta = get_post_meta($attachment->ID, 'ilab_s3_info', true);
        }

		if (isset($meta['s3'])) {
			$response['s3'] = $meta['s3'];

			if (!isset($response['s3']['privacy'])) {
				$response['s3']['privacy'] = $this->privacy;
			}
		}

		return $response;
	}

	public function uploadUrlForFile($filename) {
	    $s3 = $this->s3Client(false);

		$bucketFilename = $filename;

		$prefix = '';
		if (!empty($this->prefixFormat)) {
			$prefix = $this->parsePrefix($this->prefixFormat, null);
			$parts= explode('/',$filename);
			$bucketFilename = array_pop($parts);
		}

		if ($prefix == '') {
		    $prefix = date('Y/m').'/';
        }

		try
		{
			$optionsData = [
				['bucket'=>$this->bucket],
				['acl' => $this->privacy],
				['key' => $prefix.$bucketFilename],
                ['starts-with', '$Content-Type', '']
			];

			if (!empty($this->cacheControl)) {
				$optionsData[] = ['Cache-Control' => $this->cacheControl];
			}

			if (!empty($this->expires)) {
				$optionsData[] = ['Expires' => $this->expires];
			}

			$postObject = new PostObjectV4($s3, $this->bucket, [], $optionsData, '+15 minutes');
			$result = [
			        'key'=>$prefix.$bucketFilename,
                    'postObject' => $postObject,
			        'CacheControl' => (!empty($this->cacheControl)) ? $this->cacheControl : null,
			        'Expires' => (!empty($this->expires)) ? $this->expires : null,
            ];

			return $result;
		}
		catch (AwsException $ex)
		{
			Logger::error( 'S3 Generate File Upload URL Error', [ 'exception' =>$ex->getMessage()]);
		}

		return null;
    }

	public function s3Bucket() {
	    return $this->bucket;
    }

    public function importImageAttachmentFromS3($key) {
        $s3 = $this->s3Client();

	    $command = $s3->getCommand('GetObject', ['Bucket' => $this->bucket, 'Key' => $key]);
	    $presignedRequest = $s3->createPresignedRequest($command, '+10 minutes');
        $presignedUrl =  (string)  $presignedRequest->getUri();

	    $faster = new FasterImage();
	    $result = $faster->batch([$presignedUrl]);
	    if (empty($result)) {
	        return false;
        }

        $result = $result[$presignedUrl];

        if (!is_array($result['size'])) {
            return false;
        }

        $mimeType = 'image/'.$result['type'];

        $fileParts = explode('/',$key);
        $filename = array_pop($fileParts);
        $url = $s3->getObjectUrl($this->bucket, $key);

        $s3Info = [
            'url' => $url,
            'mime-type' => $mimeType,
            'bucket' => $this->bucket,
            'privacy' => $this->privacy,
            'key' => $key,
            'options' => [
                    'params' => []
            ]
        ];

	    if (!empty($this->cacheControl)) {
		    $s3Info['options']['params']['CacheControl'] = $this->cacheControl;
	    }

	    if (!empty($this->expires)) {
		    $s3Info['options']['params']['Expires'] = $this->expires;
	    }


        $meta = [
	        'width' => $result['size'][0],
	        'height' => $result['size'][1],
            'file' => $key,
            'image_meta' => [],
            's3' => $s3Info,
            'sizes' => []
        ];

	    $builtInSizes = [];
	    foreach(['thumbnail', 'medium', 'medium_large', 'large'] as $size) {
	        $builtInSizes[$size] = [
		        'width' => get_option("{$size}_size_w"),
		        'height' => get_option("{$size}_size_h"),
                'crop' => get_option("{$size}_crop", 0),
            ];
        }

	    $additional_sizes = wp_get_additional_image_sizes();
        $sizes=array_merge($builtInSizes, $additional_sizes);

        foreach($sizes as $sizeKey => $size) {
            $resized = image_resize_dimensions($result['size'][0],$result['size'][1],$size['width'],$size['height'],$size['crop']);
            if ($resized) {
	            $meta['sizes'][$sizeKey] = [
		            'file' => $filename,
		            'width' => $resized[4],
		            'height' => $resized[5],
		            'mime-type' => 'image/jpeg',
		            's3' => $s3Info
	            ];
            }
        }

	    $post = wp_insert_post([
		                           'post_author' => get_current_user_id(),
		                           'post_title' => $filename,
		                           'post_status' => 'inherit',
		                           'post_type' => 'attachment',
		                           'guid' => $url,
		                           'post_mime_type' => $mimeType
	                           ]);

	    if (is_wp_error($post)) {
		    return false;
	    }

	    $meta = apply_filters('ilab_s3_after_upload', $post, $meta);

        add_post_meta($post, '_wp_attached_file', $key);
        add_post_meta($post, '_wp_attachment_metadata', $meta);

        $thumbUrl = image_downsize($post, ['width'=>128, 'height'=>128]);

        if (is_array($thumbUrl)) {
            $thumbUrl = $thumbUrl[0];
        }



        return [
	        'id' => $post,
            'url' =>$url,
            'thumb' => $thumbUrl
        ];
    }

    public function documentUploadsEnabled() {
	    return $this->uploadDocs;
    }

    public function hasCustomEndPoint() {
	    return !empty($this->endpoint);
    }

    public function region() {
	    return $this->region;
    }

    public function customEndpointIsGoogle() {
	    if ($this->hasCustomEndPoint()) {
	        return (strpos($this->endpoint, 'googleapis.com')>0);
        }

        return false;
    }

    public function accessKey() {
	    return $this->key;
	}

	protected function hookMediaGrid() {
	    if (!$this->displayBadges) {
	        return;
        }
        
	    add_action('admin_head', function(){
	       ?>
            <style>
                .ilab-s3-logo {
                    display: none;
                    position: absolute;
                    right: 5px;
                    bottom: 4px;
                    z-index: 5;
                }

                .has-s3 > .ilab-s3-logo {
                    display: block;
                }
            </style>
            <?php
        });
		add_action('admin_footer', function(){
			?>
            <script>
                jQuery(document).ready(function() {
                    var attachTemplate = jQuery('#tmpl-attachment');
                    if (attachTemplate) {
                        var txt = attachTemplate.text();

                        var search = '<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }}">';
                        var replace = '<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }} <# if (data.hasOwnProperty("s3")) {#>has-s3<#}#>"><img src="<?php echo ILAB_PUB_IMG_URL.'/ilab-cloud-icon.svg'?>" width="29" height="18" class="ilab-s3-logo">';
                        txt = txt.replace(search, replace);
                        attachTemplate.text(txt);
                    }
                });
            </script>
			<?php

		} );
    }
}
