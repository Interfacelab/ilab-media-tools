<?php
$imageValue = \MediaCloud\Plugin\Utilities\Environment::Option('mcloud-storage-privacy-images', null, 'inherit');
$audioValue = \MediaCloud\Plugin\Utilities\Environment::Option('mcloud-storage-privacy-audio', null, 'inherit');
$videoValue = \MediaCloud\Plugin\Utilities\Environment::Option('mcloud-storage-privacy-video', null, 'inherit');
$docValue = \MediaCloud\Plugin\Utilities\Environment::Option('mcloud-storage-privacy-docs', null, 'inherit');
?>

<div class="privacy-container" id="setting-{{$name}}" {{(($conditions) ? 'data-conditions="true"' : '')}}>
    <div>
        <div class="privacy-label">Image Privacy</div>
        <div>
            <div id="setting-mcloud-storage-privacy-images">
                <select id="mcloud-storage-privacy-images" name="mcloud-storage-privacy-images">
                    <option value="inherit" {{($imageValue === 'inherit') ? 'selected' : ''}}>Inherit</option>
                    <option value="public-read" {{($imageValue === 'public-read') ? 'selected' : ''}}>Public</option>
                    <option value="authenticated-read" {{($imageValue === 'authenticated-read') ? 'selected' : ''}}>Authenticated Read</option>
                    <option value="private" {{($imageValue === 'private') ? 'selected' : ''}}>Private</option>
                </select>
                <p class="description">This will set the privacy for image uploads.</p>
            </div>
        </div>
    </div>
    <div>
        <div class="privacy-label">Video Privacy</div>
        <div>
            <div id="setting-mcloud-storage-privacy-video">
                <select id="mcloud-storage-privacy-video" name="mcloud-storage-privacy-video">
                    <option value="inherit" {{($videoValue === 'inherit') ? 'selected' : ''}}>Inherit</option>
                    <option value="public-read" {{($videoValue === 'public-read') ? 'selected' : ''}}>Public</option>
                    <option value="authenticated-read" {{($videoValue === 'authenticated-read') ? 'selected' : ''}}>Authenticated Read</option>
                    <option value="private" {{($videoValue === 'private') ? 'selected' : ''}}>Private</option>
                </select>
                <p class="description">This will set the privacy for video uploads.</p>
            </div>
        </div>
    </div>
    <div>
        <div class="privacy-label">Audio Privacy</div>
        <div>
            <div id="setting-mcloud-storage-privacy-audio">
                <select id="mcloud-storage-privacy-audio" name="mcloud-storage-privacy-audio">
                    <option value="inherit" {{($audioValue === 'inherit') ? 'selected' : ''}}>Inherit</option>
                    <option value="public-read" {{($audioValue === 'public-read') ? 'selected' : ''}}>Public</option>
                    <option value="authenticated-read" {{($audioValue === 'authenticated-read') ? 'selected' : ''}}>Authenticated Read</option>
                    <option value="private" {{($audioValue === 'private') ? 'selected' : ''}}>Private</option>
                </select>
                <p class="description">This will set the privacy for audio uploads.</p>
            </div>
        </div>
    </div>
    <div>
        <div class="privacy-label">Document Privacy</div>
        <div>
            <div id="setting-mcloud-storage-privacy-docs">
                <select id="mcloud-storage-privacy-docs" name="mcloud-storage-privacy-docs">
                    <option value="inherit" {{($docValue === 'inherit') ? 'selected' : ''}}>Inherit</option>
                    <option value="public-read" {{($docValue === 'public-read') ? 'selected' : ''}}>Public</option>
                    <option value="authenticated-read" {{($docValue === 'authenticated-read') ? 'selected' : ''}}>Authenticated Read</option>
                    <option value="private" {{($docValue === 'private') ? 'selected' : ''}}>Private</option>
                </select>
                <p class="description">This will set the privacy for document uploads.</p>
            </div>
        </div>
    </div>
</div>
@if($conditions)
    <script id="{{$name}}-conditions" type="text/plain">
        {!! json_encode($conditions, JSON_PRETTY_PRINT) !!}
    </script>
@endif