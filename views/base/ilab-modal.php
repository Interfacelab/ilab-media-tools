<div id="ilab-modal-container">
    <div id="ilab-modal-titlebar">
        <h1>{% content title %}</h1>
        <a title="{{__('Close')}}" href="javascript:ILabModal.cancel();" class="media-modal-close">
            <span class="media-modal-icon"></span>
        </a>
    </div>
    <div id="ilab-modal-window-area">
        <div id="ilab-modal-window-content-area">
            {% content main-tabs %}
            <div id="ilab-modal-editor-container">
                <div id="ilab-modal-editor-area">
                    {% content editor %}
                </div>
            </div>
        </div>
        <div id="ilab-modal-sidebar">
            {% content sidebar-tabs %}
            {% content sidebar-content %}
            {% content sidebar-actions %}
        </div>
    </div>
</div>

{% content script %}
