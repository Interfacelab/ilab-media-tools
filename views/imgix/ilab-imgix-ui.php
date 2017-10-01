{% extends base/ilab-modal.php %}

{% block title %}
{{ __('Edit Image') }} ({{$full_width}} x {{$full_height}})
{% end block %}

{% block main-tabs %}
<div class="ilabm-editor-tabs">
    <div class="ilabm-tabs-select-label">Size:</div>
    <select class="ilabm-tabs-select">
        <option value="full" data-url="{{$tool->editPageURL($image_id,'full',true) }}" {{(($size=='full')?'selected':'')}}>Source Image</option>
        {% foreach ($sizes as $name => $info) %}
        {% if (strpos($name,'__')!==0) %}
        <option value="{{$name}}" data-url="{{$tool->editPageURL($image_id,$name,true) }}" {{(($size==$name)?'selected':'')}}>{{ ucwords(str_replace('_', ' ', str_replace('-', ' ', $name))) }}</option>
        {% endif %}
        {% endforeach %}
    </select>
    <div data-url="{{$tool->editPageURL($image_id,'full',true) }}" data-value="full" class="ilabm-editor-tab {{(($size=='full')?'active-tab':'')}}">Source Image</div>
    {% foreach ($sizes as $name => $info) %}
    {% if (strpos($name,'__')!==0) %}
    <div data-url="{{$tool->editPageURL($image_id,$name,true) }}" data-value="{{$name}}" class="ilabm-editor-tab {{(($size==$name)?'active-tab':'')}}">{{ ucwords(str_replace('_', ' ', str_replace('-', ' ', $name))) }}</div>
    {% endif %}
    {% endforeach %}
</div>
{% end block %}

{% block editor %}
<img class="imgix-preview-image" src="{{ $src }}" />
<div class="ilabm-preview-wait-modal is-hidden">
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
    <div class="ilabm-bottom-bar-seperator"></div>
</div>
<a href="#" class="button imgix-new-preset-button">New Preset</a>
<div class="imgix-preset-container">
    <div class="ilabm-bottom-bar-seperator"></div>
    <select class="imgix-presets">
        <option>Preset 1</option>
    </select>
    <a href="#" class="button button-primary imgix-save-preset-button">Save Preset</a>
    <a href="#" class="button button-reset imgix-delete-preset-button">Delete Preset</a>
</div>
{% end block %}

{% block sidebar-content %}
<div class="ilabm-sidebar-tabs">
    {% for each($params as $paramSection => $paramSectionInfo) %}
    <div class="ilabm-sidebar-tab" data-target="imgix-params-section-{{$paramSection}}">{{__(ucwords(str_replace('-', ' ', $paramSection)))}}</div>
    {% end for each %}
</div>
<div class="ilabm-sidebar-content">
    {% for each($params as $paramSection => $paramSectionInfo) %}
    <div class="imgix-params-section-{{$paramSection}} imgix-parameters-container is-hidden">
        {% for each($paramSectionInfo as $group => $groupParams) %}
        {% if (strpos($group,'--')!==0) %}
        <h4>{{str_replace('-',' ',$group)}}</h4>
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
<div class="ilabm-sidebar-actions">
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
            meta:{{json_encode($meta,JSON_FORCE_OBJECT | JSON_PRETTY_PRINT)}},
            currentPreset:"{{$currentPreset}}",
            presets:{{json_encode($presets,JSON_FORCE_OBJECT | JSON_PRETTY_PRINT)}},
            settings:{{json_encode($settings,JSON_FORCE_OBJECT | JSON_PRETTY_PRINT)}}
        });
</script>
{% endblock %}
