<?php /** @var \ILAB\MediaCloud\Wizard\Config\Field $field */?>
<div class="form-field">
    <label for="{{$field->name()}}">{{ $field->title() }}</label>
    <div style="display:none">
        <input type="password" tabindex="-1">
    </div>
    <input type="password" placeholder="{{$field->title()}}" name="{{$field->name()}}" id="{{$field->name()}}" value="{{$field->default()}}"  {{$field->required() ? 'required' : ''}}>
</div>
