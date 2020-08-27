<?php /** @var \MediaCloud\Plugin\Wizard\Config\Step $step  */?>
<?php /** @var int $stepIndex  */?>
<div data-id="{{$step->id()}}" data-type="{{$step->type()}}" class="wizard-step wizard-step-{{$step->type()}} {{($stepIndex == 0) ? 'current': ''}} {{$step->stepClass()}}" @if(!empty($step->next())) data-next="{{$step->next()}}" @endif  @if(!empty($step->returnLink())) data-return="{{$step->returnLink() ? 'true' : 'false'}}" @endif>
    <div class="step-contents">
        <div class="video">
            {!! str_replace('feature=oembed', 'feature=oembed&enablejsapi=1', wp_oembed_get($step->videoUrl())) !!}
        </div>
    </div>
</div>