<?php
$termMode = \MediaCloud\Plugin\Utilities\Environment::Option('mcloud-replace-term-mode', null, 'replace');
?>
<div id="mcloud-simple-modal-{{$modalId}}" class="mcloud-simple-modal mcloud-replace-file-modal">
    <div class="mcloud-simple-modal-container">
        <div class="mcloud-simple-modal-title">
            <h1>Replace Image</h1>
            <a title="{{__('Close')}}" class="mcloud-simple-modal-close">
                <span class="ilabm-modal-icon"></span>
            </a>
        </div>
        <div class="mcloud-simple-modal-contents">
            <div class="mcloud-simple-modal-interior">
                <div class="mcloud-file-preview" style="display:none">
                </div>
                <div class="mcloud-term-mode-container mcloud-form-row mcloud-dual" style="display:none; margin-bottom: 15px;">
                    <label for="mcloud-term-mode">Terms/Tags Processing</label>
                    <select id="mcloud-term-mode" name="mcloud-term-mode">
                        <option value="replace" {{($termMode === 'replace') ? 'selected' : ''}}>Replace associated tags/terms</option>
                        <option value="merge" {{($termMode === 'merge') ? 'selected' : ''}}>Merge associated tags/terms</option>
                        <option value="nothing" {{($termMode === 'nothing') ? 'selected' : ''}}>Do nothing</option>
                    </select>
                </div>
                <div class="mcloud-form-row mcloud-file-picker">
                    <label for="mcloud-selected-file">
                        <span class="selected-file-text">Select file ...</span>
                        <span class="button button-primary select-button">Select File</span>
                    </label>
                    <input id="mcloud-selected-file" type="file" accept="image/png, image/jpeg, image/tiff, image/gif">
                </div>
                <div class="mcloud-form-row mcloud-upload-progress" style="display:none">
                    <div class="progress-text">Uploading ...</div>
                    <div class="progress-bar">
                        <div class="progress-bar-interior" style="width: 60%"></div>
                    </div>
                </div>
            </div>
            <div class="mcloud-form-button-row">
                <button disabled="disabled" type="button" class="button button-primary mcloud-start-upload">Start Upload</button>
            </div>
        </div>
    </div>
</div>
