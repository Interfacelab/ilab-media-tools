<div id="setting-{{$name}}" {{(($conditions) ? 'data-conditions="true"' : '')}}>
	<select id="{{$name}}" name='{{$name}}'>
	@foreach($options as $val => $optionName)
		<option value='{{$val}}' {{(($val == $value) ? 'selected' : '')}}>{{$optionName}}</option>
	@endforeach
	</select>
	@if ($description)
	<p class='description'>{!! $description !!}</p>
	@endif
    @if($conditions)
    <script id="{{$name}}-conditions" type="text/plain">
        {!! json_encode($conditions, JSON_PRETTY_PRINT) !!}
    </script>
    @endif
</div>
