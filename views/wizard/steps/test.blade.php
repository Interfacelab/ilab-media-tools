<?php /** @var \MediaCloud\Plugin\Wizard\Config\Step $step  */?>
<?php /** @var int $stepIndex  */?>
<div data-id="{{$step->id()}}" data-type="{{$step->type()}}" class="wizard-step wizard-step-{{$step->type()}} {{($stepIndex == 0) ? 'current': ''}} {{$step->stepClass()}}" @if(!empty($step->next())) data-next="{{$step->next()}}" @endif  @if(!empty($step->returnLink()))  data-return="{{$step->returnLink() ? 'true' : 'false'}}" @endif>
    <div class="step-contents">
        @if(!empty($step->introView()))
            <div class="intro">
                @include($step->introView())
            </div>
        @endif

        @if(empty($step->autoStart()))
            <div class="start-buttons">
                <a href="#">Start Tests</a>
            </div>
        @endif

        <ul class="tests">
        </ul>

        <script id="{{$step->id()}}-tests" type="application/json">
            {!! $step->testsJson() !!}
        </script>

        <script type="text/html" id="tmpl-test-item-template">
            <li class="hidden">
                <div class="icon">
                    <img class="waiting" src="{{ILAB_PUB_IMG_URL}}/wizard-spinner.svg">
                    <img class="success" src="{{ILAB_PUB_IMG_URL}}/wizard-icon-success.svg" width="32" height="32">
                    <img class="error" src="{{ILAB_PUB_IMG_URL}}/wizard-icon-error.svg" width="32" height="32">
                    <img class="warning" src="{{ILAB_PUB_IMG_URL}}/wizard-icon-warning.svg" width="32" height="32">
                </div>
                <div class="description">
                    <h3>@{{ data.title }}</h3>
                    <p>@{{ data.description  }}</p>
                    <# if (data.hasOwnProperty('errors')) { #>
                    <ul class="errors">
                        <# _.each(data.errors, function(error) { #>
                        <li>@{{{ error  }}}</li>
                        <# }); #>
                    </ul>
                    <# } #>
                </div>
            </li>
        </script>
    </div>
</div>
