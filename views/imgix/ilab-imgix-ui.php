{% extends base/ilab-modal.php %}

{% block title %}
{{ __('Edit Image') }} ({{$full_width}} x {{$full_height}})
{% end block %}

{% block main-tabs %}
<div class="ilab-modal-editor-tabs">
    {% if (count($sizes)>10) %}
    <div class="imgix-image-size-label">Size:</div>
    <select class="imgix-image-size-select">
        <option value="{{$tool->editPageURL($image_id,'full',true) }}" {{(($size=='full')?'selected':'')}}>Source Image</option>
        {% foreach ($sizes as $name => $info) %}
        <option value="{{$tool->editPageURL($image_id,$name,true) }}" {{(($size==$name)?'selected':'')}}>{{ ucwords(str_replace('_', ' ', str_replace('-', ' ', $name))) }}</option>
        {% endforeach %}
    </select>
    {% else %}
    <div data-url="{{$tool->editPageURL($image_id,'full',true) }}"  class="ilab-modal-editor-tab {{(($size=='full')?'active-tab':'')}}">Source Image</div>
    {% foreach ($sizes as $name => $info) %}
    <div data-url="{{$tool->editPageURL($image_id,$name,true) }}" class="ilab-modal-editor-tab {{(($size==$name)?'active-tab':'')}}">{{ ucwords(str_replace('_', ' ', str_replace('-', ' ', $name))) }}</div>
    {% endforeach %}
    {% endif %}
</div>
{% end block %}

{% block editor %}
<img id="ilab-imgix-preview-image" src="{{ $src }}" />
<div id="ilab-preview-wait-modal" class="is-hidden">
    <h3>Building Preview</h3>
    <span class="spinner is-active"></span>
</div>
{% endblock %}

{% block sidebar-tabs %}
<div class="ilab-modal-tabs">
    {% for each($params as $paramSection => $paramSectionInfo) %}
    <div class="ilab-modal-tab" data-target="ilab-imgix-params-section-{{$paramSection}}">{{__(ucwords(str_replace('-', ' ', $paramSection)))}}</div>
    {% end for each %}
</div>
{% endblock %}

{% block bottom-bar %}
<div class="ilab-modal-bottom-bar">
    <div id="imgix-status-container" class="is-hidden">
        <span class="spinner is-active"></span>
        <span id="imgix-status-label">Saving ...</span>
    </div>
    <div id="imgix-preset-make-default-container">
        <label for="imgix-preset-make-default">
            <input name="imgix-preset-make-default" id="imgix-preset-make-default" type="checkbox">
            Make Default For Size
        </label>
        <div class="ilab-bottom-bar-seperator"></div>
    </div>
    <a href="javascript:ILabImageEdit.newPreset()" class="button">New Preset</a>
    <div id="imgix-preset-container">
        <div class="ilab-bottom-bar-seperator"></div>
        <select id="imgix-presets">
            <option>Preset 1</option>
        </select>
        <a href="javascript:ILabImageEdit.savePreset()" class="button button-primary">Save Preset</a>
        <a href="javascript:ILabImageEdit.deletePreset()" class="button button-reset">Delete Preset</a>
    </div>
</div>
{% end block %}

{% block sidebar-content %}
<div class="ilab-modal-sidebar-content">
    {% for each($params as $paramSection => $paramSectionInfo) %}
    <div id="ilab-imgix-params-section-{{$paramSection}}" class="ilab-imgix-parameters-container is-hidden">
        {% if ($paramSection=='adjust') %}
        <div class="ilab-modal-pillbox">
            <input type="hidden" data-param-type="hidden" data-default-value="0" class="imgix-param" name="enhance" id="imgix-param-enhance" value="{{imgixCurrentValue('enhance',$settings,0)}}">
            <input type="hidden" data-param-type="hidden" data-default-value="0" class="imgix-param" name="redeye" id="imgix-param-redeye" value="{{imgixCurrentValue('redeye',$settings,0)}}">
            <a data-param="enhance" id="imgix-pill-enhance" class="ilab-imgix-pill ilab-imgix-pill-enhance {{imgixIsSelected('enhance',$settings,1,0,'pill-selected')}}" href="#">Auto Enhance</a>
            <a data-param="redeye" id="imgix-pill-redeye" class="ilab-imgix-pill ilab-imgix-pill-redeye {{imgixIsSelected('redeye',$settings,1,0,'pill-selected')}}" href="#">Remove Red Eye</a>
        </div>
        {% endif %}
        {% for each($paramSectionInfo as $group => $groupParams) %}
        <h4>{{$group}}</h4>
        <div>
            {% foreach($groupParams as $param => $paramInfo) %}
                {% if ($paramInfo['type']=='slider') %}
                    {% include imgix/editors/imgix-slider.php %}
                {% elseif ($paramInfo['type']=='color') %}
                    {% include imgix/editors/imgix-color.php %}
                {% elseif ($paramInfo['type']=='blend-color') %}
                    {% include imgix/editors/imgix-blend-color.php %}
                {% elseif ($paramInfo['type']=='media-chooser') %}
                    {% include imgix/editors/imgix-media-chooser.php %}
                {% elseif ($paramInfo['type']=='alignment') %}
                    {% include imgix/editors/imgix-alignment.php %}
                {% endif %}
            {% endforeach %}
        </div>
        {% endforeach %}
    </div>
    {% endforeach %}
</div>
{% endblock %}

{% block sidebar-actions %}
<div class="ilab-modal-sidebar-actions">
    <a href="javascript:ILabImageEdit.resetAll();"
       class="button media-button button-primary button-reset">
        {{__('Reset All')}}
    </a>
    <a href="javascript:ILabImageEdit.apply();"
       class="button media-button button-primary media-button-select">
        {{__('Save Adjustments')}}
    </a>
    <span class="spinner is-hidden"></span>
</div>
{% endblock %}

{% block script %}
<script>
    ILabImageEdit.init({
        image_id:{{$image_id}},
        size:"{{$size}}",
        currentPreset:"{{$currentPreset}}",
        presets:{{json_encode($presets,JSON_FORCE_OBJECT | JSON_PRETTY_PRINT)}},
        settings:{{json_encode($settings,JSON_FORCE_OBJECT | JSON_PRETTY_PRINT)}}
    });
</script>
{% endblock %}
