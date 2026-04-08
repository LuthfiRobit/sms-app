<header class="pc-header">
    <div class="header-wrapper">
        <div class="me-auto pc-mob-drp">
            <ul class="list-unstyled">
                <li class="pc-h-item header-mobile-collapse">
                    <a href="#" class="pc-head-link head-link-secondary ms-0" id="sidebar-hide"><i
                            class="bi bi-list"></i></a>
                </li>
                <li class="pc-h-item pc-sidebar-popup">
                    <a href="#" class="pc-head-link head-link-secondary ms-0" id="mobile-collapse"><i
                            class="bi bi-list"></i></a>
                </li>
                <li class="pc-h-item d-none d-md-inline-flex">
                    <h5 class="mb-0 fw-bold"><span class="text-muted fw-light">Dashboard /</span>
                        @yield('title', 'Akademik')</h5>
                </li>
            </ul>
        </div>
        <div class="ms-auto">
            <ul class="list-unstyled">
                <!-- Notification Dropdown -->
                <li class="dropdown pc-h-item">
                    <a class="pc-head-link head-link-secondary dropdown-toggle arrow-none me-0"
                        data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <i class="bi bi-bell"></i>
                        <span class="badge bg-danger pc-h-badge" id="notification-badge-count">0</span>
                    </a>
                    <div class="dropdown-menu dropdown-notification dropdown-menu-end pc-h-dropdown">
                        <div class="dropdown-header">
                            <a href="{{ route('admin.notifikasi.index') }}" class="float-end text-muted small">Tandai semua dibaca</a>
                            <h5>Notifikasi</h5>
                        </div>
                        <div class="dropdown-header px-0 text-wrap header-notification-scroll position-relative"
                            style="max-height: calc(100vh - 215px)">
                            <div class="list-group list-group-flush w-100" id="notification-list-container">
                                <!-- Dynamic content via JS -->
                                <div class="list-group-item text-center py-4">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center py-2">
                            <a href="{{ route('admin.notifikasi.index') }}" class="link-primary">Lihat semua notifikasi</a>
                        </div>
                    </div>
                </li>
                <!-- User Profile -->
                <li class="dropdown pc-h-item header-user-profile">
                    <a class="pc-head-link head-link-primary dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown"
                        href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <img src="{{ asset('assets/sekolah-refaktor-template/images/user/avatar-2.jpg') }}" alt="user-image"
                            class="user-avtar" />
                    </a>
                    <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
                        <div class="dropdown-header">
                            <h4>Halo, <span
                                    class="small text-muted">{{ auth()->user()->name ?? 'Admin Sekolah' }}</span></h4>
                            <p class="text-muted">Administrator</p>
                            <hr />
                            <a href="#" class="dropdown-item"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="bi bi-box-arrow-right"></i><span>Logout</span>
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>