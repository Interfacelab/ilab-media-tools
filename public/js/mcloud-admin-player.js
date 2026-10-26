window.OldMediaElementPlayer=window.MediaElementPlayer;window.MediaElementPlayer=function(e,l){if(e.tagName!=="VIDEO")new window.OldMediaElementPlayer(e,l);else if(e.parentElement&&e.parentElement.classList.contains("wp-media-wrapper")&&(e.parentElement.style.width=""),window.bindHLSPlayer){const a=e.getElementsByTagName("source");for(let t=0;t<a.length;t++)a[t].dataset.mcloudSrc&&(a[t].src=a[t].dataset.mcloudSrc);window.bindHLSPlayer(e)}};document.addEventListener("DOMContentLoaded",()=>{const e=l=>{const a=/\<video(?:[^>]+)wp-video-shortcode\"(?:.*)\s+(?:.*)\s+(?:.*)\s+(?:.*)\s+(?:.*)\s+<\/video>/gm;let t=jQuery(l);if(t){let d=t.text();d=d.replace(a,`
					<video controls="controls" preload="metadata"
						<# if ( data.width && data.height ) { #>style="background-color:#555; max-height: 88vh; width: 100%; aspect-ratio: {{data.width}}/{{data.height}}"
						<# } else { #>style="background-color:#555;"<# } #>
						<# if ( data.image && data.image.src !== data.icon ) { #>poster="{{ data.image.src }}"<# } #>>
							<# if (data.mux && data.mux.url) { #>
								<source type="application/vnd.apple.mpegurl" data-mcloud-src="{{ data.mux.url }}" />
							<# } else if (data.hls) { #>
								<source type="application/vnd.apple.mpegurl" data-mcloud-src="{{data.hls.url}}" />
							<# } #>
						 <# if (data.mime === 'video/mp4') { #>
							<source type="{{ data.mime }}" data-mcloud-src="{{ data.url }}" />
						 <# } #>
						 <# if (data.hls && data.hls.subtitles) { #>
							<# _.each(data.hls.subtitles, subtitle => { #>
								<track kind="<# if (subtitle.cc) { #>captions<# } else { #>subtitles<# } #>" src="{{ subtitle.url }}" srclang="{{ subtitle.language_code }}" label="{{ subtitle.name }}" />
							<# }); #>
						<# } #>
					</video>`),t.text(d)}};e("#tmpl-attachment-details-two-column"),e("#tmpl-attachment-details")});
