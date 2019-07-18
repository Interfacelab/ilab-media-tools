<div id="ilabm-container-@yield('modal_id')" class="ilabm-backdrop">
    <div class="ilabm-container">
        <div class="ilabm-titlebar">
            <h1>@yield('title')</h1>
            <a title="{{__('Close')}}" href="javascript:ILabModal.cancel();" class="ilabm-modal-close">
                <span class="ilabm-modal-icon"></span>
            </a>
        </div>
        <div class="ilabm-window-area">
            <div class="ilabm-window-area-content">
                @yield('main-tabs')
                <div class="ilabm-editor-container">
                    <div class="ilabm-editor-area">
                        @yield('editor')
                    </div>
                </div>
                <div class="ilabm-bottom-bar">
                    <div class="ilabm-status-container is-hidden">
                        <span class="spinner is-active"></span>
                        <span class="ilabm-status-label">Saving ...</span>
                    </div>
                    @yield('bottom-bar')
                </div>
            </div>
            <div class="ilabm-sidebar">
                @yield('sidebar-content')
                @yield('sidebar-actions')
            </div>
        </div>
    </div>
</div>
@yield('script')
