@extends('../templates/sub-page', ['title' => 'Media Cloud Log Viewer'])

@section('top')
<style>
    .wrap table th {
        background-color: #d7d7d7;
        padding: 10px;
    }
    .wrap table td {
        padding: 10px;
    }

    .tablenav {
        display: flex;
        margin-bottom: 10px;
        justify-content: space-between;
        width: 100%;
    }

    .tablenav.bottom {
        justify-content: flex-end;
    }

    .tablenav-pages {
    }

    .bulkactions {
        display: none;
    }

    .log-options {
        display: flex;
        flex: 1;
    }

    .column-channel {
        /*width: 140px !important;*/
    }

    .column-date {
        /*width: 140px !important;*/
    }

    .column-level {
        /*width: 60px !important;*/
        white-space: nowrap;
    }

    .column-class {
        /*width: 160px !important;*/
        white-space: nowrap;
    }

    .column-method {
        /*width: 160px !important;*/
        white-space: nowrap;
    }

    .column-line {
        /*width: 60px !important;*/
        white-space: nowrap;
    }

    .column-message {
        /*max-width: 100%;*/
        width: 75% !important;
    }

    .column-context {
        /*max-width: 100%;*/
        width: 25% !important;
    }
</style>
@endsection

@section('header')
    <div class="header-actions">
        <button type="button" class="button button-primary actionable" data-action="mcloud-debug-download-debug-log" data-action-type="file" data-nonce="{{wp_create_nonce('mcloud-debug-download-debug-log')}}" style="margin-right: 5px">Download Debug Log</button>
        <button type="button" class="button button-primary actionable" data-action="mcloud-debug-generate-system-report" data-action-type="file" data-nonce="{{wp_create_nonce('mcloud-debug-generate-system-report')}}">Download System Report</button>
{{--        <form method='post'><input type='hidden' name='action' value='csv'><input type='submit' style='display:inline-block; margin-right: 5px;' class='button button-primary' value='Download Debug Log'></form>--}}
{{--        <form method='post'><input type='hidden' name='action' value='bug'><input type='submit' style='display:inline-block; margin-right: 15px;' class='button button-primary' value='Download System Report'></form>--}}
    </div>
@endsection

@section('main')
    {!! $table->display()  !!}
@endsection
<script>
    (function($){
        $('#ilab-clear-log-form').on('submit', function(e) {
           if (!confirm('Are you sure you want to clear this log?')) {
               e.preventDefault();
               return false;
           }
        });
    })(jQuery);
</script>