<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="{{ asset('assets/images/newlogo.svg') }}">
    <title>@yield('title', 'HR Dashboard')</title>

    <!-- Simple bar CSS -->
    <link rel="stylesheet" href="{{ asset('css/simplebar.css') }}">
    <!-- Fonts CSS -->
    <link
        href="https://fonts.googleapis.com/css2?family=Overpass:wght@100;200;300;400;600;700;800;900&display=swap"
        rel="stylesheet">
    <!-- Icons CSS -->
    <link rel="stylesheet" href="{{ asset('css/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('css/select2.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dropzone.css') }}">
    <link rel="stylesheet" href="{{ asset('css/uppy.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/jquery.steps.css') }}">
    <link rel="stylesheet" href="{{ asset('css/jquery.timepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('css/quill.snow.css') }}">
    <!-- Date Range Picker CSS -->
    <link rel="stylesheet" href="{{ asset('css/daterangepicker.css') }}">
    <!-- App CSS -->
    <link rel="stylesheet" href="{{ asset('css/app-light.css') }}" id="lightTheme">
    <link rel="stylesheet" href="{{ asset('css/app-dark.css') }}" id="darkTheme" disabled>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        /* Prevent theme switching artifacts */
        body,
        * {
            transition: none !important;
        }
    </style>
    @yield('styles')
</head>

<body class="vertical light">
    <div class="wrapper">
        <nav class="topnav navbar navbar-light">
            <button type="button" class="navbar-toggler text-muted mt-2 p-0 mr-3 collapseSidebar">
                <i class="fe fe-menu navbar-toggler-icon"></i>
            </button>
            <form class="form-inline mr-auto searchform text-muted">
                <input class="form-control mr-sm-2 bg-transparent border-0 pl-4 text-muted" type="search"
                    placeholder="Type something..." aria-label="Search">
            </form>
            <ul class="nav">
                <li class="nav-item">
                    <a class="nav-link text-muted my-2" href="#" id="modeSwitcher" data-mode="light">
                        <i class="fe fe-sun fe-16"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <!-- updated to Bootstrap 5 -->
                    <a class="nav-link text-muted my-2" href="#" data-bs-toggle="modal" data-bs-target=".modal-shortcut">
                        <span class="fe fe-grid-3 fe-16"></span>
                    </a>
                </li>
                <!-- <li class="nav-item nav-notif">
                    <a class="nav-link text-muted my-2" href="#" data-bs-toggle="modal" data-bs-target=".modal-notif">
                        <span class="fe fe-bell fe-16"></span>
                        <span class="dot dot-md bg-success"></span>
                    </a>
                </li> -->
                <li class="nav-item dropdown">
                    <!-- updated to Bootstrap 5 -->
                    <a class="nav-link dropdown-toggle text-muted pr-0" href="#" id="navbarDropdownMenuLink"
                        role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar avatar-sm mt-2">
                            <img src="{{ Auth::user() && Auth::user()->photo_path ? asset('storage/' . Auth::user()->photo_path) : asset('assets/avatars/face-1.jpg') }}" alt="Profile Photo" class="avatar-img rounded-circle">
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuLink">
                        <li><a class="dropdown-item" href="{{ route('profile') }}">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
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
                <!-- nav bar -->
                <div class="w-100 mb-4 d-flex">
                    <a class="navbar-brand mx-auto mt-2 flex-fill text-center" href="{{ route('dashboard') }}">
                        <!-- logo -->
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
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link">
                            <i class="fe fe-home fe-16"></i>
                            <span class="ml-3 item-text">Dashboard</span>
                        </a>
                    </li>
                </ul>

                <p class="text-muted nav-heading mt-4 mb-1">
                    <span>Main Content</span>
                </p>
                <ul class="navbar-nav flex-fill w-100 mb-2">
                    <li class="nav-item">
                        <a href="{{ route('timetracking.index') }}" class="nav-link">
                            <i class="fe fe-clock fe-16"></i>
                            <span class="ml-3 item-text">Time & Attendance</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#timesheetMenu" role="button" aria-expanded="false" aria-controls="timesheetMenu">
                            <span>
                                <i class="fe fe-file-text fe-16"></i>
                                <span class="ml-3 item-text">Timesheet</span>
                            </span>
                            <i class="fe fe-chevron-down small"></i>
                        </a>
                        <div class="collapse" id="timesheetMenu">
                            <ul class="nav flex-column ml-4">
                                <li class="nav-item">
                                    <a href="{{ route('timesheet.index') }}" class="nav-link small">
                                        <i class="fe fe-edit me-2"></i> Record
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('timesheet.report') }}" class="nav-link small">
                                        <i class="fe fe-bar-chart me-2"></i> Report
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('leave.index') }}" class="nav-link">
                            <i class="fe fe-calendar fe-16"></i>
                            <span class="ml-3 item-text">Leave Management</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('shifts.index') }}" class="nav-link">
                            <i class="fe fe-users fe-16"></i>
                            <span class="ml-3 item-text">Shift & Schedule</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('claims.index') }}" class="nav-link">
                            <i class="fe fe-credit-card fe-16"></i>
                            <span class="ml-3 item-text">Claims & Reimbursement</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main role="main" class="main-content">
            <div class="container-fluid">
                @yield('content')
            </div>

            <!-- Example Modal (Notification) -->
            <div class="modal fade modal-notif modal-slide" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Notifications</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="list-group list-group-flush my-n3">
                                <div class="list-group-item bg-transparent">
                                    <div class="row align-items-center">
                                        <div class="col-auto"><span class="fe fe-box fe-24"></span></div>
                                        <div class="col">
                                            <small><strong>Package uploaded successfully</strong></small>
                                            <div class="my-0 text-muted small">Package is zipped and uploaded</div>
                                            <small class="badge bg-light text-muted">1m ago</small>
                                        </div>
                                    </div>
                                </div>
                                <!-- more items... -->
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Clear All</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/simplebar.min.js') }}"></script>
    <script src="{{ asset('js/daterangepicker.js') }}"></script>
    <script src="{{ asset('js/jquery.stickOnScroll.js') }}"></script>
    <script src="{{ asset('js/tinycolor-min.js') }}"></script>
    <script src="{{ asset('js/config.js') }}"></script>
    <script src="{{ asset('js/d3.min.js') }}"></script>
    <script src="{{ asset('js/topojson.min.js') }}"></script>
    <script src="{{ asset('js/datamaps.all.min.js') }}"></script>
    <script src="{{ asset('js/datamaps-zoomto.js') }}"></script>
    <script src="{{ asset('js/datamaps.custom.js') }}"></script>
    <script src="{{ asset('js/jquery.mask.min.js') }}"></script>
    <script src="{{ asset('js/gauge.min.js') }}"></script>
    <script src="{{ asset('js/jquery.sparkline.min.js') }}"></script>
    <script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('js/jquery.steps.min.js') }}"></script>
    <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/jquery.timepicker.js') }}"></script>
    <script src="{{ asset('js/dropzone.min.js') }}"></script>
    <script src="{{ asset('js/uppy.min.js') }}"></script>
    <script src="{{ asset('js/quill.min.js') }}"></script>
    <script src="{{ asset('js/fullcalendar.js') }}"></script>
    <script src="{{ asset('js/fullcalendar.custom.js') }}"></script>
    <script src="{{ asset('js/apps.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

    @yield('scripts')
</body>
</html>