<?php
/**
 * @var \MediaCloud\Plugin\Tools\Video\Driver\Mux\Models\MuxAsset $asset
 */
?>
@if(empty($hasVideo))
    <script>
        document.getElementById('mcloud-mux-meta').remove();
    </script>
@else
    <div class="info-panel-contents">
        <div id="info-panel-tab-original">
            <div class="info-file-info">
                <div class="info-line">
                    <h3>Subtitles</h3>
                    @if(empty($subtitles))
                    No subtitles
                    @else
                    <ul class="mux-asset-captions" data-nonce="{{wp_create_nonce('mux-delete-caption')}}" data-attachment-id="{{$attachmentId}}">
                        @foreach($subtitles as $subtitle)
                        <li data-language-code="{{ $subtitle['language_code'] }}">
                            <a href="#"><img class="logo" src="{{ILAB_PUB_IMG_URL}}/ilab-ui-icon-trash.svg"></a>
                            {{$subtitle['name']}} ({{$subtitle['language_code']}})
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>
            <div class="info-file-info" id="mux-captions-uploader" data-attachment-id="{{$attachmentId}}" data-nonce="{{wp_create_nonce('mux-upload-caption')}}">
                <div class="info-line info-line-form">
                    <h3>Upload Subtitles</h3>
                    <div class="info-line-form-row">
                        <label for="mux-captions-language">Language</label>
                        <select id="mux-captions-language">
                            @foreach(\MediaCloud\Plugin\Tools\Video\VideoEncoderConsts::$simpleSubtitleLanguages as $code => $language)
                            <option value='{{$code}}'>{{$language}}</option>
                            @endforeach'
                        </select>
                    </div>
                    <div class="info-line-form-row">
                        <label for="mux-captions-upload">Subtitle (VTT only)</label>
                        <input id="mux-captions-upload" type="file" accept=".vtt">
                    </div>
                    <div class="info-line-form-row">
                        <input id="mux-captions-cc" type="checkbox">
                        <label for="mux-captions-cc">Closed Captions</label>
                    </div>
                    <div class="info-line-form-buttons">
                        <button type="button" class="button button-primary button-small upload-captions">Upload Captions</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
