<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/images/newlogo.svg') }}">
    <title>@yield('title', 'Employee Portal')</title>

    <!-- Simple bar CSS -->
    <link rel="stylesheet" href="{{ asset('css/simplebar.css') }}">
    <!-- Fonts CSS -->
    <link
        href="https://fonts.googleapis.com/css2?family=Overpass:wght@100;200;300;400;600;700;800;900&display=swap"
        rel="stylesheet">
    <!-- Icons CSS -->
    <link rel="stylesheet" href="{{ asset('css/feather.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="{{ asset('css/select2.css') }}">
    <!-- App CSS (same as admin for consistent UI + dark mode) -->
    <link rel="stylesheet" href="{{ asset('css/app-light.css') }}" id="lightTheme">
    <link rel="stylesheet" href="{{ asset('css/app-dark.css') }}" id="darkTheme" disabled>
    <script>
      (function() {
        var mode = localStorage.getItem("mode");
        var darkTheme = document.getElementById("darkTheme");
        var lightTheme = document.getElementById("lightTheme");
        if (darkTheme && lightTheme) {
          if (mode === "dark") {
            darkTheme.disabled = false;
            lightTheme.disabled = true;
          } else {
            darkTheme.disabled = true;
            lightTheme.disabled = false;
          }
        }
      })();
    </script>

    <style>
        body, * {
            transition: none !important;
        }
    </style>
    @yield('styles')
</head>

<body class="vertical light">
    <script>document.body.className = localStorage.getItem("mode") === "dark" ? "vertical dark" : "vertical light";</script>
    <div class="wrapper">
        <nav class="topnav navbar navbar-light">
            <button type="button" class="navbar-toggler text-muted mt-2 p-0 mr-3 collapseSidebar">
                <i class="fe fe-menu navbar-toggler-icon"></i>
            </button>

            <h5 class="navbar-brand mb-0 text-uppercase fw-bold text-primary">
                Financial Management System
            </h5>

            <ul class="nav">
                <li class="nav-item">
                    <a class="nav-link text-muted my-2" href="#" id="modeSwitcher" data-mode="light">
                        <i class="fe fe-sun fe-16"></i>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-muted pr-0" href="#" id="navbarDropdownMenuLink"
                        role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar avatar-sm mt-2">
                            <img src="{{ asset('assets/avatars/avatar.jpg') }}" alt="Profile" class="avatar-img rounded-circle">
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuLink">
                        <li class="dropdown-item-text small text-muted">{{ session('employee_name', 'Employee') }}</li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                            <form id="logout-form" action="{{ route('employee.logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>

        <aside class="sidebar-left border-right bg-white shadow" id="leftSidebar" data-simplebar>
            <a href="#" class="btn collapseSidebar toggle-btn d-lg-none text-muted ml-2 mt-3" data-bs-toggle="toggle">
                <i class="fe fe-x"><span class="sr-only"></span></i>
            </a>
            <nav class="vertnav navbar navbar-light">
                <div class="w-100 mb-4 d-flex">
                    <a class="navbar-brand mx-auto mt-2 flex-fill text-center" href="{{ route('employee.dashboard') }}">
                        <svg version="1.1" id="logo" class="navbar-brand-img brand-sm"
                            xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 120 120">
                            <g>
                                <polygon class="st0" points="78,105 15,105 24,87 87,87" />
                                <polygon class="st0" points="96,69 33,69 42,51 105,51" />
                                <polygon class="st0" points="78,33 15,33 24,15 87,15" />
                            </g>
                        </svg>
                    </a>
                </div>
                <ul class="navbar-nav flex-fill w-100 mb-2">
                    <li class="nav-item mb-3">
                        <a href="{{ route('employee.dashboard') }}" class="nav-link {{ Request::routeIs('employee.dashboard') ? 'active' : '' }}">
                            <i class="fe fe-home fe-16"></i>
                            <span class="ml-3 item-text">Dashboard</span>
                        </a>
                    </li>
                </ul>

                <p class="text-muted nav-heading mt-4 mb-1">
                    <span>Main Content</span>
                </p>
                <ul class="navbar-nav flex-fill w-100 mb-2">
                    <li class="nav-item mb-3">
                        <a href="{{ route('employee.dashboard') }}#analytics-section" class="nav-link">
                            <i class="fe fe-pie-chart fe-16"></i>
                            <span class="ml-3 item-text">Overview</span>
                        </a>
                    </li>
                    <li class="nav-item mb-3">
                        <a href="{{ route('employee.dashboard') }}#budget-section" class="nav-link">
                            <i class="fe fe-dollar-sign fe-16"></i>
                            <span class="ml-3 item-text">Budget Requests</span>
                        </a>
                    </li>
                    <li class="nav-item mb-3">
                        <a href="{{ route('employee.dashboard') }}#payment-section" class="nav-link">
                            <i class="fe fe-credit-card fe-16"></i>
                            <span class="ml-3 item-text">Payment Portal</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main role="main" class="main-content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Scripts (same as admin for theme switcher and sidebar) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/simplebar.min.js') }}"></script>
    <script src="{{ asset('js/tinycolor-min.js') }}"></script>
    <script src="{{ asset('js/config.js') }}"></script>
    <script src="{{ asset('js/apps.js') }}"></script>

    @yield('scripts')
</body>
</html>
