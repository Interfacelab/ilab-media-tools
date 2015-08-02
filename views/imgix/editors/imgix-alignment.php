<div class="ilab-imgix-parameter">
    <div class="ilab-imgix-param-title ilab-imgix-media-param-title">
        <div class="ilab-imgix-param-title-left">
            <h3>{{__($paramInfo['title'])}}</h3>
        </div>
        <div class="ilab-imgix-param-title-right"></div>
    </div>
    <div class="ilab-imgix-alignment-container">
        <input type="hidden" class="imgix-param" name="{{$param}}" id="imgix-param-{{$param}}" >
        <a href="#" class="imgix-alignment-button" data-param="{{$param}}" data-param-value="top,left"><img src="{{ILAB_PUB_IMG_URL}}/wm-align-top-left.png"></a>
        <a href="#" class="imgix-alignment-button" data-param="{{$param}}" data-param-value="top,center"><img src="{{ILAB_PUB_IMG_URL}}/wm-align-top-center.png"></a>
        <a href="#" class="imgix-alignment-button" data-param="{{$param}}" data-param-value="top,right"><img src="{{ILAB_PUB_IMG_URL}}/wm-align-top-right.png"></a>
        <a href="#" class="imgix-alignment-button" data-param="{{$param}}" data-param-value="middle,left"><img src="{{ILAB_PUB_IMG_URL}}/wm-align-middle-left.png"></a>
        <a href="#" class="imgix-alignment-button" data-param="{{$param}}" data-param-value="middle,center"><img src="{{ILAB_PUB_IMG_URL}}/wm-align-middle-center.png"></a>
        <a href="#" class="imgix-alignment-button" data-param="{{$param}}" data-param-value="middle,right"><img src="{{ILAB_PUB_IMG_URL}}/wm-align-middle-right.png"></a>
        <a href="#" class="imgix-alignment-button" data-param="{{$param}}" data-param-value="bottom,left"><img src="{{ILAB_PUB_IMG_URL}}/wm-align-bottom-left.png"></a>
        <a href="#" class="imgix-alignment-button" data-param="{{$param}}" data-param-value="bottom,center"><img src="{{ILAB_PUB_IMG_URL}}/wm-align-bottom-center.png"></a>
        <a href="#" class="imgix-alignment-button" data-param="{{$param}}" data-param-value="bottom,right"><img src="{{ILAB_PUB_IMG_URL}}/wm-align-bottom-right.png"></a>
    </div>
    <a class="imgix-param-reset" data-param="{{$param}}" data-param-type="{{$paramInfo['type']}}" href="#">{{__('Remove')}}</a>
</div>