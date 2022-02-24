<div class="imgix-parameter" data-default-value="bottom,right" data-param="{{$param}}" data-param-type="{{$paramInfo['type']}}">
    <div class="imgix-param-title imgix-media-param-title">
        <div class="imgix-param-title-left">
            <h3>{{__($paramInfo['title'])}}</h3>
        </div>
        <div class="imgix-param-title-right"></div>
    </div>
    <div class="imgix-alignment-container">
        <input type="hidden" class="imgix-param">
        <a href="#" class="imgix-alignment-button {{imgixIsSelected($param,$settings,'top,left','bottom,right','selected-alignment')}}" data-param-value="top,left"><img src="{{ILAB_PUB_IMG_URL}}/wm-align-top-left.png"></a>
        <a href="#" class="imgix-alignment-button {{imgixIsSelected($param,$settings,'top,center','bottom,right','selected-alignment')}}" data-param-value="top,center"><img src="{{ILAB_PUB_IMG_URL}}/wm-align-top-center.png"></a>
        <a href="#" class="imgix-alignment-button {{imgixIsSelected($param,$settings,'top,right','bottom,right','selected-alignment')}}" data-param-value="top,right"><img src="{{ILAB_PUB_IMG_URL}}/wm-align-top-right.png"></a>
        <a href="#" class="imgix-alignment-button {{imgixIsSelected($param,$settings,'middle,left','bottom,right','selected-alignment')}}" data-param-value="middle,left"><img src="{{ILAB_PUB_IMG_URL}}/wm-align-middle-left.png"></a>
        <a href="#" class="imgix-alignment-button {{imgixIsSelected($param,$settings,'middle,center','bottom,right','selected-alignment')}}" data-param-value="middle,center"><img src="{{ILAB_PUB_IMG_URL}}/wm-align-middle-center.png"></a>
        <a href="#" class="imgix-alignment-button {{imgixIsSelected($param,$settings,'middle,right','bottom,right','selected-alignment')}}" data-param-value="middle,right"><img src="{{ILAB_PUB_IMG_URL}}/wm-align-middle-right.png"></a>
        <a href="#" class="imgix-alignment-button {{imgixIsSelected($param,$settings,'bottom,left','bottom,right','selected-alignment')}}" data-param-value="bottom,left"><img src="{{ILAB_PUB_IMG_URL}}/wm-align-bottom-left.png"></a>
        <a href="#" class="imgix-alignment-button {{imgixIsSelected($param,$settings,'bottom,center','bottom,right','selected-alignment')}}" data-param-value="bottom,center"><img src="{{ILAB_PUB_IMG_URL}}/wm-align-bottom-center.png"></a>
        <a href="#" class="imgix-alignment-button {{imgixIsSelected($param,$settings,'bottom,right','bottom,right','selected-alignment')}}" data-param-value="bottom,right"><img src="{{ILAB_PUB_IMG_URL}}/wm-align-bottom-right.png"></a>
    </div>
    <div class="imgix-param-reset"><a href="#">{{__('Reset')}}</a></div>
    @if (!empty($paramInfo['hidden']))
    <div class="imgix-param-imagick-warning">
        <div>This parameter requires the <a target="_blank" href="http://php.net/manual/en/book.imagick.php">PHP ImageMagick extension</a> to be installed.</div>
    </div>
    @endif
</div>