<style>
	#s3-importer-progress {

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
</style>
<div class="wrap">
	<h1>S3 Importer</h1>
	<div id="s3-importer-instructions" {{($status=="running") ? 'style="display:none"':''}}>
		<p>This tool will import any media and documents you are currently hosting on this server to S3.</p>
		<p>Depending on the number of items you have, this could take anywhere from a minute to several hours.  This process runs in the background until it's finished.  Once you've started the process, please check this page for progress.</p>
		<p><strong>Note:</strong></p>
		<ol>
			<li>If you upload any files while this process is running, you'll need to run this tool again after it finishes.</li>
			<li>This process DOES NOT delete your files on your server, you'll have to do that yourself manually.</li>
			<li>It's recommended that you have the S3 tool disabled in <a href="admin.php?page=media-tools-top">Tools Settings</a> before running this task.</li>
		</ol>
		<div style="margin-top: 2em;">
			<a href="#" class="ilab-ajax button">Import Media</a>
		</div>
	</div>
	<div id="s3-importer-progress" {{($status!="running") ? 'style="display:none"':''}}>
		<p>S3 importer is currently running.  Importing <span id="s3-importer-current">{{$current}}</span> of <span id="s3-importer-total">{{$total}}</span>.</p>
		<div class="s3-importer-progress-container">
			<div id="s3-importer-progress-bar"></div>
		</div>
	</div>
	<script>
		(function($){
			$(document).ready(function(){
				var importing={{($status == 'running') ? 'true' : 'false'}};

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
</div>
