@extends('templates/modal')

@section('modal_id', $modal_id)

@section('title')
{{ __('Crop Image') }} ({{$full_width}} x {{$full_height}})
@endsection

@section('main-tabs')
<div class="ilabm-editor-tabs">
    <div class="ilabm-tabs-select-ui">
        <div class="ilabm-tabs-select-label">Size:</div>
        <select class="ilabm-tabs-select">
            @foreach ($sizes as $name => $info)
            @if ((strpos($name,'__')!==0))
            <option value="{{$name}}" data-url="{{$tool->cropPageURL($image_id,$name,true) }}" {{(($size==$name)?'selected':'')}}>{{ ucwords(str_replace('_', ' ', str_replace('-', ' ', $name))) }}</option>
            @endif
            @endforeach
        </select>
    </div>
    <div class="ilabm-tabs-ui">
        @foreach ($sizes as $name => $info)
        @if ((strpos($name,'__')!==0))
        <div data-url="{{$tool->cropPageURL($image_id,$name,true) }}" data-value="{{$name}}" class="ilabm-editor-tab {{(($size==$name)?'active-tab':'')}}">{{ ucwords(str_replace('_', ' ', str_replace('-', ' ', $name))) }}</div>
        @endif
        @endforeach
    </div>
</div>
@endsection

@section('editor')
<img class="ilabc-cropper" src="{{ $src }}" />
@endsection

@section('sidebar-content')
<div class="ilabm-sidebar-content ilabm-sidebar-content-cropper">
    @if ($crop_exists)
    <h3 class="ilabc-crop-size-title">{{ __('Current')}} {{(ucwords(str_replace('-', ' ', $size)))}} ({{$cropped_width}} x {{$cropped_height}})</h3>
    <div class="ilabc-current-crop-container">
        <img class="ilab-current-crop-img" src="{!! $cropped_src !!}" />
    </div>
    @else
    <h3 class="ilabc-crop-size-title">{{ __('Current')}} {{(ucwords(str_replace('-', ' ', $size)))}} ({{$crop_width}} x {{$crop_height}})</h3>
    <div class="ilabc-current-crop-container">
        <img class="ilab-current-crop-img" />
    </div>
    <div class="ilab-not-existing-crop">
        <div class="message error">
            <p>{{ __('Crop not generated yet, use the crop button here below to generate it')}}</p>
        </div>
    </div>
    @endif
    <h3 class="ilab-crop-preview-title">{{ __( 'Crop preview') }}</h3>
    <div class="ilabc-crop-preview-container">
        <div class="ilab-crop-preview"></div>
    </div>
</div>
@endsection

@section('sidebar-actions')
<div class="ilabm-sidebar-actions">
    <a href="#" class="button media-button button-reset ilabc-button-reset-crop">{{ __('Reset Crop') }}</a>
    <a href="#" class="button media-button button-primary ilabc-button-crop">{{ __('Crop Image') }}</a>
</div>
@endsection

@section('bottom-bar')
    <div id="ilab-crop-aspect-checkbox-container">
        <input type="checkbox" id="ilab-crop-aspect-checkbox" checked><label for="ilab-crop-aspect-checkbox">Maintain Aspect Ratio</label>
    </div>
@endsection

@section('script')
<script>
    new ILabCrop(jQuery,{
        modal_id:'{{$modal_id}}',
        image_id:{{$image_id}},
        size:'{{ $size}}',
        image_width:{{(int)$image_width}},
        image_height:{{(int)$image_height}},
        real_crop_width:{{(int)$real_crop_width}},
        real_crop_height:{{(int)$real_crop_height}},
        min_width:{{$crop_width}},
        min_height:{{$crop_height}},
        aspect_ratio:{{ $ratio }},
        crop_axis: {{json_encode($crop_axis)}},
        prev_crop_x:{{($prev_crop_x!==null) ? (int)$prev_crop_x : 'undefined'}},
        prev_crop_y:{{($prev_crop_y!==null) ? (int)$prev_crop_y : 'undefined'}},
        prev_crop_width:{{($prev_crop_width!==null) ? (int)$prev_crop_width : 'undefined'}},
        prev_crop_height:{{($prev_crop_height!==null) ? (int)$prev_crop_height : 'undefined'}}
    });
</script>
@endsection
