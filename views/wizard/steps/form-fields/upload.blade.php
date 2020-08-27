<?php /** @var \MediaCloud\Plugin\Wizard\Config\Field $field */?>
<div class="form-field">
    <label for="{{$field->name()}}">{{ $field->title() }}</label>
    <input type="file" name="{{$field->name()}}" id="{{$field->name()}}" {{$field->required() ? 'required' : ''}}>
</div>
