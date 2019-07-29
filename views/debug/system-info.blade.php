<div class="troubleshooter-step">
    <div class="troubleshooter-step-icon">
        @if (!empty($warnings))
            <img src="{{ILAB_PUB_IMG_URL}}/icon-warning.svg" width="32" height="32">
        @else
            <img src="{{ILAB_PUB_IMG_URL}}/icon-success.svg" width="32" height="32">
        @endif
    </div>
    <div>
        <div class="troubleshooter-title">{{$title}}</div>
        <div class="troubleshooter-message">
            {!! $description !!}
        </div>
        <ul class="troubleshooter-info">
            @foreach($info as $item)
                <li class="info-{{$item['type']}}">{!! $item['message'] !!}</li>
            @endforeach
        </ul>
        @if (!empty($hints))
            <p>Some hints that might help you troubleshoot this issue:</p>
            <ul class="troubleshooter-errors">
                @foreach($hints as $hint)
                    <li>{!! $hint !!}</li>
                @endforeach
            </ul>
        @endif
    </div>
</div>