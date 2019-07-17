<?php $classes = ($target == 'footer') ? 'button button-primary' : ''; ?>
@foreach($data as $id => $helpLinks)
    <div id="doc-links-{{$id}}" class="doc-links-setting" @if(!$loop->first) style="display:none" @endif>
        @foreach($helpLinks as $helpLink)
            <a href="{{$helpLink['url']}}" target="_blank" class="{{$classes}} {{\ILAB\MediaCloud\Utilities\arrayPath($helpLink, 'class', '')}}">{{$helpLink['title']}}</a>
        @endforeach
    </div>
    @if(!empty($watch))
    <script>
        (function($){
            $(document).on('ready', function() {
                $('#{{$watch}}').on('change', function() {
                    $('.doc-links-setting').css({display: 'none'});
                    $('#doc-links-'+$(this).val()).css({display: ''});
                });
            });
        })(jQuery);
    </script>
    @endif
@endforeach
