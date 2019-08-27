@extends('../templates/sub-page', ['title' => $title])

@section('main')
    <div class="settings-body">
        <div id="s3-importer-manual-warning" style="display:none">
            <p><strong>IMPORTANT:</strong> You are running the import process in the web browser.  <strong>Do not navigate away from this page or the import may not finish.</strong></p>
        </div>
        <div id="s3-importer-instructions" {!! ($status=="running") ? 'style="display:none"':'' !!}>
            {!! $instructions !!}
            @if(!empty($warning))
            <div class="s3-importer-info-warning">
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
            @if(!empty($options))
                <div id="s3-importer-options">
                    <h3>Options</h3>
                    <ul>
                        @foreach($options as $optionName => $option)
                            <li>
                                <div>
                                    {!! $option['title'] !!}
                                </div>
                                <div>
                                    <div class="option-ui option-ui-{{$option['type']}}">
                                    @if($option['type'] == 'checkbox')
                                        @include('base/ui/checkbox', ['name' => $optionName, 'value' => $option['default'], 'description' => '', 'enabled' => true])
                                    @elseif($option['type'] == 'select')
                                        <select name="{{$optionName}}">
                                            @foreach($option['options'] as $suboptionValue => $suboptionName)
                                            <option value="{{$suboptionValue}}">{{$suboptionName}}</option>
                                            @endforeach
                                        </select>
                                    @elseif($option['type'] == 'browser')
                                        <input type="text" name="{{$optionName}}" disabled="disabled" value="{{$option['default']}}"><button type="button" class="button button-small button-primary" data-nonce="{{wp_create_nonce('storage-browser')}}">Browse</button>
                                    @endif
                                    </div>
                                    <div class="description">{!! $option['description'] !!}</div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div style="margin-top: 2em;">
                @if($enabled)
                    <div style="display:flex; align-items:center">
                        <a id="s3-importer-start-import" href="#" class="ilab-ajax button button-primary">{{$commandTitle}}</a><img id="s3-importer-start-spinner" src="{!! admin_url('/images/spinner-2x.gif') !!}" height="24px" style="margin-left:10px; display: none;">
                    </div>
                @else
                    <strong class="tool-disabled">Please <a href="admin.php?page=media-tools-top">{{$disabledText}}</a> before using this tool.</strong>
                @endif
            </div>

        </div>
        <div id="s3-importer-progress" {!! ($status!="running") ? 'style="display:none"':'' !!}>
            <div id="s3-importer-thumbnails">
                <div id="s3-importer-thumbnails-container">
                </div>
                <div id="s3-importer-thumbnails-fade"></div>
                <img id="s3-importer-thumbnails-cloud" src="{{ILAB_PUB_IMG_URL}}/icon-cloud.svg">
            </div>
            <div class="s3-importer-progress-container">
                <div id="s3-importer-progress-bar"></div>
                <div id="s3-importer-status-text">

                </div>
            </div>
            <button id="s3-importer-cancel-import" class="button button-whoa" title="Cancel">{{$cancelCommandTitle}}</button>
        </div>
    </div>
    @track('mcloud-opt-in-crisp', 'pro')
    @include('support.crisp')
    @endtrack
@endsection

<script src="{{ILAB_PUB_JS_URL}}/mcloud-admin.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function(){
        new Importer({
            batchType: '{{$batchType}}',

            commandTitle: "{{$commandTitle}}",
            statusText: "",

            systemTestUrl: "{{admin_url('admin.php?page=media-tools-troubleshooter')}}",

            importing: {{($status == 'running') ? 'true' : 'false'}},
            backgroundImport: {{ ($background) ? 'true' : 'false' }},
            fromSelection: {{ ($fromSelection) ? 'true' : 'false' }},

            index: 0,
            currentPage: 0,
            currentBatch: {
                posts: [],
                total: {{$total}},
                pages: 0,
                shouldRun: {{($shouldRun) ? 'true' : 'false'}},
                fromSelection: {{($fromSelection) ? 'true' : 'false'}}
            },

            currentFile: null,
            timingInfo: {
                totalTime: {{$totalTime}},
                postsPerSecond: {{$postsPerMinute / 60.0}},
                postsPerMinute: {{$postsPerMinute}},
                eta: {{$eta}}
            },

            nonce: "{{wp_create_nonce('importer-action')}}",
            nextBatchAction: "{{$nextBatchAction}}",
            manualAction: "{{$manualAction}}",
            startAction: "{{$startAction}}",
            cancelAction: "{{$cancelAction}}",
            progressAction: "{{$progressAction}}",

            thumbnailContainer: document.getElementById('s3-importer-thumbnails-container'),
            startButton: document.getElementById('s3-importer-start-import'),
            cancelButton: document.getElementById('s3-importer-cancel-import'),
            spinner: document.getElementById('s3-importer-start-spinner'),
            optionsContainer: document.getElementById('s3-importer-options'),
            instructionsContainer: document.getElementById('s3-importer-instructions'),
            manualWarningContainer: document.getElementById('s3-importer-manual-warning'),

            progressContainer: document.getElementById('s3-importer-progress'),
            progressBar: document.getElementById('s3-importer-progress-bar'),
            statusTextContainer: document.getElementById('s3-importer-status-text')
        });
    });
</script>