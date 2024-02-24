<div class="row">
    <ul class="dashboard-menu">
        <li class="d-flex align-items-center">
            <a href="{{ route('user_dashboard') }}">
                {{ __('user.dashboard') }}
            </a>
        </li>
        <li class="d-flex align-items-center">
            <a href="{{ route('user_profile') }}">
                {{ __('user.profile') }}
            </a>
        </li>
        <li>
            <a href="{{ route('user_locations.list') }}">
                {{ __('user.locations') }}
            </a>
        </li>
        <li>
            <a href="{{ route('user_orders') }}">
                {{ __('user.orders') }}
            </a>
        </li>
    </ul>
</div>
