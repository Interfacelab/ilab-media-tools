<div class="imgix-parameter" data-default-value="#FF0000" data-param="{{$param}}" data-param-type="{{$paramInfo['type']}}" data-blend-param="{{$paramInfo['blend-param']}}" data-blend-value="{{(isset($settings[$paramInfo['blend-param']]) ? $settings[$paramInfo['blend-param']] : 'none')}}">
    <div class="imgix-param-title imgix-param-title-colortype">
        <div class="imgix-param-title-left">
            <h3>{{__($paramInfo['title'])}}</h3>
        </div>
        <div class="imgix-param-title-right">
            <input class="imgix-param imgix-param-color" type="text" value="{{imgixCurrentColorValue($param,$settings,'#FF0000')}}">
        </div>
    </div>
    <input class="imgix-param-alpha" type="range" min="0" max="100" value="{{imgixCurrentAlphaValue($param,$settings,0)}}" />
    <div class="imgix-param-blend-mode">
        <h3>Blend Mode</h3>
        <select class="imgix-param-blend">
            {% foreach($paramInfo['blends'] as $blendKey => $blendName) %}
            <option value="{{$blendKey}}">{{$blendName}}</option>
            {% endforeach %}
        </select>
    </div>
    <a class="imgix-param-reset" href="#">{{__('Reset')}}</a>
</div>