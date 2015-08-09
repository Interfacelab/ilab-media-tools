<div class="ilab-imgix-parameter" data-default-value="" data-param="{{$param}}" data-param-type="{{$paramInfo['type']}}">
    <div class="ilab-imgix-param-title ilab-imgix-media-param-title">
        <div class="ilab-imgix-param-title-left">
            <h3>{{__($paramInfo['title'])}}</h3>
        </div>
        <div class="ilab-imgix-param-title-right">
            <input type="hidden" class="imgix-param" value="{{imgixCurrentValue($param,$settings,'')}}">
            <a href="#" class="button button-small button-primary imgix-media-button">Select</a>
        </div>
    </div>
    <div class="ilab-imgix-media-preview">
        <div class="ilab-imgix-media-preview-inner">
            {% if (imgixCurrentMediaSrcValue($param,$settings)) %}
            <img class="imgix-media-preview" src="{{imgixCurrentMediaSrcValue($param,$settings)}}">
            {% else %}
            <img class="imgix-media-preview">
            {% endif %}
        </div>
    </div>
    <a class="imgix-param-reset" href="#">{{__('Remove')}}</a>
</div>