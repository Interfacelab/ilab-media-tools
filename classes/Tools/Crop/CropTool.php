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

namespace ILAB\MediaCloud\Tools\Crop;

use ILAB\MediaCloud\Tools\Storage\StorageTool;
use ILAB\MediaCloud\Tools\Tool;
use ILAB\MediaCloud\Tools\ToolsManager;
use ILAB\MediaCloud\Utilities\Environment;
use ILAB\MediaCloud\Utilities\View;
use function ILAB\MediaCloud\Utilities\gen_uuid;
use function ILAB\MediaCloud\Utilities\json_response;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * Class ILabMediaCropTool
 *
 * Crop tool
 */
class CropTool extends Tool
{
	protected $cropQuality = 100;

	public function __construct($toolName, $toolInfo, $toolManager)
	{
		parent::__construct($toolName, $toolInfo, $toolManager);

		$this->cropQuality = Environment::Option('mcloud-crop-quality', null, 100);

		$this->testForBadPlugins();
		$this->testForUselessPlugins();
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
			add_action('wp_ajax_ilab_reset_crop',[$this,'resetCrop']);
		}
	}

	public function hasSettings() {
		return true;
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

				ob_start();
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
                                    ILabModal.loadURL("<?php echo get_admin_url(null, 'admin-ajax.php')?>?action=ilab_crop_image_page&size=<?php echo $sizeKeys[0]?>&post="+image_id,false,null);

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
				$result=ob_get_clean();
				echo $result;
			}, PHP_INT_MAX);
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
		$url = parse_url(get_admin_url(null, 'admin-ajax.php'), PHP_URL_PATH) . "?action=ilab_crop_image_page&post=$id&size=$size";
		if ($partial===true) {
			$url .= '&partial=1';
		}

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
		if (empty($sizeInfo['crop'])) {
			$real_crop_width = (int)$meta['width'];
			$real_crop_height = (int)$meta['height'];
        } else {
		    list($real_crop_width, $real_crop_height) = sizeToFitSize($sizeInfo['width'], $sizeInfo['height'], $meta['width'], $meta['height']);
        }

		if (!empty($sizeInfo['crop'])) {
			$ratio=$crop_width/$crop_height;
		} else {
			$ratio = floatval($meta['width'])/floatval($meta['height']);
		}

		$crop_exists = !empty($meta['sizes'][$size]['file']);
		$crop_attrs = (!$crop_exists) ? null : wp_get_attachment_image_src($image_id, $size);

		$cropped_src=null; $cropped_width=null; $cropped_height=null;
		if ($crop_attrs) {
			list($cropped_src,$cropped_width,$cropped_height,$cropped_cropped)=$crop_attrs;
		}

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
			'image_width' => (int)$meta['width'],
			'image_height' => (int)$meta['height'],
			'real_crop_width' => (int)$real_crop_width,
			'real_crop_height' => (int)$real_crop_height,
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
			'isCrop' => (empty($sizeInfo['crop'])) ? false : true,
			'cropped_src'=>$cropped_src,
			'cropped_width'=>$cropped_width,
			'cropped_height'=>$cropped_height,
			'crop_axis' => (empty($sizeInfo['crop']) || !is_array($sizeInfo['crop'])) ? [] : $sizeInfo['crop'],
			'prev_crop_x' => $prev_crop_x,
			'prev_crop_y' => $prev_crop_y,
			'prev_crop_width' => $prev_crop_w,
			'prev_crop_height' => $prev_crop_h,
			'src'=>$full_src
		];

		if (current_user_can( 'edit_post', $image_id))
		{
			if (!$partial)
				echo View::render_view( 'crop/ilab-crop-ui.php', $data);
			else
			{
				json_response([
					'status'=>'ok',
					'image_id'=>$image_id,
					'image_width' => (int)$meta['width'],
					'image_height' => (int)$meta['height'],
					'real_crop_width' => (int)$real_crop_width,
					'real_crop_height' => (int)$real_crop_height,
					'size'=>$size,
					'size_title'=>(ucwords(str_replace('-', ' ', $size))),
					'min_width'=>$crop_width,
					'min_height'=>$crop_height,
					'aspect_ratio'=>$ratio,
					'isCrop' => (empty($sizeInfo['crop'])) ? false : true,
					'prev_crop_x'=>($prev_crop_x!==null) ? (int)$prev_crop_x : null,
					'prev_crop_y'=>($prev_crop_y!==null) ? (int)$prev_crop_y : null,
					'prev_crop_width'=>($prev_crop_w!==null) ? (int)$prev_crop_w : null,
					'prev_crop_height'=>($prev_crop_h!==null) ? (int)$prev_crop_h : null,
					'crop_axis' => (empty($sizeInfo['crop']) || !is_array($sizeInfo['crop'])) ? [] : $sizeInfo['crop'],
					'cropped_src'=>$cropped_src,
					'cropped_width'=>$cropped_width,
					'cropped_height'=>$cropped_height
				]);
			}
		}

		die;
	}

	/**
	 * Reset the crop to the default
	 */
	public function resetCrop() {
		$post_id = esc_html( $_POST['post'] );
		if (!current_user_can('edit_post', $post_id)) {
			json_response([
				'status'=>'error',
				'message'=>'You are not strong enough, smart enough or fast enough.'
			]);
		}

		$size = esc_html($_POST['size']);

		$meta = wp_get_attachment_metadata($post_id);
		$width = (int)$meta['width'];
		$height = (int)$meta['height'];

		if (($width == 0) || ($height == 0)) {
			json_response([
				'status' => 'error',
				'message' => 'Invalid image dimensions.'
			]);
		}

		$crop_size = ilab_get_image_sizes($size);

		if (empty($crop_size['crop'])) {
			$crop_x = 0;
			$crop_y = 0;
			$crop_width = $width;
			$crop_height = $height;
		} else {
			list($crop_width, $crop_height) = sizeToFitSize($crop_size['width'], $crop_size['height'], $width, $height);

			if (!is_array($crop_size['crop'])) {
				$crop_x = (int)(($width / 2.0) - ($crop_width / 2.0));
				$crop_y = (int)(($height / 2.0) - ($crop_height / 2.0));
			} else {
				list($xAxis, $yAxis) = $crop_size['crop'];
				if ($xAxis == 'left') {
					$crop_x = 0;
				} else if ($xAxis == 'right') {
					$crop_x = $width - $crop_width;
				} else {
					$crop_x = (int)(($width / 2.0) - ($crop_width / 2.0));
				}

				if ($yAxis == 'top') {
					$crop_y = 0;
				} else if ($yAxis == 'bottom') {
					$crop_y = $height - $crop_height;
				} else {
					$crop_y = (int)(($height / 2.0) - ($crop_height / 2.0));
				}
			}
		}

		$this->doPerformCrop($post_id, $size, $crop_x, $crop_y, $crop_width, $crop_height, true);
	}

	/**
	 * Perform the actual crop
	 */
	public function performCrop() {
		$post_id = esc_html( $_POST['post'] );
		if (!current_user_can('edit_post', $post_id)) {
			json_response([
				'status'=>'error',
				'message'=>'You are not strong enough, smart enough or fast enough.'
			]);
		}

		$size = esc_html($_POST['size']);
		$crop_width = (int)floor(esc_html($_POST['width']));
		$crop_height = (int)floor(esc_html($_POST['height']));
		$crop_x = (int)floor(esc_html($_POST['x']));
		$crop_y = (int)floor(esc_html($_POST['y']));

		$this->doPerformCrop($post_id, $size, $crop_x, $crop_y, $crop_width, $crop_height, false);
	}

	/**
	 * Perform the actual crop
	 */
	private function doPerformCrop($post_id, $size, $crop_x, $crop_y, $crop_width, $crop_height, $reset_crop)  {
		$img_path = _load_image_to_edit_path( $post_id );
		$meta = wp_get_attachment_metadata( $post_id );
		$img_editor = wp_get_image_editor( $img_path );
		if (is_wp_error($img_editor))
			json_response([
				'status'=>'error',
				'message'=>'Could not create image editor.'
			]);

		$crop_size = ilab_get_image_sizes($size);
		$dest_width = intval($crop_size['width']);
		$dest_height = intval($crop_size['height']);

		if (empty($crop_size['crop'])) {
			$sz = sizeToFitSize(floatval($crop_width), floatval($crop_height), $dest_width ?: $dest_height, $dest_height ?: $dest_width);
			$dest_width = $sz[0];
			$dest_height = $sz[1];
		}

		$img_editor->crop($crop_x, $crop_y, $crop_width, $crop_height, $dest_width, $dest_height, false );
		$img_editor->set_quality($this->cropQuality);
		$save_path_parts = pathinfo($img_path);

		$path_url=parse_url($img_path);
		if (($path_url!==false) && (isset($path_url['scheme'])))
		{
			$parsed_path=pathinfo($path_url['path']);
			$img_subpath=apply_filters('media-cloud/storage/process-file-name',$parsed_path['dirname']);

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

		if ($reset_crop) {
			unset($meta['sizes'][$size]['crop']);
		}

		$img_editor->save($save_path . '/' . $filename);

		/** @var StorageTool $storageTool */
		$storageTool = ToolsManager::instance()->tools['storage'];

		if ($storageTool->enabled()) {
			// Let S3 upload the new crop
			$processedSize = $storageTool->processCrop($meta['sizes'][$size], $size, $save_path, $filename);
			if ($processedSize) {
				$meta['sizes'][$size] = $processedSize;
			}
        }

		wp_update_attachment_metadata($post_id, $meta);

		$attrs = wp_get_attachment_image_src($post_id, $size);
		list($full_src,$full_width,$full_height,$full_cropped)=$attrs;

		json_response([
			'status'=>'ok',
			'src'=>$full_src,
			'width'=>$full_width,
			'height'=>$full_height
		]);
	}
}