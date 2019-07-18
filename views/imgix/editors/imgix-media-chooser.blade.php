<div class="imgix-parameter" data-default-value="" data-param="{{$param}}" data-param-type="{{$paramInfo['type']}}">
    <div class="imgix-param-title imgix-media-param-title">
        <div class="imgix-param-title-left">
            <h3>{{__($paramInfo['title'])}}</h3>
        </div>
        <div class="imgix-param-title-right">
            <input type="hidden" class="imgix-param" value="{{imgixCurrentValue($param,$settings,'')}}">
            <a href="#" class="button button-small button-primary imgix-media-button">Select</a>
        </div>
    </div>
    <div class="imgix-media-preview">
        <div class="imgix-media-preview-inner">
            <div class="imgix-media-preview">
                @if (imgixCurrentMediaSrcValue($param,$settings))
                <img src="{{imgixCurrentMediaSrcValue($param,$settings)}}">
                @else
                <img>
                @endif
            </div>
        </div>
    </div>
    <div class="imgix-param-reset"><a href="#">{{__('Remove')}}</a></div>
    @if (!empty($paramInfo['hidden']))
    <div class="imgix-param-imagick-warning">
        <div>This parameter requires the <a target="_blank" href="http://php.net/manual/en/book.imagick.php">PHP ImageMagick extension</a> to be installed.</div>
    </div>
    @endif
</div>