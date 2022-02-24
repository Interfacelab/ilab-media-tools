@extends('../templates/sub-page', ['title' => $title])

@section('top')
<style>
    #ilab-media-cloud-troubleshooting-results {
        display: none;
    }

    .troubleshooter-step {
        display: flex;
        margin-top: 30px;
        font-size: 1.1em;
    }

    .troubleshooter-step-icon {
        margin-right: 10px;
    }

    .troubleshooter-message {
        flex: 1;
    }

    .troubleshooter-title {
        font-weight: bold;
        text-transform: uppercase;
        opacity: 0.8;
    }

    .troubleshooter-errors {
        margin-left: 30px;
        list-style: disc;
    }

    .settings-body-flex {
        display: flex;
        align-items: center;
    }

    #ilab-media-cloud-spinner {
        margin-left: 12px;
        display: none;
    }
</style>
@endsection

@section('main')
    <div class="settings-body">
        <p>This page will troubleshoot some basic settings to make sure that everything is configured and working properly.  It will upload a sample text file to your storage provider, verify it was uploaded correctly and then delete the file.</p>
        <p>If you are using Imgix, it will perform one last step to make sure that any uploaded images can be delivered by Imgix successfully.</p>
    </div>
    <div class="settings-body settings-body-flex">
        <select id="ilab-media-cloud-troubleshooting-start-step" style="margin-right:5px">
            <option value="-1">Run all tests</option>
            <option value="1">System compatibility</option>
            <option value="2">Plugin and theme compatibility</option>
            <option value="3">Database installed</option>
            <option value="5">Storage settings</option>
            <option value="8">Background connectivity</option>
        </select>
        <button id="ilab-media-cloud-start-troubleshooting" class="button" title="Start Testing">Start Testing</button>
        <img id="ilab-media-cloud-spinner" src="{{admin_url('images/spinner-2x.gif')}}" width="18" height="18">
    </div>
    <div class="settings-body" id="ilab-media-cloud-troubleshooting-results">
    </div>
    <div id="ilab-media-cloud-troubleshooter-wait" class="settings-body hidden">
        <div class="troubleshooter-step">
            <div class="troubleshooter-step-icon">
                <img src="{!! admin_url('/images/spinner-2x.gif') !!}" height="32px">
            </div>
            <div>
                <div id="wait-title" class="troubleshooter-title">Test Background Tasks</div>
                <div id="wait-message" class="troubleshooter-message">
                    Your WordPress server configuration supports background tasks.
                </div>
            </div>
        </div>
    </div>
@endsection

<script>
    (function($){
        var troubleshooting = false;
        var troubleshootingRan = false;

        function nextStep(step) {
            if (troubleshootingRan && ([5,6,7,9].indexOf(step) === -1) && ($('#ilab-media-cloud-troubleshooting-start-step').val() != -1)) {
                troubleshooting = false;
                troubleshootingRan = false;

                console.log('dafoook');

                $('#ilab-media-cloud-troubleshooter-wait').addClass('hidden');

                $('#ilab-media-cloud-start-troubleshooting').attr('disabled',null);
                $('#ilab-media-cloud-spinner').hide();

                return;
            }

            troubleshootingRan = true;

            $.post(ajaxurl, {
                action: 'ilab_media_cloud_start_troubleshooting',
                step: step,
                singleStep: ($('#ilab-media-cloud-troubleshooting-start-step').val() == -1)
            }, response => {
                console.log(response);

                if (response.hasOwnProperty('html')) {
                    $('#ilab-media-cloud-troubleshooting-results').append(response.html);
                }

                if (response.hasOwnProperty('title')) {
                    $('#wait-title').text(response.title);
                }

                if (response.hasOwnProperty('status')) {
                    $('#wait-message').text(response.status);
                }

                if (response.hasOwnProperty('next')) {
                    $('#ilab-media-cloud-troubleshooter-wait').removeClass('hidden');

                    $('#wait-title').text(response.next.title);
                    $('#wait-message').text(response.next.status);

                    nextStep(response.next.index);
                } else {
                    troubleshooting = false;
                    troubleshootingRan = false;

                    $('#ilab-media-cloud-troubleshooter-wait').addClass('hidden');

                    $('#ilab-media-cloud-start-troubleshooting').attr('disabled',null);
                    $('#ilab-media-cloud-spinner').hide();
                }
            });
        }

        $(document).ready(function(){

            $('#ilab-media-cloud-start-troubleshooting').on('click',function(e){
                if (troubleshooting) {
                    console.log('no');
                    return false;
                }

                troubleshooting=true;

                e.preventDefault();
                $(this).attr('disabled','disabled');

                $('#ilab-media-cloud-spinner').show();
                $('#ilab-media-cloud-troubleshooting-results').html('');
                $('#ilab-media-cloud-troubleshooting-results').css({display: 'block'});

                if ($('#ilab-media-cloud-troubleshooting-start-step').val() == -1) {
                    console.log('next 1');
                    nextStep(1);
                } else {
                    console.log('next '+$('#ilab-media-cloud-troubleshooting-start-step').val());
                    nextStep($('#ilab-media-cloud-troubleshooting-start-step').val())
                }

                return false;
            });
        });
    })(jQuery);
</script>