<div class="imgix-parameter ilabm-pillbox" data-param="{{$param}}" data-param-type="{{$paramInfo['type']}}" data-param-values="{{implode(',',array_keys($paramInfo['options']))}}">
    <input type="hidden" name="enhance" value="{{imgixAutoIsSelected('enhance',$settings,'1','0')}}">
    <input type="hidden" name="redeye" value="{{imgixAutoIsSelected('redeye',$settings,'1','0')}}">
    <a data-param="enhance" class="ilabm-pill imgix-pill-enhance {{imgixAutoIsSelected('enhance',$settings,'pill-selected')}}" href="#">Auto Enhance</a>
    <a data-param="redeye" class="ilabm-pill imgix-pill-redeye {{imgixAutoIsSelected('redeye',$settings,'pill-selected')}}" href="#">Remove Red Eye</a>
</div>
