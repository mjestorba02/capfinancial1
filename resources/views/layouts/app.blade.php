<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/images/newlogo.svg') }}">
    <title>@yield('title', 'Financial 1')</title>

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
    <link rel="stylesheet" href="{{ asset('css/app-enhancements.css') }}">
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />

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
                <li class="nav-item">
                    <a class="nav-link text-muted my-2" href="#" data-bs-toggle="modal" data-bs-target=".modal-shortcut">
                        <span class="fe fe-grid-3 fe-16"></span>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-muted pr-0" href="#" id="navbarDropdownMenuLink"
                        role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar avatar-sm mt-2">
                            <img src="{{ Auth::user() && Auth::user()->photo_path ? asset('storage/' . Auth::user()->photo_path) : asset('assets/avatars/avatar.jpg') }}" alt="Profile Photo" class="avatar-img rounded-circle" onerror="this.onerror=null; this.src='{{ asset('assets/avatars/avatar.jpg') }}';">
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
                <div class="w-100 mb-4 d-flex">
                    <a class="navbar-brand mx-auto mt-2 flex-fill text-center" href="{{ route('dashboard') }}">
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
                        <a href="{{ route('dashboard') }}" class="nav-link">
                            <i class="fe fe-home fe-16"></i>
                            <span class="ml-3 item-text">Dashboard</span>
                        </a>
                    </li>
                    @if(Auth::user() && Auth::user()->isHr())
                    <li class="nav-item mb-3">
                        <a href="{{ route('user-approvals.index') }}" class="nav-link {{ Request::routeIs('user-approvals.*') ? 'active' : '' }}">
                            <i class="fe fe-user-check fe-16"></i>
                            <span class="ml-3 item-text">User Approvals</span>
                        </a>
                    </li>
                    @endif
                    @if(Auth::user() && (Auth::user()->isAdmin() || Auth::user()->isHr()))
                    <li class="nav-item mb-3">
                        <a href="{{ route('audit_trails.index') }}" class="nav-link {{ Request::routeIs('audit_trails.*') ? 'active' : '' }}">
                            <i class="fe fe-activity fe-16"></i>
                            <span class="ml-3 item-text">Audit Trail</span>
                        </a>
                    </li>
                    @endif
                </ul>

                <p class="text-muted nav-heading mt-4 mb-1">
                    <span>Main Content</span>
                </p>
                <ul class="navbar-nav flex-fill w-100 mb-2">

                    <li class="nav-item mb-3">
                        <a href="{{ route('collections.index') }}"
                        class="nav-link {{ Request::routeIs('collections.*') ? 'active' : '' }}">
                            <i class="fe fe-credit-card fe-16"></i>
                            <span class="ml-3 item-text">Collections</span>
                        </a>
                    </li>

                    @php
                        $isBudgetActive = Request::routeIs('budget_requests.*');
                    @endphp
                    <li class="nav-item mb-3">
                        <a class="nav-link d-flex justify-content-between align-items-center {{ $isBudgetActive ? 'active' : 'collapsed' }}"
                        data-bs-toggle="collapse"
                        href="#budgetMenu"
                        role="button"
                        aria-expanded="{{ $isBudgetActive ? 'true' : 'false' }}"
                        aria-controls="budgetMenu">
                            <span>
                                <i class="fe fe-dollar-sign fe-16"></i>
                                <span class="ml-3 item-text">Budget Management</span>
                            </span>
                            <i class="fe fe-chevron-down small"></i>
                        </a>

                        <div class="collapse {{ $isBudgetActive ? 'show' : '' }}" id="budgetMenu">
                            <ul class="nav flex-column ml-4">
                                <li class="nav-item">
                                    <a href="{{ route('budget_requests.index') }}"
                                    class="nav-link small {{ Request::routeIs('budget.request') ? 'active' : '' }}">
                                        <i class="fe fe-file-plus me-2"></i> Budget Request
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('finance.allocations.index') }}"
                                    class="nav-link small {{ Request::routeIs('budget.allocation') ? 'active' : '' }}">
                                        <i class="fe fe-pie-chart me-2"></i> Budget Allocation & Planning
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    @php
                        $isLedgerActive = Request::routeIs('ledger.*');
                    @endphp
                    <li class="nav-item mb-3">
                        <a class="nav-link d-flex justify-content-between align-items-center {{ $isLedgerActive ? 'active' : 'collapsed' }}"
                        data-bs-toggle="collapse"
                        href="#ledgerMenu"
                        role="button"
                        aria-expanded="{{ $isLedgerActive ? 'true' : 'false' }}"
                        aria-controls="ledgerMenu">
                            <span>
                                <i class="fe fe-book fe-16"></i>
                                <span class="ml-3 item-text">General Ledger</span>
                            </span>
                            <i class="fe fe-chevron-down small"></i>
                        </a>

                        <div class="collapse {{ $isLedgerActive ? 'show' : '' }}" id="ledgerMenu">
                            <ul class="nav flex-column ml-4">
                                <li class="nav-item">
                                    <a href="{{ route('chart.index') }}"
                                    class="nav-link small {{ Request::routeIs('chart.*') ? 'active' : '' }}">
                                        <i class="fe fe-layers me-2"></i> Chart of Accounts
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('journal_entries.index') }}"
                                    class="nav-link small {{ Request::routeIs('journal_entries.*') ? 'active' : '' }}">
                                        <i class="fe fe-edit-3 me-2"></i> Journal Entry
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item mb-3">
                        <a href="{{ route('accounts.index') }}"
                        class="nav-link {{ Request::routeIs('accounts.*') ? 'active' : '' }}">
                            <i class="fe fe-briefcase fe-16"></i>
                            <span class="ml-3 item-text">Accounts</span>
                        </a>
                    </li>

                    <li class="nav-item mb-3">
                        <a href="{{ route('disbursements.index') }}"
                        class="nav-link {{ Request::routeIs('disbursements.*') ? 'active' : '' }}">
                            <i class="fe fe-send fe-16"></i>
                            <span class="ml-3 item-text">Disbursement</span>
                        </a>
                    </li>
                </ul>

                @if(Auth::user() && Auth::user()->isAdmin())
                    <p class="text-muted nav-heading mt-4 mb-1">
                        <span>AI Assistant</span>
                    </p>
                    <ul class="navbar-nav flex-fill w-100 mb-2">
                        <li class="nav-item mb-3">
                            <a href="{{ route('ai.financial_intelligence.index') }}"
                            class="nav-link {{ Request::routeIs('ai.financial_intelligence.*') ? 'active' : '' }}">
                                <i class="fe fe-zap fe-16"></i>
                                <span class="ml-3 item-text">AI Financial Intelligence</span>
                            </a>
                        </li>
                    </ul>
                @endif
            </nav>
        </aside>

        <main role="main" class="main-content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </main>
    </div>

    @if(Auth::check())
    <!-- Terms & Conditions Modal (Admin / HR / Finance users) -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="termsModalLabel">Financial Management System - Terms &amp; Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">
                        Please read these Terms &amp; Conditions carefully. By continuing to use this system you confirm that you understand and agree to them.
                    </p>

                    <ul class="small ps-3">
                        <li class="mb-1">
                            <strong>Authorised Use Only</strong> – This Financial Management System is for authorised users and
                            official company purposes only. You must not share your login or allow others to access the system
                            on your behalf.
                        </li>
                        <li class="mb-1">
                            <strong>Accuracy of Data</strong> – You are responsible for ensuring that all information you enter,
                            approve, or export (including collections, disbursements, budgets and HR records) is accurate,
                            complete, and up to date to the best of your knowledge.
                        </li>
                        <li class="mb-1">
                            <strong>Confidentiality</strong> – Financial and HR information in this system is confidential.
                            You must not disclose, copy, download, or use any data for personal purposes or share it with
                            unauthorised persons.
                        </li>
                        <li class="mb-1">
                            <strong>Compliance</strong> – You agree to use this system in line with all applicable company
                            policies, employment rules, and relevant laws and regulations (including data protection and
                            financial reporting requirements).
                        </li>
                        <li class="mb-1">
                            <strong>Monitoring &amp; Audit</strong> – All activity in this system may be logged and reviewed
                            for security, compliance, and audit purposes. Misuse may lead to disciplinary action and/or legal
                            consequences.
                        </li>
                        <li class="mb-1">
                            <strong>Security</strong> – You must keep your credentials secure, log out when finished, and
                            promptly report any suspected unauthorised access, data breach, or unusual activity to the system
                            administrator or HR.
                        </li>
                        <li class="mb-1">
                            <strong>System Output</strong> – Reports, analytics, and AI-generated insights are decision-support
                            tools only and do not replace professional judgement. The organisation is not liable for decisions
                            made solely on automated outputs without appropriate review.
                        </li>
                        <li class="mb-1">
                            <strong>Changes to Terms</strong> – These Terms &amp; Conditions may be updated from time to time.
                            Continued use of the system after changes are published will constitute your acceptance of the
                            updated terms.
                        </li>
                    </ul>

                    <p class="small mt-3 mb-0">
                        If you do not agree with these Terms &amp; Conditions, you must not use this system and should contact
                        your line manager or the system administrator immediately.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary btn-sm" id="acceptTermsButton">I Have Read &amp; Agree</button>
                </div>
            </div>
        </div>
    </div>
    @endif

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

    @if(Auth::check())
    <script>
        (function () {
            var hasAccepted = {{ session('terms_accepted') ? 'true' : 'false' }};

            if (!hasAccepted) {
                document.addEventListener('DOMContentLoaded', function () {
                    var modalEl = document.getElementById('termsModal');
                    if (!modalEl || !window.bootstrap) return;

                    var termsModal = new bootstrap.Modal(modalEl);
                    termsModal.show();

                    var acceptBtn = document.getElementById('acceptTermsButton');
                    if (acceptBtn) {
                        acceptBtn.addEventListener('click', function () {
                            $.ajax({
                                url: '{{ route('terms.accept') }}',
                                type: 'POST',
                                data: {
                                    _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                success: function () {
                                    var instance = bootstrap.Modal.getInstance(modalEl);
                                    if (instance) {
                                        instance.hide();
                                    }
                                }
                            });
                        });
                    }
                });
            }
        })();
    </script>
    @endif

    @yield('scripts')
</body>
</html>