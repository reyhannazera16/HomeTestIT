<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - SB Admin | Pendataan Siswa Latiseducation & Tutorindonesia</title>

    <!-- Google Fonts: Nunito (SB Admin Standard) -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    
    <!-- FontAwesome 6 Free -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #4e73df;
            background-image: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Nunito', sans-serif;
        }

        .card-login {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.25) !important;
            overflow: hidden;
            width: 100%;
            max-width: 900px;
        }

        .bg-login-image {
            background: linear-gradient(135deg, #224abe 0%, #4e73df 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #fff;
            padding: 3rem;
            text-align: center;
        }

        .form-control-user {
            font-size: 0.85rem;
            border-radius: 10rem;
            padding: 0.85rem 1.25rem;
        }

        .btn-user {
            font-size: 0.85rem;
            border-radius: 10rem;
            padding: 0.75rem 1rem;
            font-weight: 700;
        }

        .custom-control-label {
            font-size: 0.85rem;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card card-login o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="row g-0">
                            <!-- Kolom Kiri: Branding SB Admin + Lembaga -->
                            <div class="col-lg-5 d-none d-lg-flex bg-login-image">
                                <div class="text-center">
                                    <div class="mb-4">
                                        <i class="fas fa-graduation-cap fa-4x text-white opacity-75"></i>
                                    </div>
                                    <h4 class="font-weight-bold text-white mb-2">SB ADMIN</h4>
                                    <p class="text-white-50 small mb-3">Portal Pendataan Siswa Lembaga</p>
                                    <div class="badge bg-white text-primary px-3 py-2 rounded-pill font-weight-bold shadow-sm">
                                        Latiseducation &amp; Tutorindonesia
                                    </div>
                                    <div class="mt-4 small text-white-50">
                                        <i class="fas fa-shield-alt me-1"></i> Sistem Autentikasi Berbasis Session
                                    </div>
                                </div>
                            </div>

                            <!-- Kolom Kanan: Form Login SB Admin -->
                            <div class="col-lg-7">
                                <div class="p-5">
                                    <div class="text-center mb-4">
                                        <h1 class="h4 text-gray-900 font-weight-bold mb-1">Selamat Datang Kembali!</h1>
                                        <p class="text-muted small">Silakan masuk dengan akun administrator Anda</p>
                                        <span class="d-none">Latiseducation &amp; Tutorindonesia</span>
                                    </div>

                                    @if(session('success'))
                                        <div class="alert alert-success py-2 px-3 small rounded-pill mb-3 text-center">
                                            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                                        </div>
                                    @endif

                                    @if($errors->any())
                                        <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3">
                                            <i class="fas fa-exclamation-triangle me-1"></i> {{ $errors->first() }}
                                        </div>
                                    @endif

                                    <form class="user" action="{{ route('login.submit') }}" method="POST">
                                        @csrf

                                        <div class="form-group mb-3">
                                            <label for="email" class="small font-weight-bold text-gray-700 mb-1 ps-2">Alamat Email</label>
                                            <div class="input-group">
                                                <input type="email" class="form-control form-control-user @error('email') is-invalid @enderror"
                                                    id="email" name="email" value="{{ old('email', 'admin@latis.com') }}"
                                                    placeholder="Masukkan alamat email..." required autofocus>
                                            </div>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="password" class="small font-weight-bold text-gray-700 mb-1 ps-2">Password</label>
                                            <input type="password" class="form-control form-control-user @error('password') is-invalid @enderror"
                                                id="password" name="password" placeholder="Masukkan password..." required>
                                        </div>

                                        <div class="form-group mb-4 ps-2">
                                            <div class="form-check small">
                                                <input type="checkbox" class="form-check-input" name="remember" id="customCheck" checked>
                                                <label class="form-check-label text-muted" for="customCheck">Ingat Saya</label>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-user w-100 shadow-sm" id="btnLogin">
                                            <i class="fas fa-sign-in-alt me-1"></i> Masuk ke Sistem
                                        </button>
                                    </form>

                                    <hr class="my-4">

                                    <div class="card bg-light border-left-primary py-2 px-3 mb-0">
                                        <div class="card-body p-1 small">
                                            <div class="font-weight-bold text-primary mb-1">
                                                <i class="fas fa-key me-1"></i> Kredensial Default SB Admin:
                                            </div>
                                            <div class="text-gray-700">Email: <strong>admin@latis.com</strong></div>
                                            <div class="text-gray-700">Password: <strong>password123</strong></div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
