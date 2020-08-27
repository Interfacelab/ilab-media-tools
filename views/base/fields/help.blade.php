<?php $classes = ($target == 'footer') ? 'button button-primary' : ''; ?>
@foreach($data as $id => $helpLinks)
    <div id="doc-links-{{$id}}" class="doc-links-setting" @if(!$loop->first) style="display:none" @endif>
        @foreach($helpLinks as $helpLink)
            @if(isset($helpLink['video_url']))
            <a href="{{$helpLink['video_url']}}" target="_blank" class="{{$classes}} {{\MediaCloud\Plugin\Utilities\arrayPath($helpLink, 'class', '')}} mediabox">{{$helpLink['title']}}</a>
            @elseif(isset($helpLink['wizard']))
            <a href="{{admin_url('admin.php?page=media-cloud-wizard&wizard='.$helpLink['wizard'])}}" class="{{$classes}} {{\MediaCloud\Plugin\Utilities\arrayPath($helpLink, 'class', '')}}">{{$helpLink['title']}}</a>
            @elseif(isset($helpLink['external_url']))
            <a href="{{$helpLink['external_url']}}" target="_blank" class="{{$classes}} {{\MediaCloud\Plugin\Utilities\arrayPath($helpLink, 'class', '')}}">{{$helpLink['title']}}</a>
            @else
            <a href="{{$helpLink['url']}}" target="_blank" class="{{$classes}} {{\MediaCloud\Plugin\Utilities\arrayPath($helpLink, 'class', '')}}" @if(!empty($helpLink['url'])) data-article-sidebar="{{$helpLink['url']}}" @endif>{{$helpLink['title']}}</a>
            @endif
        @endforeach
    </div>
    @if(!empty($watch))
    <script>
        (function($){
            $('#{{$watch}}').on('change', function() {
                $('.doc-links-setting').css({display: 'none'});
                $('#doc-links-'+$(this).val()).css({display: ''});
            });

            $('#{{$watch}}').trigger('change');
        })(jQuery);
    </script>
    @endif
@endforeach
