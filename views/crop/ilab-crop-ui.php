{% if (!$partial) %}
    <div id="ilab-modal-wrapper">
        <div class="media-modal wp-core-ui">
            <a title="{{__('Close')}}" href="javascript:ILabCrop.cancel();" class="media-modal-close">
                <span class="media-modal-icon"></span>
            </a>
            <div class="media-modal-content">
{% endif %}
                <div class="media-frame wp-core-ui">
                    <div class="media-frame-title"><h1>{{ __('Crop Image') }} ({{$full_width}} x {{$full_height}})</h1></div>
                    <div class="media-frame-router">
                        <div class="media-router">
                            {% foreach($sizes as $name => $info) %}
                            {% if ($info['crop']==1) %}
                            <?php
                            $is_current_size = ($name === $size);
                            $anchor_class=($is_current_size) ? 'active':'';
                            ?>
                            <a href="{{ $tool->crop_page_url($image_id,$name,true) }}" class="media-menu-item ilab-thickbox ilab-thickbox-partial {{$anchor_class}}">{{ ucwords(str_replace('-', ' ', $name)) }}</a>
                            {% endif %}
                            {% endforeach %}
                        </div>
                    </div>
                    <div class="media-frame-content">
                        <div class="attachments-browser">
                            <div class="attachments">
                                <div id="ilab-crop-container" style="width:100%; height:100%;">
                                    <img id="ilab-cropper" src="{{ $src }}" style="max-width:100%;" />
                                </div>
                            </div>
                            <div class="media-sidebar">
                                <div class="attachment-details">
                                    {% if ($crop_exists) %}
                                        <h3>{{ __('Current')}} {{(ucwords(str_replace('-', ' ', $size)))}} ({{$cropped_width}} x {{$cropped_height}})</h3>
                                        <img id="ilab-current-crop-img" src="{{$cropped_src}}" style="width: 100%; height: auto;" />
                                    {% else %}
                                        <h3>{{ __('Current')}} {{(ucwords(str_replace('-', ' ', $size)))}} ({{$crop_width}} x {{$crop_height}})</h3>
                                        <img id="ilab-current-crop-img" style="width: 100%; height: auto;" />
                                        <div class="ilab-not-existing-crop">
                                            <div class="message error">
                                                <p>{{ __('Crop not generated yet, use the crop button here below to generate it')}}</p>
                                            </div>
                                        </div>
                                    {% endif %}
                                    <h3 id="ilab-crop-preview-title">{{ __( 'Crop preview') }}</h3>
                                    <div id="ilab-crop-preview"></div>
                                    <div class="ilab-crop-now-wrapper">
                                        <a href="javascript:ILabCrop.crop();"
                                           class="button media-button button-primary button-large media-button-select">
                                            {{__('Crop')}} {{(ucwords(str_replace('-', ' ', $size)))}}
                                        </a>
                                        <span class="spinner"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
{% if (!$partial) %}
            </div>
        </div>
        <div id="yoimg-cropper-bckgr" class="media-modal-backdrop"></div>
    </div>
{% endif %}

<script>
    ILabCrop.init({
        image_id:{{$image_id}},
        size:'{{ $size}}',
        min_width:{{$crop_width}},
        min_height:{{$crop_height}},
        aspect_ratio:{{ $ratio }},
        prev_crop_x:{{($prev_crop_x!==null) ? $prev_crop_x : 'undefined'}},
        prev_crop_y:{{($prev_crop_y!==null) ? $prev_crop_y : 'undefined'}},
        prev_crop_width:{{($prev_crop_width!==null) ? $prev_crop_width : 'undefined'}},
        prev_crop_height:{{($prev_crop_height!==null) ? $prev_crop_height : 'undefined'}}
    });
</script>