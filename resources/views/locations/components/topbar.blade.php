<div class="row">
    <div class="col-md-12">
        <nav class="navbar navbar-expand-lg navbar-light bg-light ">
            <div class="container-fluid">

                <!-- Left Side: Dashboard -->
                <a class="navbar-brand" href="javascript:;" class="fw-bold">
                    Dashboard
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav"
                    aria-controls="topNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Right Side Links -->
                <div class="collapse navbar-collapse justify-content-end" id="topNav">
                    <ul class="navbar-nav mb-2 mb-lg-0">

                        <!-- Settings -->
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('locations.planningcenter.index') ? 'active fw-bold text-primary' : '' }}"
                                href="{{ route('locations.planningcenter.index') }}">
                                Settings
                            </a>
                        </li>

                        <!-- Integrations -->
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('locations.planningcenter.headcount.visuals') ? 'active fw-bold text-primary' : '' }}"
                                href="{{ route('locations.planningcenter.headcount.visuals') }}">
                                Integrations
                            </a>
                        </li>

                    </ul>
                </div>
            </div>
        </nav>
    </div>
</div>
