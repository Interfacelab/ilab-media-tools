<div id="setting-{{$name}}" {{(($conditions) ? 'data-conditions="true"' : '')}}>
    <input size='40' {{empty($editable) ? 'readonly' : ''}} id="{{$name}}" name='{{$name}}' type='text' value='{{$value}}'>
    @if($description)
        <p class='description'>{!! $description !!}</p>
    @endif
    @if($conditions)
        <script id="{{$name}}-conditions" type="text/plain">
            {!! json_encode($conditions, JSON_PRETTY_PRINT) !!}
        </script>
    @endif
</div>
