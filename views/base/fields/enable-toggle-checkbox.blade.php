<input type="checkbox" id="mcloud-tool-enabled-{{$name}}" name='mcloud-tool-enabled-{{$name}}' value="1" class="ic-Super-toggle__input" {{($tool->envEnabled()) ? 'checked' : ''}}>
<label class="ic-Super-toggle__label" for="mcloud-tool-enabled-{{$name}}">
    <div class="ic-Super-toggle__screenreader">{{$tool->toolInfo['description']}}</div>
    <div class="ic-Super-toggle__disabled-msg" data-checked="On" data-unchecked="Off" aria-hidden="true"></div>
    <div class="ic-Super-toggle-switch" aria-hidden="true">
        <div class="ic-Super-toggle-option-LEFT" aria-hidden="true">
            @if($tool->envEnabled() && !$tool->enabled())
                <svg class="ic-Super-toggle__svg" x="0" y="0" width="109px" height="378px" viewBox="0 0 109 378" version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve"><path d="M26.5,241 L0.5,0 L108.5,0 L82,241 L26.5,241 Z M5.5,378 L5.5,280 L103.5,280 L103.5,378 L5.5,378 Z" id="!" fill="#000000"></path></svg>
            @else
                <svg class="ic-Super-toggle__svg" xmlns="http://www.w3.org/2000/svg" version="1.1" x="0" y="0" width="548.9" height="548.9" viewBox="0 0 548.9 548.9" xml:space="preserve"><polygon points="449.3 48 195.5 301.8 99.5 205.9 0 305.4 95.9 401.4 195.5 500.9 295 401.4 548.9 147.5 "/></svg>
            @endif
        </div>
        <div class="ic-Super-toggle-option-RIGHT" aria-hidden="true">
            <svg class="ic-Super-toggle__svg" xmlns="http://www.w3.org/2000/svg" version="1.1" x="0" y="0" viewBox="0 0 28 28" xml:space="preserve"><polygon points="28 22.4 19.6 14 28 5.6 22.4 0 14 8.4 5.6 0 0 5.6 8.4 14 0 22.4 5.6 28 14 19.6 22.4 28 " fill="#030104"/></svg>
        </div>
    </div>
</label>