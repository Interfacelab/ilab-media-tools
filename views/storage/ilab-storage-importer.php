<style>
	#s3-importer-progress {

	}

    #s3-importer-progress > button {
        margin-top: 20px;
    }

	.s3-importer-progress-container {
		display: relative;
		width: 100%;
		height: 18px;
		background: lightgray;
		border-radius: 8px;
		overflow: hidden;
	}

	#s3-importer-progress-bar {
		background-color: #3a84e6;
		height: 100%;
		width: {{$progress}}%;
	}

    .tool-disabled {
        padding: 10px 15px;
        border: 1px solid #df8403;
    }

    .force-cancel-help {
        margin-top: 20px;
    }

    .wp-cli-callout {
        padding: 10px;
        background-color: rgba(0,0,0,0.0625);
        margin-top: 20px;
        border-radius: 8px;
    }

    .wp-cli-callout > h3 {
        margin: 0; padding: 0;
        font-size: 14px;
    }
</style>
<div class="settings-container">
    <header>
        <img src="{{ILAB_PUB_IMG_URL}}/icon-cloud.svg">
        <h1>Storage Importer</h1>
    </header>
    <div class="settings-body">
        <div id="s3-importer-instructions" {{($status=="running") ? 'style="display:none"':''}}>
            <p>This tool will import any media and documents you are currently hosting on this server to your cloud storage service.</p>
            <p>Depending on the number of items you have, this could take anywhere from a minute to several hours.  This process runs in the background until it's finished.  Once you've started the process, please check this page for progress.</p>
            <p><strong>Note:</strong></p>
            <ol>
                <li>If you upload any files while this process is running, you'll need to run this tool again after it finishes.</li>
                <li>This process DOES NOT delete your files on your server, you'll have to do that yourself manually.</li>
                <li>You <strong>MUST HAVE</strong> storage enabled and working in <a href="admin.php?page=media-tools-top">Tools Settings</a> before running this task.</li>
            </ol>
            <div class="wp-cli-callout">
                <h3>Using WP-CLI</h3>
                <p>You can run this importer process from the command line using WP-CLI:</p>
                <code>
                    wp mediacloud import
                </code>
            </div>
            <div style="margin-top: 2em;">
                <?php if($enabled): ?>
                <a href="#" class="ilab-ajax button button-primary">Import Uploads</a>
                <?php else: ?>
                    <strong class="tool-disabled">Please <a href="admin.php?page=media-tools-top">enable storage</a> before using this tool.</strong>
                <?php endif ?>
            </div>
        </div>
        <div id="s3-importer-progress" {{($status!="running") ? 'style="display:none"':''}}>
            <div id="s3-importer-progress-text">
                <p id="s3-importer-cancelling-text" style="display:{{($shouldCancel) ? 'block':'none'}}">Cancelling ... This may take a minute ...</p>
                <p id="s3-importer-status-text" style="display:{{($shouldCancel) ? 'none':'block'}}">Storage importer is currently running.  Importing '<span id="s3-importer-current-file">{{$currentFile}}</span>' (<span id="s3-importer-current">{{$current}}</span> of <span id="s3-importer-total">{{$total}}</span>).</p>
            </div>
            <div class="s3-importer-progress-container">
                <div id="s3-importer-progress-bar"></div>
            </div>
            <button id="s3-importer-cancel-import" class="button button-warning" title="Cancel">Cancel Import</button>
        </div>
    </div>
</div>
<script>
    (function($){
        $(document).ready(function(){
            var importing={{($status == 'running') ? 'true' : 'false'}};

            $('#s3-importer-cancel-import').on('click', function(e){
               e.preventDefault();

               if (confirm("Are you sure you want to cancel the import?")) {
                   var data={
                       action: 'ilab_s3_cancel_import'
                   };

                   $.post(ajaxurl,data,function(response){
                       $('#s3-importer-cancelling-text').css({'display':'block'});
                       $('#s3-importer-status-text').css({'display':'none'});
                       $('#s3-importer-cancel-import').attr('disabled', true);
                       console.log(response);
                   });
               }

               return false;
            });

            $('.ilab-ajax').on('click',function(e){
                e.preventDefault();

                if (importing)
                    return false;


                importing=true;

                var data={
                    action: 'ilab_s3_import_media'
                };

                $.post(ajaxurl,data,function(response){
                    if (response.status == 'running') {
                        $('#s3-importer-cancel-import').attr('disabled', false);
                        $('#s3-importer-cancelling-text').css({'display':'none'});
                        $('#s3-importer-status-text').css({'display':'block'});

                        $('#s3-importer-instructions').css({display: 'none'});
                        $('#s3-importer-progress').css({display: 'block'});
                    }
                });
                return false;
            });

            var checkStatus = function() {
                if (importing) {
                    var data={
                        action: 'ilab_s3_import_progress'
                    };

                    $.post(ajaxurl,data,function(response){
                        if (response.shouldCancel) {
                            $('#s3-importer-cancelling-text').css({'display':'block'});
                            $('#s3-importer-status-text').css({'display':'none'});
                        } else {
                            $('#s3-importer-cancelling-text').css({'display':'none'});
                            $('#s3-importer-status-text').css({'display':'block'});
                        }

                        if (response.status != 'running') {
                            importing = false;
                            $('#s3-importer-instructions').css({display: 'block'});
                            $('#s3-importer-progress').css({display: 'none'});
                        } else {
                            if (response.total > 0) {
                                var progress = (response.current / response.total) * 100;
                                $('#s3-importer-progress-bar').css({width: progress+'%'});
                            }

                            $('#s3-importer-current').text(response.current);
                            $('#s3-importer-current-file').text(response.currentFile);
                            $('#s3-importer-total').text(response.total);
                        }
                    });
                }

                setTimeout(checkStatus, 3000);
            };

            setTimeout(checkStatus, 3000);
        });
    })(jQuery);
</script>