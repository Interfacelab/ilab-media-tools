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