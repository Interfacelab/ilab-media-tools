<div class="imgix-parameter" data-default-value="#00FF0000" data-param="{{$param}}" data-param-type="{{$paramInfo['type']}}">
    <div class="imgix-param-title imgix-param-title-colortype">
        <div class="imgix-param-title-left">
            <h3>{{__($paramInfo['title'])}}</h3>
        </div>
        <div class="imgix-param-title-right">
            <input type="text" class="ilab-color-input" data-opacity="{{imgixCurrentAlphaValue($param,$settings,'0.00')}}" value="{{imgixCurrentColorValue($param,$settings,'#FF0000')}}" size="7">
        </div>
    </div>
    <div class="imgix-param-reset"><a href="#">{{__('Reset')}}</a></div>
    @if (!empty($paramInfo['hidden']))
    <div class="imgix-param-imagick-warning">
        <div>This parameter requires the <a target="_blank" href="http://php.net/manual/en/book.imagick.php">PHP ImageMagick extension</a> to be installed.</div>
    </div>
    @endif
</div>