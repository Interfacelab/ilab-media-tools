<?php /** @var \MediaCloud\Plugin\Wizard\Config\Field $field */?>
<div class="form-field">
    <label for="{{$field->name()}}">{{ $field->title() }}</label>
    <div style="display:none">
        <input type="password" tabindex="-1">
    </div>
    <input type="password" placeholder="{{$field->title()}}" name="{{$field->name()}}" id="{{$field->name()}}" value="{{$field->defaultValue()}}"  {{$field->required() ? 'required' : ''}}>
</div>
