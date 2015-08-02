<div class="ilab-imgix-parameter">
    <div class="ilab-imgix-param-title">
        <div class="ilab-imgix-param-title-left">
            <h3>{{__($paramInfo['title'])}}</h3>
        </div>
        <div class="ilab-imgix-param-title-right">
            <input class="imgix-param imgix-param-color" data-param-type="{{$paramInfo['type']}}" type="text" name="{{$param}}" id="imgix-param-{{$param}}" value="#FF0000">
        </div>
    </div>
    <input class="imgix-param-alpha" id="imgix-param-alpha-{{$param}}" type="range" min="0" max="100" value="0" />
    <div class="imgix-param-blend-mode">
        <h3>Blend Mode</h3>
        <select class="imgix-param-blend" id="imgix-param-blend-{{$param}}" data-blend-param="{{$paramInfo['blend-param']}}">
            {% foreach($paramInfo['blends'] as $blendKey => $blendName) %}
            <option value="{{$blendKey}}">{{$blendName}}</option>
            {% endforeach %}
        </select>
    </div>
    <a class="imgix-param-reset" data-param-type="{{$paramInfo['type']}}" data-param="{{$param}}" href="#">{{__('Reset')}}</a>
</div>