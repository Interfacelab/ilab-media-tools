<?php /** @var \MediaCloud\Plugin\Wizard\Config\Field $field */?>
<div class="form-field">
    <label for="{{$field->name()}}">{{ $field->title() }}</label>
    <input type="number" name="{{$field->name()}}" id="{{$field->name()}}" value="{{$field->defaultValue()}}" min="{{$field->min()}}" max="{{$field->max()}}" step="{{$field->step()}}"  {{$field->required() ? 'required' : ''}}>
</div>
