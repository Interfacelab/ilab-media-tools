@extends('templates/modal')

@section('modal_id', $modal_id)

@section('title')
    @if ($mode == 'size')
        {{ __('Edit Settings for ') }} {{ucwords(preg_replace('/[-_]/', ' ', $size))}}
    @else
        {{ __('Edit Image') }} ({{$full_width}} x {{$full_height}})
    @endif
@endsection

@section('main-tabs')
<div class="ilabm-editor-tabs">
    <div class="ilabm-tabs-select-ui">
        <div class="ilabm-tabs-select-label">Size:</div>
        <select class="ilabm-tabs-select">
            @if($mode == 'size')
                <option value="{{$size}}" data-url="{{$tool->editPageURL($image_id,$size,true) }}" selected>{{ ucwords(str_replace('_', ' ', str_replace('-', ' ', $size))) }}</option>
            @else
                <option value="full" data-url="{{$tool->editPageURL($image_id,'full',true) }}" {{(($size=='full')?'selected':'')}}>Source Image</option>
                @foreach ($sizes as $name => $info)
                    @if (strpos($name,'__')!==0)
                        <option value="{{$name}}" data-url="{{$tool->editPageURL($image_id,$name,true) }}" {{(($size==$name)?'selected':'')}}>{{ ucwords(str_replace('_', ' ', str_replace('-', ' ', $name))) }}</option>
                    @endif
                @endforeach
            @endif
        </select>
    </div>
    <div class="ilabm-tabs-ui">
        @if($mode == 'size')
            <div data-url="{!! $tool->editPageURL($image_id,$size,true) !!}" data-value="{{$size}}" class="ilabm-editor-tab active-tab">{{ ucwords(str_replace('_', ' ', str_replace('-', ' ', $size))) }}</div>
        @else
            <div data-url="{!! $tool->editPageURL($image_id,'full',true) !!}" data-value="full" class="ilabm-editor-tab {{(($size=='full')?'active-tab':'')}}">Source Image</div>
            @foreach ($sizes as $name => $info)
                @if (strpos($name,'__')!==0)
                    <div data-url="{!! $tool->editPageURL($image_id,$name,true) !!}" data-value="{{$name}}" class="ilabm-editor-tab {{(($size==$name)?'active-tab':'')}}">{{ ucwords(str_replace('_', ' ', str_replace('-', ' ', $name))) }}</div>
                @endif
            @endforeach
        @endif
    </div>
</div>
@endsection

@section('editor')
<img class="imgix-preview-image" src="{!! $src !!} " />
<div class="ilabm-preview-wait-modal is-hidden">
    <h3>Building Preview</h3>
    <span class="spinner is-active"></span>
</div>
@endsection

@section('bottom-bar')
    @if($mode != 'size')
        <a href="#" class="button imgix-new-preset-button">New Preset</a>
        <div class="imgix-preset-container">
            <div class="ilabm-bottom-bar-seperator"></div>
            <select class="imgix-presets">
                <option>Preset 1</option>
            </select>
            <div class="imgix-preset-make-default-container">
                <label for="imgix-preset-make-default">
                    <input name="imgix-preset-make-default" class="imgix-preset-make-default" type="checkbox">
                    Make Default For Size
                </label>
                <div class="ilabm-bottom-bar-seperator"></div>
            </div>
            <a href="#" class="button button-primary imgix-save-preset-button">Save Preset</a>
            <a href="#" class="button button-reset imgix-delete-preset-button">Delete Preset</a>
        </div>
    @endif
@endsection

@section('sidebar-content')
<div class="ilabm-sidebar-tabs">
    @foreach($params as $paramSection => $paramSectionInfo)
    <div class="ilabm-sidebar-tab" data-target="imgix-params-section-{{$paramSection}}">{{__(ucwords(str_replace('-', ' ', $paramSection)))}}</div>
    @endforeach
</div>
<div class="ilabm-sidebar-content">
    @foreach($params as $paramSection => $paramSectionInfo)
    <div class="imgix-params-section-{{$paramSection}} imgix-parameters-container is-hidden">
        @foreach($paramSectionInfo as $group => $groupParams)
        <div class="imgix-parameter-group">
            @if (strpos($group,'--')!==0)
            <h4>{{str_replace('-',' ',$group)}}</h4>
            @endif
            <div>
                @foreach($groupParams as $param => $paramInfo)
                    @if ($paramInfo['type']=='slider')
                        @include('imgix/editors/imgix-slider', ['param' => $param, 'paramInfo' => $paramInfo, 'settings' => $settings])
                    @elseif ($paramInfo['type']=='color')
                        @include('imgix/editors/imgix-color', ['param' => $param, 'paramInfo' => $paramInfo, 'settings' => $settings])
                    @elseif ($paramInfo['type']=='pillbox')
                        @include('imgix/editors/imgix-pillbox', ['param' => $param, 'paramInfo' => $paramInfo, 'settings' => $settings])
                    @elseif ($paramInfo['type']=='blend-color')
                        @include('imgix/editors/imgix-blend-color', ['param' => $param, 'paramInfo' => $paramInfo, 'settings' => $settings])
                    @elseif ($paramInfo['type']=='media-chooser')
                        @include('imgix/editors/imgix-media-chooser', ['param' => $param, 'paramInfo' => $paramInfo, 'settings' => $settings])
                    @elseif ($paramInfo['type']=='alignment')
                        @include('imgix/editors/imgix-alignment', ['param' => $param, 'paramInfo' => $paramInfo, 'settings' => $settings])
                    @endif
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    @endforeach
</div>
@endsection

@section('sidebar-actions')
<div class="ilabm-sidebar-actions">
    <a href="#" class="button media-button button-primary button-reset imgix-button-reset-all">
        {{__('Reset All')}}
    </a>
    <a href="#" class="button media-button button-primary media-button-select imgix-button-save-adjustments">
        {{__('Save Adjustments')}}
    </a>
</div>
@endsection

@section('script')
<script>
        new ILabImageEdit(jQuery, {
            modal_id:'{{$modal_id}}',
            image_id:{{$image_id}},
            size:"{{$size}}",
            mode:"{{$mode}}",
            meta:{!! json_encode($meta,JSON_FORCE_OBJECT | JSON_PRETTY_PRINT) !!},
            currentPreset:"{{$currentPreset}}",
            presets:{!! json_encode($presets,JSON_FORCE_OBJECT | JSON_PRETTY_PRINT) !!},
            settings:{!! json_encode($settings,JSON_FORCE_OBJECT | JSON_PRETTY_PRINT) !!}
        });
</script>
@endsection
