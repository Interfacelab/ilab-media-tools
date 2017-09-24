<div class="ic-Super-toggle--on-off checkbox-w-description">
    <input type="checkbox" id="ilab-media-tool-enabled-{{$name}}" name='ilab-media-tool-enabled-{{$name}}' value="1" class="ic-Super-toggle__input" {{($tool->enabled()) ? 'checked' : ''}}>
    <label class="ic-Super-toggle__label" for="ilab-media-tool-enabled-{{$name}}">
        <div class="ic-Super-toggle__screenreader">{{$tool->toolInfo['description']}}</div>
        <div class="ic-Super-toggle__disabled-msg" data-checked="On" data-unchecked="Off" aria-hidden="true"></div>
        <div class="ic-Super-toggle-switch" aria-hidden="true">
            <div class="ic-Super-toggle-option-LEFT" aria-hidden="true">
                <svg class="ic-Super-toggle__svg" xmlns="http://www.w3.org/2000/svg" version="1.1" x="0" y="0" width="548.9" height="548.9" viewBox="0 0 548.9 548.9" xml:space="preserve"><polygon points="449.3 48 195.5 301.8 99.5 205.9 0 305.4 95.9 401.4 195.5 500.9 295 401.4 548.9 147.5 "/></svg>
            </div>
            <div class="ic-Super-toggle-option-RIGHT" aria-hidden="true">
                <svg class="ic-Super-toggle__svg" xmlns="http://www.w3.org/2000/svg" version="1.1" x="0" y="0" viewBox="0 0 28 28" xml:space="preserve"><polygon points="28 22.4 19.6 14 28 5.6 22.4 0 14 8.4 5.6 0 0 5.6 8.4 14 0 22.4 5.6 28 14 19.6 22.4 28 " fill="#030104"/></svg>
            </div>
        </div>
    </label>
    <div>
        <p>{{$tool->toolInfo['description']}}</p>
        {% if (count($tool->toolInfo['dependencies'])>0) %}
        <p style="font-size:12px; margin-top:5px;">
            <strong>Requires:</strong>
		    <?php
		    $required=[];
		    foreach($tool->toolInfo['dependencies'] as $dep) {
			    if (is_array($dep)) {
				    $depTitles = [];
				    foreach($dep as $toolDep){
					    $depTitles[] = $manager->tools[$toolDep]->toolInfo['title'];
				    }

				    $required[] = implode(' and/or ', $depTitles);
			    } else {
				    $required[]=$manager->tools[$dep]->toolInfo['title'];
			    }
		    }
		    $required=implode(', ',$required);
		    ?>
            {{$required}}
        </p>
        {% endif %}
    </div>

</div>

