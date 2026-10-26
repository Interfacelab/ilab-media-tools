<div id="setting-{{$name}}" {{(($conditions) ? 'data-conditions="true"' : '')}}>
    <div class="mcloud-media-chrome-controls-editor"
         data-base-url="{{ILAB_PUB_URL}}"
         data-config="{{$value}}"
         data-setting-name="{{$name}}"
    ></div>
    <input type="hidden" id="{{$name}}" name='{{$name}}' value='{{$value}}' />
    @if($conditions)
        <script id="{{$name}}-conditions" type="text/plain">
            {!! json_encode($conditions, JSON_PRETTY_PRINT) !!}
        </script>
    @endif
</div>
