@extends('../templates/sub-page')

@section('main')
    @network()
    <form action='edit.php?action=update_media_cloud_network_options' method='post' autocomplete="off">
    @else
    <form action='options.php' method='post' autocomplete="off">
    @endnetwork
        <?php
        settings_fields( $group );
        ?>
        @if(is_multisite() && is_network_admin())
        <div class="ilab-settings-section ilab-settings-features">
            <table class="form-table">
                <tr>
                    <td class="toggle">
                        <div class="ic-Super-toggle--on-off">
                            @include('base/fields/checkbox', ['name' => 'mcloud-network-mode', 'value' => $networkMode, 'description' => '', 'conditions' => null])
                        </div>
                        <div class="title">
                            Network Mode
                        </div>
                    </td>
                    <td class="description">
                        <p>Turning this value on means that all sites in your network will share the same Media Cloud configuration.  Additionally, the individual sites will not be able to see or change this configuration.  The use of the plugin will be, for the most part, transparent to them.  They will still have access to certain batch tools and direct uploads.</p>
                    </td>
                </tr>
            </table>
        </div>
        @endif
        <div class="ilab-settings-section ilab-settings-features">
            <table class="form-table">
                <?php /** @var $tool \ILAB\MediaCloud\Tools\Tool */ ?>
                @foreach($tools as $key => $tool)
                    @if(!empty($tool->toolInfo['exclude']))
                        @continue
                    @endif
                <tr>
                    <td class="toggle">
                        <div class="ic-Super-toggle--on-off {{($tool->envEnabled() && !$tool->enabled()) ? 'toggle-warning' : ''}}">
                            @include('base/fields/enable-toggle-checkbox', ['name' => $key, 'tool' => $tool])
                        </div>
                        <div class="title">
                            {{$tool->toolInfo['name']}}
                            @if($tool->hasSettings())
                            <a href="{{ilab_admin_url("admin.php?page=media-cloud-settings&tab=$key")}}">Settings</a>
                            @endif
                        </div>
                    </td>
                    <td class="description">
                        @include('base/fields/enable-toggle-description', ['name' => $key, 'tool' => $tool, 'manager' => $manager])
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
        <div class="ilab-settings-button">
            <?php submit_button(); ?>
        </div>
    </form>
@endsection
