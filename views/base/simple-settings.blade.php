@extends('../templates/sub-page', ['title' => $title])

@section('main')
    @network()
    <form action='edit.php?action=update_media_cloud_network_options' method='post' autocomplete="off">
    @else
    <form action='options.php' method='post' autocomplete="off">
    @endnetwork
        <?php
        settings_fields( $group );
        ?>
        @foreach($sections as $section)
            <a name="{{sanitize_title($section['title'])}}"></a>
            <div class="ilab-settings-section">
                @if(!empty($section['title']))
                    <h2>{{$section['title']}}</h2>
                @endif
                @if(!empty($section['description']))
                    <div class="section-description">{!! $section['description'] !!}</div>
                @endif
                <table class="form-table">
                    <?php do_settings_fields( $page, $section['id'] ) ?>
                </table>
            </div>
        @endforeach
        <div class="ilab-settings-button">
            <?php submit_button(); ?>
        </div>
    </form>
@endsection
