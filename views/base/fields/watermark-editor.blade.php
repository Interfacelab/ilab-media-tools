<div id="setting-{{$name}}" {{(($conditions) ? 'data-conditions="true"' : '')}}>
    <div class="mcloud-watermark-editor"
         data-reset-nonce="{{wp_create_nonce('mcloud-reset-watermark')}}"
         data-setting-name="{{$name}}"
         data-cardinal="{{$cardinal}}"
         data-ajax-url="{{admin_url( 'admin-ajax.php', 'relative' )}}"
    ></div>
    <input type="hidden" id="{{$name}}" name='{{$name}}' value='{{$value}}' />
    @if($conditions)
        <script id="{{$name}}-conditions" type="text/plain">
            {!! json_encode($conditions, JSON_PRETTY_PRINT) !!}
        </script>
    @endif
</div>
