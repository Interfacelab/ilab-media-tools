<div class="ilab-modal-backdrop"></div>
<div id="ilab-modal-container-{{$modal_id}}" class="ilab-modal-container">
    <div class="ilab-modal-titlebar">
        <h1>{% content title %}</h1>
        <a title="{{__('Close')}}" href="javascript:ILabModal.cancel();" class="media-modal-close">
            <span class="media-modal-icon"></span>
        </a>
    </div>
    <div id="ilab-modal-window-area">
        <div class="ilab-modal-window-content-area">
            {% content main-tabs %}
            <div class="ilab-modal-editor-container">
                <div class="ilab-modal-editor-area">
                    {% content editor %}
                </div>
            </div>
            <div class="ilab-modal-bottom-bar">
                <div class="ilab-status-container is-hidden">
                    <span class="spinner is-active"></span>
                    <span class="ilab-status-label">Saving ...</span>
                </div>
                {% content bottom-bar %}
            </div>
        </div>
        <div class="ilab-modal-sidebar">
            {% content sidebar-content %}
            {% content sidebar-actions %}
        </div>
    </div>
</div>

{% content script %}
