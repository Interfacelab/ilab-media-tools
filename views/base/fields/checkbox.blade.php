<div id="setting-{{$name}}" {{((!empty($conditions)) ? 'data-conditions="true"' : '')}}>
@include('base/ui/checkbox', ['name' => $name, 'value' => $value, 'description' => '', 'enabled' => true])
@if($description)
<p class='description'>{!! $description !!}</p>
@endif
@if($conditions)
<script id="{{$name}}-conditions" type="text/plain">
    {!! json_encode($conditions, JSON_PRETTY_PRINT) !!}
</script>
@endif
</div>
