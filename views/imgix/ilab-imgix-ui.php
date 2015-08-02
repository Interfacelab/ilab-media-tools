<div id="ilab-modal-container">
    <div id="ilab-modal-titlebar">
        <h1>{{ __('Edit Image') }} ({{$full_width}} x {{$full_height}})</h1>
        <a title="{{__('Close')}}" href="javascript:ILabModal.cancel();" class="media-modal-close">
            <span class="media-modal-icon"></span>
        </a>
    </div>
    <div id="ilab-modal-window-area">
        <div id="ilab-modal-editor-container">
            <div id="ilab-modal-editor-area">
                <img id="ilab-imgix-preview-image" src="{{ $src }}" />
                <div id="ilab-preview-wait-modal" class="is-hidden">
                    <h3>Building Preview</h3>
                    <span class="spinner is-active"></span>
                </div>
            </div>
        </div>
        <div id="ilab-modal-sidebar">
            <div class="ilab-modal-tabs">
                {% foreach($params as $paramSection => $paramSectionInfo) %}
                <div class="ilab-modal-tab" data-target="ilab-imgix-params-section-{{$paramSection}}">{{__(ucwords(str_replace('-', ' ', $paramSection)))}}</div>
                {% endforeach %}
            </div>
            <div class="ilab-modal-sidebar-content">
                {% foreach($params as $paramSection => $paramSectionInfo) %}
                <div id="ilab-imgix-params-section-{{$paramSection}}" class="ilab-imgix-parameters-container is-hidden">
                    {% foreach($paramSectionInfo as $group => $groupParams) %}
                    <h4>{{$group}}</h4>
                    <div>
                    {% foreach($groupParams as $param => $paramInfo) %}
                    {% if ($paramInfo['type']=='slider') %}
                    <div class="ilab-imgix-parameter">
                        <div class="ilab-imgix-param-title">
                            <div class="ilab-imgix-param-title-left">
                                <h3>{{__($paramInfo['title'])}}</h3>
                            </div>
                            <div class="ilab-imgix-param-title-right">
                                <h3 id="imgix-current-value-{{$param}}" style="font-style:italic">12</h3>
                            </div>
                        </div>
                        <input data-param-type="{{$paramInfo['type']}}" data-default-value="{{$paramInfo['default']}}" class="imgix-param" name="{{$param}}" id="imgix-param-{{$param}}" type="range" min="{{$paramInfo['min']}}" max="{{$paramInfo['max']}}" value="0" />
                        <a class="imgix-param-reset" data-param-type="{{$paramInfo['type']}}" data-param="{{$param}}" href="#">{{__('Reset')}}</a>
                    </div>
                    {% elseif ($paramInfo['type']=='color') %}
                    <div class="ilab-imgix-parameter">
                        <div class="ilab-imgix-param-title">
                            <div class="ilab-imgix-param-title-left">
                                <h3>{{__($paramInfo['title'])}}</h3>
                            </div>
                            <div class="ilab-imgix-param-title-right">
                                <input class="imgix-param imgix-param-color" data-param-type="{{$paramInfo['type']}}" type="text" name="{{$param}}" id="imgix-param-{{$param}}" value="#FF0000">
                            </div>
                        </div>
                        <input class="imgix-param-alpha" id="imgix-param-alpha-{{$param}}" type="range" min="0" max="100" value="0" />
                        <a class="imgix-param-reset" data-param-type="{{$paramInfo['type']}}" data-param="{{$param}}" href="#">{{__('Reset')}}</a>
                    </div>
                    {% elseif ($paramInfo['type']=='blend-color') %}
                    <div class="ilab-imgix-parameter">
                        <div class="ilab-imgix-param-title">
                            <div class="ilab-imgix-param-title-left">
                                <h3>{{__($paramInfo['title'])}}</h3>
                            </div>
                            <div class="ilab-imgix-param-title-right">
                                <input class="imgix-param imgix-param-color" data-param-type="{{$paramInfo['type']}}" type="text" name="{{$param}}" id="imgix-param-{{$param}}" value="#FF0000">
                            </div>
                        </div>
                        <input class="imgix-param-alpha" id="imgix-param-alpha-{{$param}}" type="range" min="0" max="100" value="0" />
                        <div class="imgix-param-blend-mode">
                            <h3>Blend Mode</h3>
                            <select class="imgix-param-blend" id="imgix-param-blend-{{$param}}" data-blend-param="{{$paramInfo['blend-param']}}">
                                {% foreach($paramInfo['blends'] as $blendKey => $blendName) %}
                                <option value="{{$blendKey}}">{{$blendName}}</option>
                                {% endforeach %}
                            </select>
                        </div>
                        <a class="imgix-param-reset" data-param-type="{{$paramInfo['type']}}" data-param="{{$param}}" href="#">{{__('Reset')}}</a>
                    </div>
                    {% elseif ($paramInfo['type']=='media-chooser') %}
                    <div class="ilab-imgix-parameter">
                        <div class="ilab-imgix-param-title ilab-imgix-media-param-title">
                            <div class="ilab-imgix-param-title-left">
                                <h3>{{__($paramInfo['title'])}}</h3>
                            </div>
                            <div class="ilab-imgix-param-title-right">
                                <input type="hidden" class="imgix-param" name="{{$param}}" id="imgix-param-{{$param}}" >
                                <a data-param="{{$param}}"  href="#" class="button button-small button-primary imgix-media-button">Select</a>
                            </div>
                        </div>
                        <div class="ilab-imgix-media-preview">
                            <div class="ilab-imgix-media-preview-inner">
                                <img id="imgix-media-preview">
                            </div>
                        </div>
                        <a class="imgix-param-reset" data-param="{{$param}}" data-param-type="{{$paramInfo['type']}}" href="#">{{__('Remove')}}</a>
                    </div>
                    {% elseif ($paramInfo['type']=='alignment') %}
                    <div class="ilab-imgix-parameter">
                        <div class="ilab-imgix-param-title ilab-imgix-media-param-title">
                            <div class="ilab-imgix-param-title-left">
                                <h3>{{__($paramInfo['title'])}}</h3>
                            </div>
                            <div class="ilab-imgix-param-title-right"></div>
                        </div>
                        <div class="ilab-imgix-alignment-container">
                            <input type="hidden" class="imgix-param" name="{{$param}}" id="imgix-param-{{$param}}" >
                            <a href="#" class="imgix-alignment-button" data-param="{{$param}}" data-param-value="top,left"><img src="{{ILAB_PUB_IMG_URL}}/wm-align-top-left.png"></a>
                            <a href="#" class="imgix-alignment-button" data-param="{{$param}}" data-param-value="top,center"><img src="{{ILAB_PUB_IMG_URL}}/wm-align-top-center.png"></a>
                            <a href="#" class="imgix-alignment-button" data-param="{{$param}}" data-param-value="top,right"><img src="{{ILAB_PUB_IMG_URL}}/wm-align-top-right.png"></a>
                            <a href="#" class="imgix-alignment-button" data-param="{{$param}}" data-param-value="middle,left"><img src="{{ILAB_PUB_IMG_URL}}/wm-align-middle-left.png"></a>
                            <a href="#" class="imgix-alignment-button" data-param="{{$param}}" data-param-value="middle,center"><img src="{{ILAB_PUB_IMG_URL}}/wm-align-middle-center.png"></a>
                            <a href="#" class="imgix-alignment-button" data-param="{{$param}}" data-param-value="middle,right"><img src="{{ILAB_PUB_IMG_URL}}/wm-align-middle-right.png"></a>
                            <a href="#" class="imgix-alignment-button" data-param="{{$param}}" data-param-value="bottom,left"><img src="{{ILAB_PUB_IMG_URL}}/wm-align-bottom-left.png"></a>
                            <a href="#" class="imgix-alignment-button" data-param="{{$param}}" data-param-value="bottom,center"><img src="{{ILAB_PUB_IMG_URL}}/wm-align-bottom-center.png"></a>
                            <a href="#" class="imgix-alignment-button" data-param="{{$param}}" data-param-value="bottom,right"><img src="{{ILAB_PUB_IMG_URL}}/wm-align-bottom-right.png"></a>
                        </div>
                        <a class="imgix-param-reset" data-param="{{$param}}" data-param-type="{{$paramInfo['type']}}" href="#">{{__('Remove')}}</a>
                    </div>
                    {% endif %}
                    {% endforeach %}
                    </div>
                    {% endforeach %}
                </div>
                {% endforeach %}
            </div>
            <div class="ilab-modal-sidebar-actions">

                <a href="javascript:ILabImageEdit.apply();"
                   class="button media-button button-primary media-button-select">
                    {{__('Save Adjustments')}}
                </a>
                <a href="javascript:ILabImageEdit.resetAll();"
                   class="button media-button button-primary button-reset">
                    {{__('Reset All')}}
                </a>
                <span class="spinner"></span>
            </div>
        </div>
    </div>
</div>

<script>
    ILabImageEdit.init({
        image_id:{{$image_id}},
        settings:{{json_encode($settings,JSON_FORCE_OBJECT | JSON_PRETTY_PRINT)}}

    });
</script>