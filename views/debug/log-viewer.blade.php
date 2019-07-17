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
        width: 140px !important;
    }

    .column-date {
        width: 140px !important;
    }

    .column-level {
        width: 60px !important;
        white-space: nowrap;
    }
</style>
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