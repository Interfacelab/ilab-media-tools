@yield('top')

<div class="settings-container">
    <header>
        <img src="{{ILAB_PUB_IMG_URL}}/icon-cloud.svg">
        <h1>{{$title}}@yield('header-title')</h1>
        @yield('header')
    </header>
    <div class="settings-body @plan('free') show-upgrade @endplan">
        <div class="settings-interior">
            <div class="ilab-notification-container"></div>
            @yield('main')
        </div>
        @plan('free')
        @include('base/upgrade')
        @endplan
    </div>
</div>

