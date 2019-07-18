<div class="ic-Super-toggle--on-off checkbox-w-description {{($tool->envEnabled() && !$tool->enabled()) ? 'toggle-warning' : ''}}">
    @include('base/fields/enable-toggle-checkbox', ['name' => $name, 'tool' => $tool])
    <div>
        @include('base/fields/enable-toggle-description', ['name' => $name, 'tool' => $tool, 'manager' => $manager])
    </div>

</div>

