<?php /** @var \MediaCloud\Plugin\Wizard\Config\Field $field */?>
<div class="form-field">
    <label for="{{$field->name()}}">{{ $field->title() }}</label>
    <select name="{{$field->name()}}" id="{{$field->name()}}" value="{{$field->defaultValue()}}" required>
        @foreach($field->options() as $value => $name)
        <option value="{{$value}}" {{($value == $field->defaultValue()) ? 'selected' : ''}}>{{$name}}</option>
        @endforeach
    </select>
</div>
