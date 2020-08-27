<?php /** @var \MediaCloud\Plugin\Wizard\Config\Step $step  */?>
<?php /** @var int $stepIndex  */?>
<div data-id="{{$step->id()}}" data-type="{{$step->type()}}" class="wizard-step wizard-step-{{$step->type()}} {{($stepIndex == 0) ? 'current': ''}} {{$step->stepClass()}}" @if(!empty($step->next())) data-next="{{$step->next()}}" @endif  @if(!empty($step->returnLink())) data-return="{{$step->returnLink() ? 'true' : 'false'}}" @endif>
    <div class="step-contents">
        @if(!empty($step->introView()))
            <div class="intro">
                @include($step->introView())
            </div>
        @endif

        <div class="contents">
            <form>
                @foreach($step->fields() as $field)
                    @include('wizard.steps.form-fields.'.$field->type(), ['field' => $field])
                @endforeach
            </form>
        </div>
    </div>
    <div class="progress">
        <h3>Please wait ...</h3>
        <div class="logo-spinner">
            <img src="{{ILAB_PUB_IMG_URL}}/icon-cloud.svg">
        </div>
    </div>
</div>