<div id="setting-{{$name}}" {{(($conditions) ? 'data-conditions="true"' : '')}}>
    <input type='hidden' id="{{$name}}" name='{{$name}}' value='{{$value}}'>
    <div class="settings-image-preview-container">
        <div class="settings-image-preview" @if(!empty($imageUrl))style="background-image:url({!! $imageUrl !!})" @endif></div>
        <div>
            <button type="button" class="button select-watermark-button">Select Media</button>
        </div>
    </div>
    @if ($description)
    <p class='description'>{!! $description !!}</p>
    @endif
    @if($conditions)
        <script id="{{$name}}-conditions" type="text/plain">
            {!! json_encode($conditions, JSON_PRETTY_PRINT) !!}
        </script>
    @endif


    <script>
        (function($) {
            var settingId = "#setting-{{$name}}";
            var setting = $(settingId);
            var imageInput = $('#{{$name}}');
            var preview = setting.find('.settings-image-preview');
            setting.find('button.select-watermark-button').on('click', (event) => {
                event.preventDefault();

                var selector = wp.media({
                    title: "Select watermark image",
                    button: {
                        text: "Select Watermark"
                    },
                    library: {
                        type: ['image']
                    }
                });

                selector.on('select', () => {
                    var selection = selector.state().get('selection');

                    var selected = [];
                    selection.forEach((ele) => {
                        selected.push(ele);
                    });

                    if (selected.length > 0) {
                        var media = selected[0];
                        var sizes = media.get('sizes');
                        if ((typeof sizes !== 'undefined') && (sizes !== null) && (sizes.hasOwnProperty('medium'))) {
                            preview.css('background-image', 'url('+sizes.medium.url+')');
                        } else {
                            preview.css('background-image', 'url('+media.get('url')+')');
                        }

                        imageInput.val(media.get('id'));
                    }
                });

                selector.open();

                return false;
            })
        })(jQuery);
    </script>
</div>
