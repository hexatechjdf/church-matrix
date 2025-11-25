<ul class="metismenu left-sidenav-menu slimscroll" style="overflow-y:scroll; min-height:480px;">
    {{-- <li class="menu-label">Main</li> --}}
    <li class="leftbar-menu-item">
        <a href="{{ route('dashboard') }}" class="menu-link">
            <i data-feather="pie-chart" class="align-self-center vertical-menu-icon icon-dual-vertical"></i>
            <span>Dashboard</span>
        </a>
    </li>
    {{-- @if (auth()->user()->role == 0)
        <li class="leftbar-menu-item">
            <a href="{{ route('user.list') }}" class="menu-link">
                <i data-feather="pie-users" class="align-self-center vertical-menu-icon icon-dual-vertical"></i>
                <span >Users</span>
            </a>
        </li>
    @endif --}}
    @if (auth()->user()->role == 0)
        <li class="leftbar-menu-item">
            <a href="{{route('setting.add')}}" class="menu-link">
                <i data-feather="pie-users" class="align-self-center vertical-menu-icon icon-dual-vertical"></i>
                <span >Setting</span>
            </a>
        </li>
    @endif
</ul>

<!-- end left-sidenav-->
