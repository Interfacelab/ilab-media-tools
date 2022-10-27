<?php /** @var string $instructions */ ?>
<?php /** @var string $warning */ ?>
<?php /** @var string $commandLine */ ?>
<?php /** @var string $commandLink */ ?>
<?php /** @var string $taskClass */ ?>
<?php /** @var string $instructionsView */ ?>
<?php
use function \MediaCloud\Plugin\Utilities\arrayPath;
?>

@include($instructionsView)
@if(!empty($warning))
    <div class="info-warning">
        <h4>Warning</h4>
        {!! $warning !!}
    </div>
@endif
@if(!empty($commandLine))
    <div class="wp-cli-callout">
        <h3>Using WP-CLI</h3>
        <p>You can run this importer process from the command line using <a href="https://wp-cli.org" target="_blank">WP-CLI</a>:</p>
        <code>
            {{$commandLine}}
        </code>
        @if(!empty($commandLink))
            <p><a href="{{$commandLink}}" target="_blank">Command documentation</a></p>
        @endif
    </div>
@endif
@if(!empty($taskClass::taskOptions()))
    <div id="task-options" class="task-options">
        <h3>Options</h3>
        <ul>
            @foreach($taskClass::taskOptions() as $optionName => $option)
                <li>
                    <div>
                        {!! $option['title'] !!}
                    </div>
                    <div style="width:100%">
                        <div class="option-ui option-ui-{{$option['type']}}">
                            @if($option['type'] == 'checkbox')
                                @include('base/ui/checkbox', ['name' => $optionName, 'value' => $option['default'], 'description' => '', 'enabled' => true])
                            @elseif($option['type'] == 'select')
                                <select name="{{$optionName}}">
                                    <?php $defaultValue = arrayPath($option, 'default', 'null'); ?>
                                    @foreach($option['options'] as $suboptionValue => $suboptionName)
                                        <option {{($defaultValue == $suboptionValue) ? 'selected' : ''}} value="{{$suboptionValue}}">{{$suboptionName}}</option>
                                    @endforeach
                                </select>
                            @elseif($option['type'] == 'text')
                                <input style="width: 100%; max-width: 500px;" type="text" name="{{$optionName}}" value="{{arrayPath($option, 'default', '')}}" placeholder="{{arrayPath($option, 'placeholder', '')}}">
                            @elseif($option['type'] == 'url')
                                <input style="width: 100%; max-width: 500px;" type="url" name="{{$optionName}}" value="{{arrayPath($option, 'default', '')}}" placeholder="{{arrayPath($option, 'placeholder', '')}}">
                            @elseif($option['type'] == 'browser')
                                <input type="text" name="{{$optionName}}" disabled="disabled" value="{{$option['default']}}"><button type="button" class="button button-small button-primary" data-nonce="{{wp_create_nonce('storage-browser')}}">Browse</button>
                            @elseif($option['type'] == 'media-select')
                                <div id="{{$optionName}}-display" class="media-select-label">All Media Items</div><input type="hidden" name="{{$optionName}}"><button type="button" class="button button-small button-primary button-select-media" @if(!empty($option['media-types']))data-media-types="{{implode(',',$option['media-types'])}}" @endif>Select Media</button><button type="button" class="button button-small button-primary button-clear-media">Clear Selection</button>
                            @endif
                        </div>
                        <div class="description">{!! $option['description'] !!}</div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endif