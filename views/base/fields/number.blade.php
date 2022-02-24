<div id="setting-{{$name}}" {{(($conditions) ? 'data-conditions="true"' : '')}}>
<input size='40' type='number' id="{{$name}}" min="{{$min}}" max="{{$max}}" step="{{$inc}}" name='{{$name}}' value='{{$value}}'>
@if($description)
<p class='description'>{!! $description !!}</p>
@endif
@if($conditions)
<script id="{{$name}}-conditions" type="text/plain">
        {!! json_encode($conditions, JSON_PRETTY_PRINT) !!}
</script>
@endif
</div>
