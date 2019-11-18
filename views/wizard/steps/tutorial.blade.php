<?php /** @var \ILAB\MediaCloud\Wizard\Config\Step $step  */?>
<?php /** @var int $stepIndex  */?>
<div data-id="{{$step->id()}}" data-type="{{$step->type()}}" class="wizard-step wizard-step-{{$step->type()}} {{($stepIndex == 0) ? 'current': ''}} {{$step->class()}}" @if(!empty($step->next())) data-next="{{$step->next()}}" @endif  @if(!empty($step->return())) data-return="{{$step->return() ? 'true' : 'false'}}" @endif>
    <div class="tutorial-container">
        <div class="tutorial">
            @include($step->introView())
        </div>
    </div>
</div>