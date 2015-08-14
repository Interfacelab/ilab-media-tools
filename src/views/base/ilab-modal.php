<div id="ilabm-container-{{$modal_id}}" class="ilabm-backdrop">
    <div class="ilabm-container">
        <div class="ilabm-titlebar">
            <h1>{% content title %}</h1>
            <a title="{{__('Close')}}" href="javascript:ILabModal.cancel();" class="ilabm-modal-close">
                <span class="ilabm-modal-icon"></span>
            </a>
        </div>
        <div class="ilabm-window-area">
            <div class="ilabm-window-area-content">
                {% content main-tabs %}
                <div class="ilabm-editor-container">
                    <div class="ilabm-editor-area">
                        {% content editor %}
                    </div>
                </div>
                <div class="ilabm-bottom-bar">
                    <div class="ilabm-status-container is-hidden">
                        <span class="spinner is-active"></span>
                        <span class="ilabm-status-label">Saving ...</span>
                    </div>
                    {% content bottom-bar %}
                </div>
            </div>
            <div class="ilabm-sidebar">
                {% content sidebar-content %}
                {% content sidebar-actions %}
            </div>
        </div>
    </div>
</div>
{% content script %}
