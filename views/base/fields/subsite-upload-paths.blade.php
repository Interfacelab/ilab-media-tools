<?php
    /** @var WP_Site[] $sites */
    $sites = get_sites();
?>
<div id="setting-{{$name}}" {{(($conditions) ? 'data-conditions="true"' : '')}}>
    @if($description)
        <p class='description' style="margin-bottom: 20px">{!! $description !!}</p>
    @endif

    @foreach($sites as $site)
        <?php
            $currentValue = null;
            if (is_array($value) && isset($value[$site->blog_id])) {
                $currentValue = $value[$site->blog_id];
            }
        ?>
    <div class="subsite-setting-group">
        <div class="subsite-upload-path"><label for="{{$name}}-{{$site->blog_id}}">{{$site->blogname}}</label><input size='40' type='text' id="{{$name}}-{{$site->blog_id}}" name='{{$name}}[{{$site->blog_id}}]' value='{{$currentValue}}' placeholder='{{$placeholder}}'>
        </div>
        <div class="upload-path-preview">
            <span>Preview</span>
            <span id="{{$name}}-{{$site->blog_id}}-preview"></span>
        </div>
    </div>
    <script>
        (function($) {
            var uploadPathId = "#{{$name}}-{{$site->blog_id}}";
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
    @endforeach
</div>
