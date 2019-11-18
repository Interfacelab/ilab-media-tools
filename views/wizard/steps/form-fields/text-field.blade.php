<?php /** @var \ILAB\MediaCloud\Wizard\Config\Field $field */?>
<div class="form-field">
    <label for="{{$field->name()}}">{{ $field->title() }}</label>
    <input type="text" name="{{$field->name()}}" placeholder="{{$field->title()}}" id="{{$field->name()}}" value="{{$field->default()}}" {{$field->required() ? 'required' : ''}}>
</div>
