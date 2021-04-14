<div class="info-panel-tabs">
    <ul>
        <li data-tab-target="debug-panel-tab-metadata" class="active">Metadata</li>
        <li data-tab-target="debug-panel-tab-file-audit">Metadata Audit</li>
    </ul>
</div>
<div class="info-panel-contents">
    <div id="debug-panel-tab-metadata">
        <label for="mcloud-debug-attached-file"><strong>Attached File</strong></label>
        <input type="text" class="widefat" style="margin-bottom:8px;" id="mcloud-debug-attached-file" name="mcloud-debug-attached-file" value="{{$attachedFile}}">
        <label for="mcloud-debug-metadata-editor"><strong>Metadata (JSON)</strong></label>
        <textarea id="mcloud-debug-metadata-editor" name="mcloud-debug-metadata-editor">@if(strpos($post->post_mime_type, 'image') !== 0){!! esc_textarea($ilab) !!}@else{!! esc_textarea($meta) !!}@endif</textarea>
        <div class="button-row">
            <button id="mcloud-debug-file-fix-metadata" type="button" class="button button-warning" style="margin-right: 8px">Fix Metadata</button>
            <button id="mcloud-debug-metadata-editor-update" type="button" class="button button-primary">Update</button>
        </div>
    </div>
    <div id="debug-panel-tab-file-audit" style="display: none;">
        <div style="margin-bottom: 16px">
            <button id="mcloud-debug-file-audit-start" type="button" class="button button-primary">Run Audit</button>
        </div>
        <div id="mcloud-debug-audit-result"></div>
    </div>
</div>
<script>
    jQuery(document).ready(function($) {
        let metaDataEditor = wp.codeEditor.initialize($('#mcloud-debug-metadata-editor'), cm_settings);

        let metadataUpdateButton = $('#mcloud-debug-metadata-editor-update');
        metadataUpdateButton.on('click', e => {
            e.preventDefault();

            if (!confirm("Are you sure you want to update the metadata for this attachment?")) {
                return false;
            }

            let data = {
                action: 'media_cloud_update_metadata',
                nonce: '{{wp_create_nonce('media_cloud_update_metadata')}}',
                post: {{$post->ID}},
                metadata: metaDataEditor.codemirror.getValue(),
                ilabS3: {{ (strpos($post->post_mime_type, 'image') !== 0) ? '1' : '0' }},
            };

            jQuery.post(ajaxurl, data, response => {
                document.location.reload();
            }).fail(response => {
                alert("There was an error updating the metadata.  Please try again later.");
            });

            return false;
        });

        let auditStartButton = $('#mcloud-debug-file-audit-start');
        let fixMetadataButton = $('#mcloud-debug-file-fix-metadata');

        auditStartButton.on('click', e => {
            e.preventDefault();
            auditStartButton.prop('disabled', true);
            fixMetadataButton.prop('disabled', true);
            auditStartButton.text('Running ...');

            let data = {
                action: 'media_cloud_audit_metadata',
                nonce: '{{wp_create_nonce('media_cloud_audit_metadata')}}',
                post: {{$post->ID}}
            };

            jQuery.post(ajaxurl, data, response => {
                console.log(response);
                $('#mcloud-debug-audit-result').html(response.html);
                auditStartButton.prop('disabled', false);
                fixMetadataButton.prop('disabled', false);
                auditStartButton.text('Run Audit');
            }).fail(response => {
                auditStartButton.prop('disabled', false);
                fixMetadataButton.prop('disabled', false);
                auditStartButton.text('Run Audit');
                alert("There was an error starting the audit.  Please try again later.");
            });

            return false;
        });

        fixMetadataButton.on('click', e => {
            e.preventDefault();
            if (!confirm("This will completely rebuild the metadata for this attachment.  Continue?")) {
                return false;
            }

            auditStartButton.prop('disabled', true);
            fixMetadataButton.prop('disabled', true);
            fixMetadataButton.text('Fixing ...');

            let data = {
                action: 'media_cloud_fix_metadata',
                nonce: '{{wp_create_nonce('media_cloud_fix_metadata')}}',
                post: {{$post->ID}}
            };

            jQuery.post(ajaxurl, data, response => {
                document.location.reload();
            }).fail(response => {
                auditStartButton.prop('disabled', false);
                fixMetadataButton.prop('disabled', false);
                fixMetadataButton.text('Fix Metadata');
                alert("There was an error fixing the metadata.  Please try again later.");
            });

            return false;
        });
    });
</script>