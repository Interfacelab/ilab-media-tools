<div id="setting-{{$name}}" {{(($conditions) ? 'data-conditions="true"' : '')}}>
<textarea cols='40' rows="4" id="{{$name}}" name='{{$name}}' placeholder='{{$placeholder}}'>{{$value}}</textarea>
@if($description)
<p class='description'>{!! $description !!}</p>
@endif
@if($conditions)
<script id="{{$name}}-conditions" type="text/plain">
        {!! json_encode($conditions, JSON_PRETTY_PRINT) !!}
    </script>
@endif
</div>
