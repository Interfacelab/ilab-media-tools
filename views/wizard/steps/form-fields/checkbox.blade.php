<?php /** @var \MediaCloud\Plugin\Wizard\Config\Field $field */?>
<div class="form-field field-checkbox">
    <div class="checkbox">
        @include('base/fields/checkbox', ['name' => $field->name(), 'value' => $field->defaultValue(), 'description' => '', 'conditions' => null])
    </div>
    <div class="title">
        {{ $field->title() }}
    </div>
    <div class="description">
        {!! $field->description() !!}
    </div>
</div>
