<?php /** @var \MediaCloud\Plugin\Wizard\Config\Section $section  */?>
<?php /** @var bool $initial  */?>
<div data-id="{{$section->id()}}" class="wizard-section {{$initial ? 'current' : ''}} {{$section->sectionClass()}}" data-initial="{{$initial ? 'true' : 'false'}}" data-display-steps="{{$section->displaySteps() ? 'true' : 'false'}}" tabindex="-1">
    @foreach($section->steps() as $step)
        @include('wizard.steps.'.$step->type(), ['step' => $step, 'stepIndex' => $loop->index])
    @endforeach
    @if($section->displaySteps())
    <script id="{{$section->id().'-steps'}}" type="application/json">
        {!! $section->stepJson() !!}
    </script>
    @endif
</div>