@extends('../templates/sub-page', ['title' => $title])

@section('main')
    <div class="settings-body mcloud-report-viewer">
        <div class="mcloud-report-selector">
            <label for="report">Report:</label>
            <select id="report" name="report">
                @foreach($allReports as $url => $name)
                <option value="{{$url}}">{{$name}}</option>
                @endforeach
            </select>
{{--            <button type="button" class="button button-small button-delete-report" data-nonce="{{wp_create_nonce('delete_report')}}">Delete Report</button>--}}
        </div>
        <div class="mcloud-report-grid-container">

        </div>
    </div>
@endsection
