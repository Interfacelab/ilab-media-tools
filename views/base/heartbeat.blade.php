function mcloudHeartbeat() {
    jQuery.post(ajaxurl, { 'action': 'mcloud_task_heartbeat'});
    setTimeout(mcloudHeartbeat, {{$heartbeatFrequency}});
}

document.addEventListener('DOMContentLoaded', function(){
    mcloudHeartbeat();
});