{% extends base/ilab-modal.php %}

{% block title %}
{{ __('Edit Image') }} ({{$full_width}} x {{$full_height}})
{% end block %}

{% block main-tabs %}
<div id="ilab-modal-editor-tabs">
    <div class="ilab-modal-editor-tab active-tab">Original Image</div>
    <div class="ilab-modal-editor-tab">Tab 1</div>
    <div class="ilab-modal-editor-tab">Tab 1</div>
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

{% block sidebar-content %}
<div class="ilab-modal-sidebar-content">
    {% for each($params as $paramSection => $paramSectionInfo) %}
    <div id="ilab-imgix-params-section-{{$paramSection}}" class="ilab-imgix-parameters-container is-hidden">
        {% if ($paramSection=='adjust') %}
        <div class="ilab-modal-pillbox">
            <input type="hidden" data-param-type="hidden" data-default-value="0" class="imgix-param" name="enhance" id="imgix-param-enhance" value="{{imgixCurrentValue('enhance',$current,0)}}">
            <input type="hidden" data-param-type="hidden" data-default-value="0" class="imgix-param" name="redeye" id="imgix-param-redeye" value="{{imgixCurrentValue('redeye',$current,0)}}">
            <a data-param="enhance" id="imgix-pill-enhance" class="ilab-imgix-pill ilab-imgix-pill-enhance {{imgixIsSelected('enhance',$current,1,0,'pill-selected')}}" href="#">Auto Enhance</a>
            <a data-param="redeye" id="imgix-pill-redeye" class="ilab-imgix-pill ilab-imgix-pill-redeye {{imgixIsSelected('redeye',$current,1,0,'pill-selected')}}" href="#">Remove Red Eye</a>
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
{% endblock %}

{% block script %}
<script>
    ILabImageEdit.init({
        image_id:{{$image_id}},
        current:{{json_encode($current,JSON_FORCE_OBJECT|JSON_PRETTY_PRINT)}},
        settings:{{json_encode($settings,JSON_FORCE_OBJECT | JSON_PRETTY_PRINT)}}
    });
</script>
{% endblock %}
