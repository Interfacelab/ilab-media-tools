<div id="setting-{{$name}}" {{(($conditions) ? 'data-conditions="true"' : '')}}>
<input type='checkbox' id="{{$name}}" name='{{$name}}' {{(($value) ? 'checked' : '')}}>
{% if($description) %}
<p class='description'>{{$description}}</p>
{% endif %}
{% if($conditions) %}
<script id="{{$name}}-conditions" type="text/plain">
    {{json_encode($conditions, JSON_PRETTY_PRINT)}}
</script>
{% endif %}
</div>
