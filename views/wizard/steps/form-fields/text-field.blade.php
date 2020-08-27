<?php /** @var \MediaCloud\Plugin\Wizard\Config\Field $field */?>
<div class="form-field">
    <label for="{{$field->name()}}">{{ $field->title() }}</label>
    <input type="text" name="{{$field->name()}}" placeholder="{{$field->title()}}" id="{{$field->name()}}" value="{{$field->defaultValue()}}" {{$field->required() ? 'required' : ''}}>
</div>
