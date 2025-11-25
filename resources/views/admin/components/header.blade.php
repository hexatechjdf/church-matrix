<div class="topbar">
    <!-- Navbar -->
    <nav class="navbar-custom">
        <div class="hamburger">
            
        </div>
        <ul class="list-unstyled topbar-nav float-right mb-0">

            <li class="dropdown">
                <a class="nav-link dropdown-toggle waves-effect waves-light nav-user" data-toggle="dropdown" href="#" role="button"
                    aria-haspopup="false" aria-expanded="false">
                    {{-- <img src="{{ asset('assets/images/users/dr-1.jpg') }}" alt="profile-user" class="rounded-circle" /> --}}
                    <span class="ml-1 nav-user-name hidden-sm">{{ auth()->user()->name }} <i class="mdi mdi-chevron-down"></i> </span>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="{{ route('profile') }}"><i class="dripicons-user text-muted mr-2"></i> Profile</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('logout') }}" ><i class="dripicons-exit text-muted mr-2"></i> Logout</a>
                </div>
            </li>

        </ul><!--end topbar-nav-->

        <ul class="list-unstyled topbar-nav mb-0">
            <li>
                <a href="{{ route('dashboard') }}">
                    <span class="responsive-logo">
                        <img src="{{ asset('mustard-main.png') }}" alt="logo-small" class="logo-sm align-self-center" height="34">
                    </span>
                </a>
            </li>
            <li>
                <button class="button-menu-mobile nav-link">
                    <img class="hamburger-icon" height="50" src="https://static.vecteezy.com/system/resources/thumbnails/002/292/406/small/hamburger-menu-line-icon-free-vector.jpg" alt="">
                </button>
            </li>

        </ul>
    </nav>
    <!-- end navbar-->
</div>
