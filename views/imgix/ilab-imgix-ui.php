<div id="ilab-modal-wrapper">
    <div class="media-modal wp-core-ui">
        <a title="{{__('Close')}}" href="javascript:ILabImgixEdit.cancel();" class="media-modal-close">
            <span class="media-modal-icon"></span>
        </a>
        <div class="media-modal-content">
            <div class="media-frame wp-core-ui">
                <div class="media-frame-title"><h1>{{ __('Crop Image') }} ({{$full_width}} x {{$full_height}})</h1></div>
                <div class="media-frame-content">
                    <div class="attachments-browser">
                        <div class="attachments">
                            <div id="ilab-crop-container" style="width:100%; height:100%;">
                                <img id="ilab-cropper" src="{{ $src }}" style="max-width:100%;" />
                            </div>
                        </div>
                        <div class="media-sidebar">
                            <div class="attachment-details">
                                <div class="ilab-crop-now-wrapper">
                                    <a href="javascript:ILabImgixEdit.apply();"
                                       class="button media-button button-primary button-large media-button-select">
                                        {{__('Apply')}}
                                    </a>
                                    <span class="spinner"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="yoimg-cropper-bckgr" class="media-modal-backdrop"></div>
</div>

<script>
    ILabImgixEdit.init({
        image_id:{{$image_id}}
    });
</script>