 <div class="col-md-12 text-left mt-4">

                <a href="{{ route('locations.churchmatrix.index') }}"
                    class="btn btn-gradient-success px-4 mt-0 mb-3 import-users {{ request()->routeIs('locations.churchmatrix.index') ? 'active-tab' : '' }}">
                    <i class="mdi mdi-plus-circle-outline mr-2"></i>Setting
                </a>

                {{-- <a href=""
                    class="btn btn-gradient-primary px-4 mt-0 mb-3">
                    <i class="mdi mdi-plus-circle-outline mr-2"></i>Integration</a> --}}


                <a href="{{ route('locations.churchmatrix.integration.events.index') }}"
                    class="btn btn-gradient-primary px-4 mt-0 mb-3 {{ request()->routeIs('locations.churchmatrix.integration*') ? 'active-tab' : '' }}">
                    <i class="mdi mdi-plus-circle-outline mr-2"></i>Integration</a>
            </div>

<style>
    .btn.active-tab {
        position: relative;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        transform: translateY(-2px);
        font-weight: 600;
    }

    .btn.active-tab::before {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 50%;
        transform: translateX(-50%);
        width: 60%;
        height: 4px;
        background: currentColor;
        border-radius: 2px;
        animation: slideIn 0.3s ease;
    }

    .btn.active-tab::after {
        content: '✓';
        position: absolute;
        top: -8px;
        right: -8px;
        background: #28a745;
        color: white;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
        box-shadow: 0 2px 8px rgba(40, 167, 69, 0.4);
        animation: popIn 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    @keyframes slideIn {
        from {
            width: 0;
            opacity: 0;
        }
        to {
            width: 60%;
            opacity: 1;
        }
    }

    @keyframes popIn {
        0% {
            transform: scale(0);
            opacity: 0;
        }

        50% {
            transform: scale(1.2);
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .btn {
        transition: all 0.3s ease;
        position: relative;
    }

    .btn:not(.active-tab):hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
    }

    .btn.active-tab {
        border: 2px solid rgba(255, 255, 255, 0.3);
    }
</style>
