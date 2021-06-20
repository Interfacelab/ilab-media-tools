{{--<script>--}}
let mcloudHeartbeatTimeout = null;

function mcloudHeartbeat() {
    let currentPulseTime = parseInt(window.localStorage.getItem('mcloudpulseTime'));

    if ((currentPulseTime > 0) && (Date.now() - currentPulseTime < {{$heartbeatFrequency}})) {
{{--        console.log('has pulsed recently');--}}
        mcloudHeartbeatTimeout = setTimeout(mcloudHeartbeat, {{$heartbeatFrequency * 2}});
    } else {
{{--        console.log('sending pulse');--}}
        window.localStorage.setItem('mcloudpulseUrl', window.location.href);
        window.localStorage.setItem('mcloudpulseTime', Date.now());
        jQuery.post(ajaxurl, { 'action': 'mcloud_task_heartbeat'});
        mcloudHeartbeatTimeout = setTimeout(mcloudHeartbeat, {{$heartbeatFrequency}});
    }
}

document.addEventListener('DOMContentLoaded', function(){
    window.addEventListener('storage', e => {
        if ((e.storageArea !== window.localStorage) || (e.key !== 'mcloudpulseTime')) {
            return;
        }

        let url = window.localStorage.getItem('mcloudpulseUrl');
        let time = window.localStorage.getItem('mcloudpulseTime');
{{--        console.log(url, time);--}}
        if (url === window.location.href) {
{{--            console.log('same page ignoring');--}}
            return;
        }

{{--        console.log('pulse event');--}}
        clearTimeout(mcloudHeartbeatTimeout);
        mcloudHeartbeatTimeout = setTimeout(mcloudHeartbeat, {{$heartbeatFrequency * 2}});
    });

{{--    console.log('starting pulse');--}}
    mcloudHeartbeat();
});
{{--</script>--}}