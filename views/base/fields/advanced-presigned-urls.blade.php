<div class="presigned-url-container">
    <div>
        <div class="presigned-label">Pre-Sign Image URLs</div>
        <div>
            @include('base.fields.checkbox', ['name' => 'mcloud-storage-use-presigned-urls-images', 'conditions' => '', 'description' => 'Enable to generate signed URLs for images that will expire within a specified time period.  If <strong>Use Pre-Signed URLs</strong> is enabled, this setting is ignored.', 'value' => \MediaCloud\Plugin\Utilities\Environment::Option('mcloud-storage-use-presigned-urls-images', null, false)])
        </div>
    </div>
    <div>
        <div class="presigned-label">Image URL Expiration</div>
        <div>
            <div id="setting-mcloud-storage-presigned-expiration" data-conditions="&quot;true&quot;">
                <input size="40" type="number" id="mcloud-storage-presigned-expiration-images" min="0" max="1000" step="1" name="mcloud-storage-presigned-expiration-images" value="{{\MediaCloud\Plugin\Utilities\Environment::Option('mcloud-storage-presigned-expiration-images', null, 0)}}">
                <p class="description">The number of minutes the signed URL is valid for.  If set to <strong>0</strong>, the default <strong>Pre-Signed URL Expiration</strong> will be used.</p>
            </div>
        </div>
    </div>
    <div>
        <div class="presigned-label">Pre-Sign Video URLs</div>
        <div>
            @include('base.fields.checkbox', ['name' => 'mcloud-storage-use-presigned-urls-video', 'conditions' => '', 'description' => 'Enable to generate signed URLs for video files that will expire within a specified time period.  If <strong>Use Pre-Signed URLs</strong> is enabled, this setting is ignored.', 'value' => \MediaCloud\Plugin\Utilities\Environment::Option('mcloud-storage-use-presigned-urls-video', null, false)])
        </div>
    </div>
    <div>
        <div class="presigned-label">Video URL Expiration</div>
        <div>
            <div id="setting-mcloud-storage-presigned-expiration" data-conditions="&quot;true&quot;">
                <input size="40" type="number" id="mcloud-storage-presigned-expiration-video" min="0" max="1000" step="1" name="mcloud-storage-presigned-expiration-video" value="{{\MediaCloud\Plugin\Utilities\Environment::Option('mcloud-storage-presigned-expiration-video', null, 0)}}">
                <p class="description">The number of minutes the signed URL is valid for.  If set to <strong>0</strong>, the default <strong>Pre-Signed URL Expiration</strong> will be used.</p>
            </div>
        </div>
    </div>
    <div>
        <div class="presigned-label">Pre-Sign Audio URLs</div>
        <div>
            @include('base.fields.checkbox', ['name' => 'mcloud-storage-use-presigned-urls-audio', 'conditions' => '', 'description' => 'Enable to generate signed URLs for audio files that will expire within a specified time period.  If <strong>Use Pre-Signed URLs</strong> is enabled, this setting is ignored.', 'value' => \MediaCloud\Plugin\Utilities\Environment::Option('mcloud-storage-use-presigned-urls-audio', null, false)])
        </div>
    </div>
    <div>
        <div class="presigned-label">Audio URL Expiration</div>
        <div>
            <div id="setting-mcloud-storage-presigned-expiration" data-conditions="&quot;true&quot;">
                <input size="40" type="number" id="mcloud-storage-presigned-expiration-audio" min="0" max="1000" step="1" name="mcloud-storage-presigned-expiration-audio" value="{{\MediaCloud\Plugin\Utilities\Environment::Option('mcloud-storage-presigned-expiration-audio', null, 0)}}">
                <p class="description">The number of minutes the signed URL is valid for.  If set to <strong>0</strong>, the default <strong>Pre-Signed URL Expiration</strong> will be used.</p>
            </div>
        </div>
    </div>
    <div>
        <div class="presigned-label">Pre-Sign Document URLs</div>
        <div>
            @include('base.fields.checkbox', ['name' => 'mcloud-storage-use-presigned-urls-docs', 'conditions' => '', 'description' => 'Enable to generate signed URLs for audio files that will expire within a specified time period.  If <strong>Use Pre-Signed URLs</strong> is enabled, this setting is ignored.', 'value' => \MediaCloud\Plugin\Utilities\Environment::Option('mcloud-storage-use-presigned-urls-docs', null, false)])
        </div>
    </div>
    <div>
        <div class="presigned-label">Document URL Expiration</div>
        <div>
            <div id="setting-mcloud-storage-presigned-expiration" data-conditions="&quot;true&quot;">
                <input size="40" type="number" id="mcloud-storage-presigned-expiration-docs" min="0" max="1000" step="1" name="mcloud-storage-presigned-expiration-docs" value="{{\MediaCloud\Plugin\Utilities\Environment::Option('mcloud-storage-presigned-expiration-docs', null, 0)}}">
                <p class="description">The number of minutes the signed URL is valid for.  If set to <strong>0</strong>, the default <strong>Pre-Signed URL Expiration</strong> will be used.</p>
            </div>
        </div>
    </div>
</div>
@if($conditions)
    <script id="{{$name}}-conditions" type="text/plain">
        {!! json_encode($conditions, JSON_PRETTY_PRINT) !!}
    </script>
@endif