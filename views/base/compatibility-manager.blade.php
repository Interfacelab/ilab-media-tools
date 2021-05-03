<?php
/**
 * @var array $disabledHooks
 * @var array $availableHooks
 */
?>
@extends('../templates/sub-page', ['title' => 'Compatibility Manager'])

@section('main')
    <div class="settings-body mcloud-compatibility-manager">
        <div class="manager-description">
            <p>The compatibility manager allows you to disable hooks used by your active theme and plugins that may interfere with how Media Cloud functions.</p>
            <p>It's important to understand that a plugin showing up in the list below does not mean that it is incompatible with Media Cloud, it simply means that the plugin or theme is using the same hooks as Media Cloud which could potentially be a source of conflict.  For example, you will see WooCommerce and Elementor in the list below, but they are both certainly 100% compatible with Media Cloud.</p>
            <p>If you disable a hook in a plugin or theme, it's important that you test test test to make sure that disabling the hook doesn't cause any other side effects.</p>
        </div>
        @if(count($disabledHooks) > 0)
            <div class="hooks-list-container deactivated-hooks">
                <h2>Disabled Hooks</h2>
                <div class="hooks-list">
                    @foreach($disabledHooks as $hook)
                        <div class="hook" data-hash="{{$hook['hash']}}" data-nonce="{{wp_create_nonce('media_cloud_enable_hook')}}">
                            <h3>{{$hook['name']}} <span class="hook-type">{{$hook['type']}}</span></h3>
                            <div class="hook-name"><span class="hook-pill">Hook</span><strong>{{$hook['hook']}}</strong></div>
                            <div class="hook-file"><span class="hook-pill">File</span><code>{{ltrim($hook['filename'], '/')}}</code>, line {{$hook['line']}}</div>
                            <div class="hook-actions">
                                <select name="disable-type">
                                    <option {{($hook['disableType'] === 'both') ? 'selected': ''}} value="both">Disable on frontend and backend</option>
                                    <option {{($hook['disableType'] === 'frontend') ? 'selected': ''}} value="frontend">Disable on frontend only</option>
                                    <option {{($hook['disableType'] === 'backend') ? 'selected': ''}} value="backend">Disable on backend only</option>
                                </select>
                            </div>
                            <a href="#" class="hook-deactivate">
                                @inline('wizard-close-modal.svg')
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        @if(count($availableHooks) > 0)
            <div class="hooks-list-container available-hooks">
                <h2>Available</h2>
                <div class="hooks-list">
                    @foreach($availableHooks as $hook)
                    <div class="hook" data-hash="{{$hook['hash']}}" data-nonce="{{wp_create_nonce('media_cloud_disable_hook')}}">
                        <h3>{{$hook['name']}} <span class="hook-type">{{$hook['type']}}</span></h3>
                        <div class="hook-name"><span class="hook-pill">Hook</span><strong>{{$hook['hook']}}</strong></div>
                        <div class="hook-file"><span class="hook-pill">File</span><code>{{ltrim($hook['filename'], '/')}}</code>, line {{$hook['line']}}</div>
                        <div class="hook-actions">
                            <select name="disable-type">
                                <option value="both">Disable on frontend and backend</option>
                                <option value="frontend">Disable on frontend only</option>
                                <option value="backend">Disable on backend only</option>
                            </select>
                            <button type="button" class="button button-warning button-disable-hook">Disable Hook</button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif
        @if((count($availableHooks) == 0) && (count($disabledHooks) == 0))
        @endif
    </div>
@endsection
