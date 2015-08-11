{% extends base/ilab-modal.php %}

{% block title %}
{{ __('Crop Image') }} ({{$full_width}} x {{$full_height}})
{% end block %}

{% block main-tabs %}
<div id="ilab-modal-editor-tabs">
    {% if (count($sizes)>10) %}
    <div id="imgix-image-size-label">Size:</div>
    <select class="imgix-image-size-select">
        {% foreach ($sizes as $name => $info) %}
        {% if ($info['crop']==1) %}
        <option value="{{$tool->cropPageURL($image_id,$name,true) }}" {{(($size==$name)?'selected':'')}}>{{ ucwords(str_replace('_', ' ', str_replace('-', ' ', $name))) }}</option>
        {% endif %}
        {% endforeach %}
    </select>
    {% else %}
    {% foreach ($sizes as $name => $info) %}
    <div data-url="{{$tool->editPageURL($image_id,$name,true) }}" class="ilab-modal-editor-tab {{(($size==$name)?'active-tab':'')}}">{{ ucwords(str_replace('_', ' ', str_replace('-', ' ', $name))) }}</div>
    {% endforeach %}
    {% endif %}
</div>
{% end block %}

{% block editor %}
<img id="ilab-cropper" src="{{ $src }}" />
{% endblock %}

{% block bottom-bar %}
<div class="ilab-modal-bottom-bar">
    <div id="imgix-status-container" class="is-hidden">
        <span class="spinner is-active"></span>
        <span id="imgix-status-label">Saving ...</span>
    </div>
</div>
{% end block %}

{% block sidebar-content %}
<div class="ilab-modal-sidebar-content ilab-modal-sidebar-content-cropper">
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
</div>
{% endblock %}

{% block sidebar-actions %}
<div class="ilab-modal-sidebar-actions">
    <a href="javascript:ILabCrop.crop();" class="button media-button button-primary">
        {{__('Crop Image')}}
    </a>
</div>
{% endblock %}

{% block script %}
<script>
    ILabCrop.init({
        image_id:{{$image_id}},
        size:'{{ $size}}',
        min_width:{{$crop_width}},
        min_height:{{$crop_height}},
        aspect_ratio:{{ $ratio }},
        prev_crop_x:{{($prev_crop_x!==null) ? (int)$prev_crop_x : 'undefined'}},
        prev_crop_y:{{($prev_crop_y!==null) ? (int)$prev_crop_y : 'undefined'}},
        prev_crop_width:{{($prev_crop_width!==null) ? (int)$prev_crop_width : 'undefined'}},
        prev_crop_height:{{($prev_crop_height!==null) ? (int)$prev_crop_height : 'undefined'}}
    });
</script>
{% endblock %}
