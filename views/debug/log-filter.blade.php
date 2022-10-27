<div class='log-options' style="display: flex; align-items: center">
    <form method="get" style="display: flex; align-items: center">
        <input type="hidden" name="page" value="media-tools-debug-log">
        @if(isset($_REQUEST['paged']))
        <input type="hidden" name="paged" value="{{ isset($_REQUEST['paged']) ? intval($_REQUEST['paged']) : 1 }}">
        @endif
        <select id="mcloud-log-level" name="level" style="margin-right: 5px;">
            <option value="">Any Log Level</option>
            <option value="INFO" {{ (isset($_REQUEST['level']) && ($_REQUEST['level'] === 'INFO')) ? 'selected' : '' }}>Info</option>
            <option value="WARNING" {{ (isset($_REQUEST['level']) && ($_REQUEST['level'] === 'WARNING')) ? 'selected' : '' }}>Warning</option>
            <option value="ERROR" {{ (isset($_REQUEST['level']) && ($_REQUEST['level'] === 'ERROR')) ? 'selected' : '' }}>Error</option>
        </select>
        <select id="mcloud-log-class" name="class" style="margin-right: 5px;">
            <option value="">Any Class</option>
            <option value="not-empty" {{ (isset($_REQUEST['class']) && ($_REQUEST['class'] === 'not-empty')) ? 'selected' : '' }}>Not Empty</option>
            <option value="empty" {{ (isset($_REQUEST['class']) && ($_REQUEST['class'] === 'empty')) ? 'selected' : '' }}>Empty</option>
            <optgroup label="Specific Classes">
                @foreach($classes as $class)
                    <option value="{{$class['class']}}"  {{ (isset($_REQUEST['class']) && ($_REQUEST['class'] === $class['class'])) ? 'selected' : '' }}>{{$class['class']}}</option>
                @endforeach
            </optgroup>
        </select>
        <input id="mcloud-search-text" name="message" placeholder="Search text" style="margin-right: 5px; padding: 6px 8px; border-radius: 3px; border: 1px solid #8c8f94;" value="{{(isset($_REQUEST['message'])) ? $_REQUEST['message'] : ''}}">
        <input type="submit" value="Filter" class="button">
    </form>
    <div style="margin: 0 10px; opacity: 0.5;">|</div>
    <button type="button" class="button button-primary actionable" data-action="mcloud-debug-clear-debug-log" data-action-type="reload" data-nonce="{{wp_create_nonce('mcloud-debug-clear-debug-log')}}">Clear Log</button>
    <div style="margin: 0 10px; opacity: 0.5;">|</div>
    <div style="display: flex; align-items:center">
        @include('base/ui/checkbox', ['name' => 'mcloud-realtime-log', 'value' => false, 'description' => '', 'enabled' => true, 'toggleClass' => 'small-toggle', 'extraData' => [ 'nonce' => wp_create_nonce('mcloud-get-debug-log')]])
        <div style="margin-left:8px;">Real-time View</div>
    </div>
{{--    <form id='ilab-clear-log-form' method='post'><input type='hidden' name='action' value='clear-log'><input type='submit' style='display:inline-block' class='button button-warning' value='Clear Log'></form>--}}
</div>