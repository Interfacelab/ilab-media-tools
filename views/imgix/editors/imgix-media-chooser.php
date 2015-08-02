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