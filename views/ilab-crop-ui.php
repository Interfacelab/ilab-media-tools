{% if (!$partial) %}
    <div id="ilab-crop-wrapper">
        <div class="media-modal wp-core-ui">
            <a title="{{__('Close')}}" href="#" class="media-modal-close">
                <span class="media-modal-icon"></span>
            </a>
            <div class="media-modal-content">
{% endif %}
                <div class="media-frame wp-core-ui">
                    <div class="media-frame-title"><h1>{{ __('Crop Image') }}</h1></div>
                    <div class="media-frame-router">
                        <div class="media-router">
                            {% foreach($sizes as $name => $info) %}
                            {% if ($info['crop']==1) %}
                            <?php
                            $is_current_size = ($name === $size);
                            $anchor_class=($is_current_size) ? 'active':'';
                            ?>
                            <a href="{{ $tool->crop_url($image_id,$name,true) }}" class="media-menu-item ilab-thickbox ilab-thickbox-partial {{$anchor_class}}">{{ ucwords(str_replace('-', ' ', $size_key)) }}</a>
                            {% endif %}
                            {% endforeach %}
                        </div>
                    </div>
                    <div class="media-frame-content">
                        <div class="attachments-browser">
                            <div class="attachments">
                                {{$size}}
                            </div>
                            <div class="media-sidebar">
                                <div class="attachment-details">
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
