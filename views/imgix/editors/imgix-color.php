<div class="ilab-imgix-parameter" data-default-value="#00FF0000" data-param="{{$param}}" data-param-type="{{$paramInfo['type']}}">
    <div class="ilab-imgix-param-title ilab-imgix-param-title-colortype">
        <div class="ilab-imgix-param-title-left">
            <h3>{{__($paramInfo['title'])}}</h3>
        </div>
        <div class="ilab-imgix-param-title-right">
            <input class="imgix-param imgix-param-color" type="text" value="{{imgixCurrentColorValue($param,$settings,'#FF0000')}}">
        </div>
    </div>
    <input class="imgix-param-alpha" type="range" min="0" max="100" value="{{imgixCurrentAlphaValue($param,$settings,0)}}" />
    <a class="imgix-param-reset" href="#">{{__('Reset')}}</a>
</div>