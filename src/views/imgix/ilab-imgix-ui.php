{% extends base/ilab-modal.php %}

{% block title %}
{{ __('Edit Image') }} ({{$full_width}} x {{$full_height}})
{% end block %}

{% block main-tabs %}
<div class="ilab-modal-editor-tabs">
    <div class="ilab-modal-tabs-select-label">Size:</div>
    <select class="ilab-modal-tabs-select">
        <option value="full" data-url="{{$tool->editPageURL($image_id,'full',true) }}" {{(($size=='full')?'selected':'')}}>Source Image</option>
        {% foreach ($sizes as $name => $info) %}
        <option value="{{$name}}" data-url="{{$tool->editPageURL($image_id,$name,true) }}" {{(($size==$name)?'selected':'')}}>{{ ucwords(str_replace('_', ' ', str_replace('-', ' ', $name))) }}</option>
        {% endforeach %}
    </select>
    <div data-url="{{$tool->editPageURL($image_id,'full',true) }}" data-value="full" class="ilab-modal-editor-tab {{(($size=='full')?'active-tab':'')}}">Source Image</div>
    {% foreach ($sizes as $name => $info) %}
    <div data-url="{{$tool->editPageURL($image_id,$name,true) }}" data-value="{{$name}}" class="ilab-modal-editor-tab {{(($size==$name)?'active-tab':'')}}">{{ ucwords(str_replace('_', ' ', str_replace('-', ' ', $name))) }}</div>
    {% endforeach %}
</div>
{% end block %}

{% block editor %}
<img class="ilab-imgix-preview-image" src="{{ $src }}" />
<div class="ilab-preview-wait-modal is-hidden">
    <h3>Building Preview</h3>
    <span class="spinner is-active"></span>
</div>
{% endblock %}

{% block bottom-bar %}
<div class="imgix-preset-make-default-container">
    <label for="imgix-preset-make-default">
        <input name="imgix-preset-make-default" class="imgix-preset-make-default" type="checkbox">
        Make Default For Size
    </label>
    <div class="ilab-bottom-bar-seperator"></div>
</div>
<a href="#" class="button imgix-new-preset-button">New Preset</a>
<div class="imgix-preset-container">
    <div class="ilab-bottom-bar-seperator"></div>
    <select class="imgix-presets">
        <option>Preset 1</option>
    </select>
    <a href="#" class="button button-primary imgix-save-preset-button">Save Preset</a>
    <a href="#" class="button button-reset imgix-delete-preset-button">Delete Preset</a>
</div>
{% end block %}

{% block sidebar-content %}
<div class="ilab-sidebar-tabs">
    {% for each($params as $paramSection => $paramSectionInfo) %}
    <div class="ilab-sidebar-tab" data-target="ilab-imgix-params-section-{{$paramSection}}">{{__(ucwords(str_replace('-', ' ', $paramSection)))}}</div>
    {% end for each %}
</div>
<div class="ilab-modal-sidebar-content">
    {% for each($params as $paramSection => $paramSectionInfo) %}
    <div class="ilab-imgix-params-section-{{$paramSection}} ilab-imgix-parameters-container is-hidden">
        {% for each($paramSectionInfo as $group => $groupParams) %}
        {% if (strpos($group,'--')!==0) %}
        <h4>{{$group}}</h4>
        {% endif %}
        <div>
            {% foreach($groupParams as $param => $paramInfo) %}
                {% if ($paramInfo['type']=='slider') %}
                    {% include imgix/editors/imgix-slider.php %}
                {% elseif ($paramInfo['type']=='color') %}
                    {% include imgix/editors/imgix-color.php %}
                {% elseif ($paramInfo['type']=='pillbox') %}
                    {% include imgix/editors/imgix-pillbox.php %}
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
    <a href="#" class="button media-button button-primary button-reset imgix-button-reset-all">
        {{__('Reset All')}}
    </a>
    <a href="#" class="button media-button button-primary media-button-select imgix-button-save-adjustments">
        {{__('Save Adjustments')}}
    </a>
</div>
{% endblock %}

{% block script %}
<script>
    new ILabImageEdit(jQuery, {
        modal_id:'{{$modal_id}}',
        image_id:{{$image_id}},
        size:"{{$size}}",
        currentPreset:"{{$currentPreset}}",
        presets:{{json_encode($presets,JSON_FORCE_OBJECT | JSON_PRETTY_PRINT)}},
        settings:{{json_encode($settings,JSON_FORCE_OBJECT | JSON_PRETTY_PRINT)}}
    });
</script>
{% endblock %}
