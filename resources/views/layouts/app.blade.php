<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - SB Admin | Pendataan Siswa</title>

    <!-- Google Fonts: Nunito (SB Admin Standard) -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- FontAwesome 6 Free -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- SB Admin 2 Custom Core Styles -->
    <style>
        :root {
            --primary: #4e73df;
            --primary-dark: #224abe;
            --success: #1cc88a;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --secondary: #858796;
            --light: #f8f9fc;
            --dark: #5a5c69;
        }

        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--light);
            color: #5a5c69;
            overflow-x: hidden;
        }

        #wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar SB Admin */
        .sidebar {
            width: 14rem;
            min-height: 100vh;
            background-color: #4e73df;
            background-image: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            background-size: cover;
            transition: width 0.15s ease-in-out;
            z-index: 100;
        }

        .sidebar .sidebar-brand {
            height: 4.375rem;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 800;
            padding: 1.5rem 1rem;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.05rem;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar .sidebar-brand .sidebar-brand-icon i {
            font-size: 1.75rem;
        }

        .sidebar .sidebar-brand .sidebar-brand-text {
            display: inline;
            margin-left: 0.5rem;
        }

        .sidebar hr.sidebar-divider {
            margin: 0 1rem 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
        }

        .sidebar .sidebar-heading {
            text-align: left;
            padding: 0 1rem;
            font-weight: 800;
            font-size: 0.65rem;
            color: rgba(255, 255, 255, 0.5);
            text-transform: uppercase;
            letter-spacing: 0.13rem;
        }

        .sidebar .nav-item {
            position: relative;
            list-style: none;
        }

        .sidebar .nav-item .nav-link {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 0.85rem 1rem;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 700;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.2s;
        }

        .sidebar .nav-item .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-item.active .nav-link {
            color: #fff;
            font-weight: 800;
            background-color: rgba(255, 255, 255, 0.15);
            border-left: 4px solid #fff;
        }

        .sidebar .nav-item .nav-link i {
            font-size: 1rem;
            margin-right: 0.75rem;
            width: 1.25rem;
            text-align: center;
        }

        .sidebar.toggled {
            width: 6.5rem !important;
            overflow: visible;
        }

        .sidebar.toggled .sidebar-brand {
            padding: 1rem 0.5rem;
        }

        .sidebar.toggled .sidebar-brand .sidebar-brand-text,
        .sidebar.toggled .sidebar-heading,
        .sidebar.toggled .nav-item .nav-link span,
        .sidebar.toggled .sidebar-card,
        .sidebar.toggled .sidebar-card-divider {
            display: none !important;
        }

        .sidebar.toggled .nav-item .nav-link {
            text-align: center;
            padding: 0.85rem 1rem;
            width: auto;
            margin: 0.2rem 0.5rem;
            border-radius: 0.35rem;
            justify-content: center;
            border-left: none !important;
        }

        .sidebar.toggled .nav-item.active .nav-link {
            background-color: rgba(255, 255, 255, 0.2);
            border-left: none !important;
        }

        .sidebar.toggled .nav-item .nav-link i {
            margin-right: 0;
            font-size: 1.25rem;
        }

        .sidebar-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 0.875rem;
            border-radius: 0.35rem;
            color: #fff;
            text-align: center;
            padding: 1rem;
            background-color: rgba(0, 0, 0, 0.15);
            margin: 0 1rem 1rem 1rem;
        }

        /* Content Wrapper */
        #content-wrapper {
            background-color: var(--light);
            width: 100%;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
        }

        #content {
            flex: 1 0 auto;
        }

        /* Topbar */
        .topbar {
            height: 4.375rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            z-index: 1;
        }

        .topbar .nav-item .nav-link {
            height: 4.375rem;
            display: flex;
            align-items: center;
            padding: 0 0.75rem;
        }

        .topbar .dropdown-menu {
            position: absolute;
            right: 0;
            left: auto;
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .img-profile {
            height: 2.25rem;
            width: 2.25rem;
            object-fit: cover;
        }

        /* Card SB Admin */
        .card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }

        .card .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }

        /* Border Left Cards */
        .border-left-primary {
            border-left: 0.25rem solid var(--primary) !important;
        }
        .border-left-success {
            border-left: 0.25rem solid var(--success) !important;
        }
        .border-left-info {
            border-left: 0.25rem solid var(--info) !important;
        }
        .border-left-warning {
            border-left: 0.25rem solid var(--warning) !important;
        }

        /* Footer */
        footer.sticky-footer {
            padding: 2rem 0;
            flex-shrink: 0;
            background-color: #ffffff;
            border-top: 1px solid #e3e6f0;
        }

        /* Scroll to Top */
        .scroll-to-top {
            position: fixed;
            right: 1rem;
            bottom: 1rem;
            display: none;
            width: 2.75rem;
            height: 2.75rem;
            text-align: center;
            color: #fff;
            background: rgba(90, 92, 105, 0.5);
            line-height: 46px;
            border-radius: 0.35rem;
            z-index: 1000;
        }
        .scroll-to-top:hover {
            color: #fff;
            background: #5a5c69;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 6.5rem;
            }
            .sidebar .sidebar-brand .sidebar-brand-text,
            .sidebar .sidebar-heading,
            .sidebar .nav-item .nav-link span {
                display: none;
            }
            .sidebar .nav-item .nav-link {
                text-align: center;
                padding: 0.75rem 1rem;
                justify-content: center;
            }
            .sidebar .nav-item .nav-link i {
                margin-right: 0;
                font-size: 1.25rem;
            }
            .sidebar-card {
                display: none;
            }
        }
    </style>
    @stack('styles')
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar (Requirement 9: Siswa, Profile, Logout) -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion p-0" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('siswa.index') }}">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="sidebar-brand-text mx-2">SB ADMIN <sup>EDU</sup></div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Heading -->
            <div class="sidebar-heading mt-3">
                Menu Utama
            </div>

            <!-- Nav Item - Siswa (Requirement 9: Siswa) -->
            <li class="nav-item {{ request()->routeIs('siswa.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('siswa.index') }}" id="sidebarMenuSiswa">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Data Siswa</span>
                </a>
            </li>

            <!-- Nav Item - Profile (Requirement 9 & 10: Profile) -->
            <li class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('profile.index') }}" id="sidebarMenuProfile">
                    <i class="fas fa-fw fa-id-card"></i>
                    <span>Profil Kandidat</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Autentikasi
            </div>

            <!-- Nav Item - Logout (Requirement 9: Logout) -->
            <li class="nav-item">
                <a class="nav-link text-white" href="javascript:void(0);" onclick="confirmLogout()" id="sidebarMenuLogout">
                    <i class="fas fa-fw fa-sign-out-alt text-white"></i>
                    <span class="text-white">Logout</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline mb-4">
                <button class="rounded-circle border-0 text-white bg-white-50" id="sidebarToggle" style="width: 2.5rem; height: 2.5rem; background: rgba(255,255,255,0.2);">
                    <i class="fas fa-angle-left"></i>
                </button>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow px-4">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Title & Lembaga Badge -->
                    <div class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100">
                        <h1 class="h5 mb-0 text-gray-800 font-weight-bold">
                            @yield('page_title', 'Dashboard')
                        </h1>
                        <small class="text-muted">Sistem Pendataan Siswa: <strong>Latiseducation</strong> &amp; <strong>Tutorindonesia</strong></small>
                    </div>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto align-items-center ms-auto">

                        <div class="topbar-divider d-none d-sm-block border-end mx-3" style="height: 2rem;"></div>

                        <!-- Nav Item - User Information (Requirement 10: Nama, Position, Avatar) -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle text-decoration-none" href="#" id="userDropdown" role="button"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <div class="d-flex flex-column text-end me-2 d-none d-lg-inline">
                                    <span class="text-gray-600 font-weight-bold small">{{ Auth::user()->name }}</span>
                                    <span class="text-muted small" style="font-size: 0.75rem;">{{ Auth::user()->position }}</span>
                                </div>
                                <img class="img-profile rounded-circle border shadow-sm" src="{{ Auth::user()->avatar_url }}">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in p-2" aria-labelledby="userDropdown">
                                <a class="dropdown-item py-2 rounded" href="{{ route('profile.index') }}">
                                    <i class="fas fa-id-badge fa-sm fa-fw mr-2 text-primary me-2"></i>
                                    Profil Kandidat
                                </a>
                                <a class="dropdown-item py-2 rounded" href="{{ route('siswa.index') }}">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-info me-2"></i>
                                    Data Siswa
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item py-2 rounded text-danger" href="javascript:void(0);" onclick="confirmLogout()">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-danger me-2"></i>
                                    Logout
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid px-4">

                    <!-- Flash Alerts -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show border-left-success shadow-sm mb-4" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Berhasil!</strong> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show border-left-danger shadow-sm mb-4" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Peringatan!</strong> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show border-left-danger shadow-sm mb-4" role="alert">
                            <div class="font-weight-bold mb-1"><i class="fas fa-ban me-2"></i> Terjadi kesalahan input:</div>
                            <ul class="mb-0 ps-3 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @yield('content')

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white mt-5">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto small text-muted">
                        <span>Copyright &copy; {{ date('Y') }} - Pendataan Siswa Latiseducation &amp; Tutorindonesia (SB Admin Theme)</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Form Logout Tersembunyi (Requirement 9) -->
    <form action="{{ route('logout') }}" method="POST" id="logoutForm" class="d-none">
        @csrf
    </form>

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Core Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Sidebar Toggle Handler
        $("#sidebarToggle, #sidebarToggleTop").on('click', function(e) {
            $("body").toggleClass("sidebar-toggled");
            $(".sidebar").toggleClass("toggled");
            if ($(".sidebar").hasClass("toggled")) {
                $('.sidebar .collapse').collapse('hide');
                $('#sidebarToggle i').removeClass('fa-angle-left').addClass('fa-angle-right');
            } else {
                $('#sidebarToggle i').removeClass('fa-angle-right').addClass('fa-angle-left');
            }
        });

        // Scroll to top
        $(document).on('scroll', function() {
            var scrollDistance = $(this).scrollTop();
            if (scrollDistance > 100) {
                $('.scroll-to-top').fadeIn();
            } else {
                $('.scroll-to-top').fadeOut();
            }
        });

        // Konfirmasi Logout dengan SweetAlert2
        function confirmLogout() {
            Swal.fire({
                title: 'Konfirmasi Logout',
                text: 'Apakah Anda yakin ingin keluar dari sistem SB Admin?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4e73df',
                cancelButtonColor: '#858796',
                confirmButtonText: '<i class="fas fa-sign-out-alt me-1"></i> Ya, Logout',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logoutForm').submit();
                }
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
