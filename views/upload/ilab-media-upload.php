
<div class="wrap">
	<h2>Upload Media</h2>
	<p>Drag and drop media onto this page to upload them directly to S3, bypassing uploading to WordPress.</p>
	<div id="ilab-video-upload-target">
		<div class="ilab-upload-directions">Drag and drop to upload.</div>
	</div>
</div>
<div id="ilab-attachment-info">
</div>
<div class="ilab-upload-footer">
    <button type="button" id="ilab-insert-button" class="button media-button button-primary button-large media-button-insert" disabled="disabled">Insert into post</button>
</div>
<?php include ILAB_VIEW_DIR.'/upload/ilab-media-upload-attachment-info.template.html' ?>
<script type="text/template" id="tmpl-ilab-upload-cell">
	<div class="ilab-upload-item">
		<div class="ilab-upload-item-background"></div>
		<div class="ilab-upload-status-container">
			<div class="ilab-upload-status">Uploading ...</div>
			<div class="ilab-upload-progress">
				<div class="ilab-upload-progress-track" style="width: 64%;"></div>
			</div>
		</div>
        <div class="ilab-loader-container" style="opacity:0;">
            <div class="ilab-loader"></div>
        </div>
	</div>
</script>
<script>
    (function($){
        $(document).on('ready',function(){
            new ilabMediaUploader($,{
                "insertMode": {{($insertMode) ? 'true' : 'false'}},
                "imgixEnabled": {{($imgixEnabled) ? 'true' : 'false'}},
                "videoEnabled": {{($videoEnabled) ? 'true' : 'false'}},
                "docsEnabled": {{($docUploads) ? 'true' : 'false'}},
                "extrasEnabled": {{($altFormats) ? 'true' : 'false'}},
                "allowedMimes": {{json_encode($allowedMimes, JSON_PRETTY_PRINT)}}
            });
        });
    })(jQuery);
</script>