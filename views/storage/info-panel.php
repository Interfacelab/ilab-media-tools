<div class="info-panel-tabs">
    <ul>
        <li data-tab-target="info-panel-tab-original" class="active">Original File</li>
        <li data-tab-target="info-panel-tab-sizes">Other Sizes ({{count($sizes)}})</li>
    </ul>
</div>
<div class="info-panel-contents">
    <div id="info-panel-tab-original">
        <div class="info-file-info">
	        <?php
	        echo \ILAB\MediaCloud\Utilities\View::render_view('storage/info-file-info.php', [
		        'uploaded' => 1,
		        'bucket' => $bucket,
		        'postId' => $postId,
		        'key' => $key,
		        'privacy' => $privacy,
		        'cacheControl' => $cacheControl,
		        'expires' => $expires,
		        'url' => $url,
		        'publicUrl' => $publicUrl,
                'width' => $width,
                'height' => $height,
		        'driverName' => $driverName,
		        'bucketLink' => $bucketLink,
		        'pathLink' => $pathLink,
                'isSize' => false
	        ]);
            ?>
        </div>
    </div>
    <div id="info-panel-tab-sizes" style="display: none;">
        <div class="info-line info-size-selector">
            <label for="ilab-other-sizes">WordPress Size</label>
            <select id="ilab-other-sizes" name="ilab-other-sizes">
                {% foreach($sizes as $key => $size) %}
                <option value="{{$key}}">{{$size['name']}}</option>
                {% endforeach %}
            </select>
        </div>
        <?php $firstSize = true; ?>
        {% foreach($sizes as $key => $size) %}
        <div id="info-size-{{$key}}" class="info-file-info info-file-info-size" style="{{(!$firstSize) ? 'display:none': ''}}">
		    <?php echo \ILAB\MediaCloud\Utilities\View::render_view('storage/info-file-info.php', $size); ?>
        </div>
        <?php $firstSize = false; ?>
        {% endforeach %}
    </div>
    {% if (!$imgixEnabled) %}
    <div class="button-row">
        <a data-post-id="{{$postId}}" data-imgix-enabled="{{($imgixEnabled) ? 'true': 'false'}}" href="#" class="ilab-info-regenerate-thumbnails button button-warning button-small">Regenerate Image</a>
        <div id="ilab-info-regenerate-status" style="display:none;"><div class="spinner is-active"></div>Regenerating ...</div>
    </div>
    {% endif %}
</div>
