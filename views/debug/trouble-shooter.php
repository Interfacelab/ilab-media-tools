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
<div class="settings-container">
    <header>
        <img src="{{ILAB_PUB_IMG_URL}}/icon-cloud.svg">
        <h1>{{$title}}</h1>
    </header>
    <div class="settings-body">
        <p>This page will troubleshoot some basic settings to make sure that everything is configured and working properly.  It will upload a sample text file to your storage provider, verify it was uploaded correctly and then delete the file.</p>
        <p>If you are using Imgix, it will perform one last step to make sure that any uploaded images can be delivered by Imgix successfully.</p>
    </div>
    <div class="settings-body settings-body-flex">
        <button id="ilab-media-cloud-start-troubleshooting" class="button" title="Start Troubleshooting">Start Troubleshooting</button>
        <img id="ilab-media-cloud-spinner" src="{{admin_url('images/spinner-2x.gif')}}" width="18" height="18">
    </div>
    <div class="settings-body" id="ilab-media-cloud-troubleshooting-results">

    </div>
</div>
<script>
    (function($){
        var troubleshooting = false;

        function nextStep(step) {
            $.post(ajaxurl, { action: 'ilab_media_cloud_start_troubleshooting', step: step }, function(response){
                $('#ilab-media-cloud-troubleshooting-results').append(response.html);
                if (response.hasOwnProperty('next')) {
                    nextStep(response.next);
                } else {
                    troubleshooting = false;
                    $('#ilab-media-cloud-start-troubleshooting').attr('disabled',null);
                    $('#ilab-media-cloud-spinner').hide();
                }
            });
        }


        $(document).ready(function(){

            $('#ilab-media-cloud-start-troubleshooting').on('click',function(e){
                $(this).attr('disabled','disabled');

                e.preventDefault();

                if (troubleshooting) {
                    return false;
                }

                troubleshooting=true;

                $('#ilab-media-cloud-spinner').show();

                $.post(ajaxurl, { action: 'ilab_media_cloud_start_troubleshooting', step: 1 }, function(response){
                    $('#ilab-media-cloud-troubleshooting-results').css({display: 'block'});
                    $('#ilab-media-cloud-troubleshooting-results').html(response.html);
                    if (response.hasOwnProperty('next')) {
                        nextStep(response.next);
                    } else {
                        troubleshooting = false;
                        $('#ilab-media-cloud-start-troubleshooting').attr('disabled',null);
                        $('#ilab-media-cloud-spinner').hide();
                    }
                });

                return false;
            });
        });
    })(jQuery);
</script>