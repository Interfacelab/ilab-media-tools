<div class="ilab-imgix-parameter">
    <div class="ilab-imgix-param-title">
        <div class="ilab-imgix-param-title-left">
            <h3>{{__($paramInfo['title'])}}</h3>
        </div>
        <div class="ilab-imgix-param-title-right">
            <input class="imgix-param imgix-param-color" data-param-type="{{$paramInfo['type']}}" type="text" name="{{$param}}" id="imgix-param-{{$param}}" value="{{imgixCurrentColorValue($param,$settings,'#FF0000')}}">
        </div>
    </div>
    <input class="imgix-param-alpha" id="imgix-param-alpha-{{$param}}" type="range" min="0" max="100" value="{{imgixCurrentAlphaValue($param,$settings,0)}}" />
    <a class="imgix-param-reset" data-param-type="{{$paramInfo['type']}}" data-param="{{$param}}" href="#">{{__('Reset')}}</a>
</div>