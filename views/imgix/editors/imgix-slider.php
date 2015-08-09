<div data-default-value="{{$paramInfo['default']}}" data-param="{{$param}}" data-param-type="{{$paramInfo['type']}}" class="ilab-imgix-parameter">
    <div class="ilab-imgix-param-title">
        <div class="ilab-imgix-param-title-left">
            <h3>{{__($paramInfo['title'])}}</h3>
        </div>
        <div class="ilab-imgix-param-title-right">
            <h3 style="font-style:italic">12</h3>
        </div>
    </div>
    <input class="imgix-param" type="range" min="{{$paramInfo['min']}}" max="{{$paramInfo['max']}}" value="{{imgixCurrentValue($param,$settings,$paramInfo['default'])}}" />
    <a class="imgix-param-reset" href="#">{{__('Reset')}}</a>
</div>