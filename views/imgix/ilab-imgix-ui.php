<div id="ilab-modal-wrapper">
    <div class="media-modal wp-core-ui">
        <a title="{{__('Close')}}" href="javascript:ILabModal.cancel();" class="media-modal-close">
            <span class="media-modal-icon"></span>
        </a>
        <div class="media-modal-content">
            <div class="media-frame wp-core-ui">
                <div class="media-frame-title"><h1>{{ __('Edit Image') }} ({{$full_width}} x {{$full_height}})</h1></div>
                <div class="media-frame-content ilab-imgix-media-frame-content">
                    <div class="attachments-browser">
                        <div class="attachments ilab-imgix-preview" style="padding:0px !important; text-align: center; margin-left: 10px; margin-right:10px; margin-bottom:10px; background-image:url({{ILAB_PUB_IMG_URL}}/ilab-imgix-edit-bg.png);">
                            {% if ($full_width<$full_height) %}
                            <img id="ilab-imgix-preview-image" src="{{ $src }}" style="height:100%; width:auto;" />
                            {% else %}
                            <img id="ilab-imgix-preview-image" src="{{ $src }}" style="width:100%; height:auto; margin:auto 0;" />
                            {% endif %}
                            <div id="ilab-preview-wait-modal" class="is-hidden">
                                <h3>Building Preview</h3>
                                <span class="spinner is-active"></span>
                            </div>
                        </div>
                        <div class="media-sidebar ilab-imgix-media-sidebar">
                            <div class="attachment-details ilab-imgix-attachment-details">
                                <div class="ilab-imgix-presets">
                                    <select>
                                        <option>My Preset</option>
                                    </select>

                                </div>
                                <div class="ilab-imgix-parameters-container">
                                    {% foreach($params as $param => $paramInfo) %}
                                    <div class="ilab-imgix-parameter">
                                        <h3>{{__($paramInfo['title'])}}</h3>
                                        <input data-default-value="{{$paramInfo['default']}}" class="imgix-param" name="{{$param}}" id="imgix-param-{{$param}}" type="range" min="{{$paramInfo['min']}}" max="{{$paramInfo['max']}}" value="0" />
                                        <a class="imgix-param-reset" data-param="{{$param}}" href="#">{{__('Reset')}}</a>
                                    </div>
                                    {% endforeach %}
<!--                                    <div class="ilab-imgix-parameter">-->
<!--                                        <h3>{{__('Sharpness')}}</h3>-->
<!--                                        <input data-default-value="0" class="imgix-param" name="sharp" id="imgix-param-sharp" type="range" min="0" max="100" value="0" />-->
<!--                                        <a class="imgix-param-reset" data-param="sharp" href="#">{{__('Reset')}}</a>-->
<!--                                    </div>-->
                                </div>
                                <div class="ilab-apply-now-wrapper">
                                    <a href="javascript:ILabImageEdit.apply();"
                                       class="button media-button button-primary button-large media-button-select">
                                        {{__('Save Adjustments')}}
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
    ILabImageEdit.init({
        image_id:{{$image_id}},
        settings:{{json_encode($settings,JSON_FORCE_OBJECT | JSON_PRETTY_PRINT)}}

    });
</script>