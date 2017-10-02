<div class="imgix-parameter ilabm-pillbox" data-param="{{$param}}" data-param-type="{{$paramInfo['type']}}" data-param-values="{{implode(',',array_keys($paramInfo['options']))}}">
    {% foreach($paramInfo['options'] as $optionKey => $optionInfo) %}
    <input type="hidden" name="{{$optionKey}}" value="{{$paramInfo['selected']($settings, $optionKey, '1','0')}}">
    <a data-param="{{$optionKey}}" class="ilabm-pill imgix-pill-{{$optionKey}} {{$paramInfo['selected']($settings, $optionKey, 'pill-selected', '')}}" href="#"><span class="icon"></span><span>{{$optionInfo['title']}}</span></a>
    {% endforeach %}
</div>
