<div id="mux-shortcode-wizard" class="wizard-hidden">
    <div class="shortcode-wizard-window">
        <header>
            <h1>Add Video</h1>
            <button type="button" class="close-button">Close</button>
        </header>
        <div class="contents">
            <div class="video-selector">
                <div class="video-container empty">

                </div>
                <button type="button" class="button mux-add-video">Select Video</button>
            </div>
            <h2>Options</h2>
            <ul class="options-grid">
                <li>
                    <div>
                        @include('base/ui/checkbox', ['name' => 'autoplay', 'value' => false, 'description' => '', 'enabled' => true])
                    </div>
                    <div>Autoplay</div>
                </li>
                <li>
                    <div>
                        @include('base/ui/checkbox', ['name' => 'loop', 'value' => false, 'description' => '', 'enabled' => true])
                    </div>
                    <div>Loop</div>
                </li>
                <li>
                    <div>
                        @include('base/ui/checkbox', ['name' => 'muted', 'value' => false, 'description' => '', 'enabled' => true])
                    </div>
                    <div>Muted</div>
                </li>
                <li>
                    <div>
                        @include('base/ui/checkbox', ['name' => 'controls', 'value' => true, 'description' => '', 'enabled' => true])
                    </div>
                    <div>Playback Controls</div>
                </li>
                <li>
                    <div>
                        @include('base/ui/checkbox', ['name' => 'inline', 'value' => false, 'description' => '', 'enabled' => true])
                    </div>
                    <div>Play Inline</div>
                </li>
            </ul>
            <div class="misc-options">
                <div>Preload</div>
                <select name="preload">
                    <option value="auto">Auto</option>
                    <option selected value="metadata">Metadata</option>
                    <option value="none">None</option>
                </select>
            </div>
        </div>
        <div class="actions">
            <button type="button" class="button insert-button">Insert Video</button>
        </div>
    </div>
</div>