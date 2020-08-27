<?php /** @var \MediaCloud\Plugin\Wizard\Config\Config $config  */?>
<div class="wizard-container wizard-invisible">
    <div class="wizard-modal @if(!$config->initialSectionHasSteps())no-steps @endif" data-initial-section="{{$config->initialSectionName()}}" data-admin-exit="{{admin_url('admin.php?page=media-cloud')}}" data-admin-template="{{admin_url('admin.php?page=media-cloud-wizard&wizard=')}}">
        <div class="steps-background"></div>
        <div class="wizard-content">
            <div class="sections" tabindex="-1">
                @foreach($config->sections() as $section)
                    @include('wizard.section', ['section' => $section, 'initial' => ($section->id() == $config->initialSectionName())])
                @endforeach
            </div>
            <div class="steps">
                <ul></ul>
            </div>
            <footer>
                <img class="logo" src="{{ILAB_PUB_IMG_URL}}/icon-cloud-w-type.svg">
                <nav>
                    <a class="previous invisible" href="#">Go Back</a>
                    <a class="next" href="#">Next</a>
                    <a class="return hidden" href="#">Return</a>
                </nav>
            </footer>
        </div>
        <a href="#" class="close-modal">Close</a>
    </div>
{{--    <div id="breakpoints"></div>--}}
    {{-- Used for debugging css breakpoints --}}
{{--    <div id="breakpoint-debug"></div>--}}
</div>

<script type="text/html" id="tmpl-step-template">
    <li>
        <div class="step-number">
            <span>@{{ data.index }}</span>
            <span class="back"><img src="{{ILAB_PUB_IMG_URL}}/wizard-check.svg"></span>
        </div>
        <input id="@{{ data.id }}-checkbox" type="checkbox">
        <div class="description">
            <# if (data.title != null) { #>
            <h3>@{{  data.title }}</h3>
            <# } #>
            <# if (data.description != null) { #>
            <div class="description-container">
                <p>@{{ data.description }}</p>
            </div>
            <# } #>
        </div>
    </li>
</script>