@extends('../templates/sub-page')

@section('main')
    <div class="upgrade-feature">
        <h2>You are trying to use a Multisite license on a non-Multisite site</h2>
        <p>Oops!  The license you have entered is only valid for Multisite WordPress.  Please visit the pricing page to upgrade or downgrade to the correct non-multisite license.</p>
        <div class="button-container">
            <a href="{{network_admin_url('admin.php?page=media-cloud-pricing')}}">Change License</a>
        </div>
    </div>
@endsection
