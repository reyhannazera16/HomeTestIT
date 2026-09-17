@extends('layouts.app')

@section('title', 'Profil Kandidat')
@section('page_title', 'Profil Kandidat')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">Profil Kandidat</h1>
        <p class="text-muted small mb-0">Informasi dan konfigurasi profil kandidat pengembang aplikasi</p>
    </div>
</div>

<div class="row">
    <!-- Kolom Kiri: Kartu Identitas Kandidat (Requirement 10: Nama, Position, Image) -->
    <div class="col-xl-4 col-lg-5 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-white">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-id-card me-1"></i> Kartu Identitas Kandidat
                </h6>
            </div>
            <div class="card-body text-center p-4">
                <div class="position-relative d-inline-block mb-3">
                    <!-- Kriteria 10c: Image kandidat -->
                    <img class="img-profile rounded-circle border border-4 border-primary shadow-sm"
                         src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                         id="candidateAvatarPreview"
                         style="width: 140px; height: 140px; object-fit: cover;">
                </div>

                <!-- Kriteria 10a: Nama kandidat -->
                <h5 class="font-weight-bold text-gray-900 mb-1" id="candidateNameDisplay">
                    {{ $user->name }}
                </h5>

                <!-- Kriteria 10b: Position kandidat -->
                <div class="mb-3">
                    <span class="badge bg-primary px-3 py-2 rounded-pill font-weight-bold" id="candidatePositionDisplay">
                        <i class="fas fa-briefcase me-1"></i> {{ $user->position ?? 'Fullstack Web Developer' }}
                    </span>
                </div>

                <p class="text-muted small mb-3">
                    <i class="fas fa-envelope text-gray-400 me-1"></i> {{ $user->email }}
                </p>

                <hr class="my-3">

                <div class="text-start small">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Role Akses:</span>
                        <strong class="text-gray-800">Super Administrator</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Template UI:</span>
                        <strong class="text-primary">SB Admin 2</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Sistem Lembaga:</span>
                        <strong class="text-gray-800">Latiseducation &amp; Tutorindonesia</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">Session ID:</span>
                        <span class="badge bg-light text-secondary border font-monospace">{{ substr(session()->getId(), 0, 12) }}...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Formulir Pengaturan Profil -->
    <div class="col-xl-8 col-lg-7 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-white">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-user-cog me-1"></i> Pengaturan Data Profil Kandidat
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <h6 class="font-weight-bold text-primary mb-3">
                        <i class="fas fa-info-circle me-1"></i> Biodata Kandidat
                    </h6>

                    <!-- Kriteria 10a: Nama Kandidat -->
                    <div class="form-group mb-3">
                        <label for="name" class="small font-weight-bold text-gray-700 mb-1">
                            Nama Kandidat <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" id="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $user->name) }}" 
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Kriteria 10b: Position Kandidat -->
                    <div class="form-group mb-3">
                        <label for="position" class="small font-weight-bold text-gray-700 mb-1">
                            Position Kandidat <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="position" id="position" 
                               class="form-control @error('position') is-invalid @enderror" 
                               value="{{ old('position', $user->position) }}" 
                               placeholder="Contoh: Fullstack Web Developer" 
                               required>
                        @error('position')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email Akun -->
                    <div class="form-group mb-3">
                        <label for="email" class="small font-weight-bold text-gray-700 mb-1">
                            Alamat Email <span class="text-danger">*</span>
                        </label>
                        <input type="email" name="email" id="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email', $user->email) }}" 
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Kriteria 10c: Image Kandidat -->
                    <div class="form-group mb-4">
                        <label for="avatar" class="small font-weight-bold text-gray-700 mb-1">
                            Upload Foto / Image Kandidat
                        </label>
                        <input type="file" name="avatar" id="avatar" 
                               class="form-control @error('avatar') is-invalid @enderror" 
                               accept=".jpg,.jpeg,.png"
                               onchange="previewCandidateAvatar(this)">
                        <small class="form-text text-muted">
                            Format yang didukung: <strong>JPG, JPEG, PNG</strong> (Maksimal 2MB). Foto akan langsung diperbarui di kartu profil dan topbar.
                        </small>
                        @error('avatar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4">

                    <h6 class="font-weight-bold text-gray-700 mb-3">
                        <i class="fas fa-lock me-1"></i> Ubah Password (Opsional)
                    </h6>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label for="password" class="small font-weight-bold text-gray-700 mb-1">Password Baru</label>
                            <input type="password" name="password" id="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   placeholder="Kosongkan jika tidak diubah">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="small font-weight-bold text-gray-700 mb-1">Ulangi Password Baru</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" 
                                   class="form-control" 
                                   placeholder="Ulangi password baru">
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4 shadow-sm" id="btnUpdateProfile">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function previewCandidateAvatar(input) {
        var file = input.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#candidateAvatarPreview').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    }
</script>
@endpush
