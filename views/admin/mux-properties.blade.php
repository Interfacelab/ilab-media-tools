<?php
/**
 * @var \MediaCloud\Plugin\Tools\Video\Driver\Mux\Models\MuxAsset $asset
 */
?>
@if(empty($asset))
    <script>
        document.getElementById('mcloud-mux-meta').remove();
    </script>
@else
    <div class="info-panel-contents">
        <div id="info-panel-tab-original">
            <div class="info-file-info">
                <div class="info-line">
                    <h3>Status</h3>
                    @if($asset->isTransferred)
                        Transferred to {{ ($asset->transferData['source'] === 'local') ? 'local server' : 'cloud storage' }}
                    @else
                        {{ucfirst($asset->status)}}
                    @endif
                </div>
                <div class="info-line">
                    <h3>Subtitles</h3>
                    @if(empty($asset->subtitles))
                    No subtitles
                    @else
                    <ul class="mux-asset-captions" data-nonce="{{wp_create_nonce('mux-delete-caption')}}" data-asset-id="{{$asset->id()}}">
                        @foreach($asset->subtitles as $subtitle)
                        <li data-track-id="{{isset($subtitle['id']) ? $subtitle['id'] : $loop->index }}">
                            @if((!$asset->isDeleted && !$asset->isTransferred) || $subtitle['local'])
                            <a href="#"><img class="logo" src="{{ILAB_PUB_IMG_URL}}/ilab-ui-icon-trash.svg"></a>
                            @endif
                            {{$subtitle['name']}} ({{$subtitle['language_code']}})
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>
            <div class="info-file-info" id="mux-captions-uploader" data-asset-id="{{$asset->id()}}" data-nonce="{{wp_create_nonce('mux-upload-caption')}}">
                <div class="info-line info-line-form">
                    <h3>Upload Subtitles</h3>
                    <div class="info-line-form-row">
                        <label for="mux-captions-language">Language</label>
                        <select id="mux-captions-language">
                            @foreach(\MediaCloud\Plugin\Tools\Video\Driver\Mux\Models\MuxAsset::$subtitleLanguages as $code => $language)
                            <option value='{{$code}}'>{{$language}}</option>
                            @endforeach'
                        </select>
                    </div>
                    <div class="info-line-form-row">
                        @if(!$asset->isDeleted && !$asset->isTransferred)
                        <label for="mux-captions-upload">Subtitle (SRT or VTT only)</label>
                        <input id="mux-captions-upload" type="file" accept=".srt,.vtt">
                        @else
                        <label for="mux-captions-upload">Subtitle (VTT only)</label>
                        <input id="mux-captions-upload" type="file" accept=".vtt">
                        @endif
                    </div>
                    <div class="info-line-form-row">
                        <input id="mux-captions-cc" type="checkbox">
                        <label for="mux-captions-cc">Closed Captions</label>
                    </div>
                    <div class="info-line-note">
                        @if(!$asset->isDeleted && !$asset->isTransferred)
                        Upload subtitles will not appear in this panel until after mux has processed them.  This may take 1-2 minutes.
                        @else
                        If you have uploaded subtitles through Mux, they will be replaced with what you upload here.
                        @endif
                    </div>
                    <div class="info-line-form-buttons">
                        <button type="button" class="button button-primary button-small upload-captions">Upload Captions</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
