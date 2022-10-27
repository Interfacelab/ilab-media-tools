<div id="setting-{{$name}}" {{(($conditions) ? 'data-conditions="true"' : '')}}>
	<input size='40' type='text' id="{{$name}}" name='{{$name}}' value='{{$value}}' placeholder='{{$placeholder}}'>
    <div class="upload-path-preview">
        <span>Preview</span>
        <span id="{{$name}}-preview"></span>
    </div>
	@if($description)
	<p class='description'>{!! $description !!}</p>
	@endif
    @if($conditions)
    <script id="{{$name}}-conditions" type="text/plain">
        {!! json_encode($conditions, JSON_PRETTY_PRINT) !!}
    </script>
    @endif

    <script>
        (function($) {
            var uploadPathId = "#{{$name}}";
            var uploadPathNonce = "{{wp_create_nonce('mcloud-preview-upload-path')}}";

            var updating = false;
            var needsUpdate = false;

            var updatePreview = function() {
                if (updating) {
                    needsUpdate = true;
                    return;
                }

                updating = true;

                $.post(ajaxurl, {
                    'action': 'mcloud_preview_upload_path',
                    'nonce': uploadPathNonce,
                    'prefix': $(uploadPathId).val() },
                    (response) => {
                        if (response.hasOwnProperty('path')) {
                            $(uploadPathId+'-preview').text(response.path);
                        }

                        updating = false;

                        if (needsUpdate) {
                            needsUpdate = false;
                            updatePreview();
                        }
                    })
                    .fail((response) => {
                        updating = false;

                        if (needsUpdate) {
                            needsUpdate = false;
                            updatePreview();
                        }
                    });
            };

            var updateTimeout = null;
            $(uploadPathId).on('keyup', function() {
                clearTimeout(updateTimeout);
                updateTimeout = setTimeout(() => {
                    updatePreview();
                }, 500);
            });

            updatePreview();
        })(jQuery);
    </script>
</div>
