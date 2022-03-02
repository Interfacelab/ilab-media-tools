<?php /** @var \MediaCloud\Plugin\Wizard\Config\Step $step  */?>
<?php /** @var int $stepIndex  */?>
<div data-id="{{$step->id()}}" data-type="{{$step->type()}}" class="wizard-step wizard-step-{{$step->type()}} {{($stepIndex == 0) ? 'current': ''}} {{$step->stepClass()}}" @if(!empty($step->next())) data-next="{{$step->next()}}" @endif  @if(!empty($step->returnLink())) data-return="{{$step->returnLink() ? 'true' : 'false'}}" @endif>
    @foreach($step->groups() as $group)
    <div class="step-contents step-group" data-id="{{$step->id()}}-group-{{$group->index()}}">
        <script type="application/json" id="{{$step->id()}}-group-{{$group->index()}}">
            {!! json_encode($group->conditions(), JSON_PRETTY_PRINT) !!}
        </script>
        @if(!empty($group->introView()))
            <div class="intro">
                @include($group->introView())
            </div>
        @endif

        <div class="contents">
            <ul class="options {{$group->groupClass()}}">
                @foreach($group->options() as $option)
                    <li>
                        @if(!empty($option->descriptionView()))
                            <div class="description">
                                @include($option->descriptionView())
                                <div class="arrow-down"></div>
                            </div>
                        @endif
                        @if(!empty($option->link()))
                        <a class="{{$option->optionClass()}}" href="{{$option->link()}}" @if(!empty($option->target()))target="{{$option->target()}}" @endif tooltip="{{$option->title()}}">
                            @if(!empty($option->icon()))
                                <img src="{{ILAB_PUB_IMG_URL.'/'.$option->icon()}}">
                            @else
                               {{$option->title()}}
                            @endif
                        </a>
                        @else
                        <a class="{{$option->optionClass()}}" href="#" tooltip="{{$option->title()}}" data-next="{{$option->next()}}">
                            @if(!empty($option->icon()))
                                <img src="{{ILAB_PUB_IMG_URL.'/'.$option->icon()}}">
                            @else
                               {{$option->title()}}
                            @endif
                        </a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endforeach
</div>
