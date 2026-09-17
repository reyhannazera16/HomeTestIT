@extends('layouts.app')

@section('title', 'Tambah Siswa Baru')
@section('page_title', 'Tambah Siswa Baru')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">Tambah Siswa Baru</h1>
        <p class="text-muted small mb-0">Lengkapi formulir pendataan siswa untuk lembaga Latiseducation atau Tutorindonesia</p>
    </div>
    <a href="{{ route('siswa.index') }}" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50 me-1"></i> Kembali ke Daftar
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-white">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-user-plus me-1"></i> Formulir Pendaftaran Siswa
                </h6>
            </div>

            <div class="card-body">
                <form action="{{ route('siswa.store') }}" method="POST" enctype="multipart/form-data" id="formTambahSiswa">
                    @csrf

                    <!-- Lembaga Siswa (Requirement 6a: dari database) -->
                    <div class="form-group mb-3">
                        <label for="lembaga_id" class="small font-weight-bold text-gray-700 mb-1">
                            Lembaga Siswa <span class="text-danger">*</span>
                        </label>
                        <select name="lembaga_id" id="lembaga_id" class="form-select @error('lembaga_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Lembaga --</option>
                            @foreach($lembagas as $lembaga)
                                <option value="{{ $lembaga->id }}" {{ old('lembaga_id') == $lembaga->id ? 'selected' : '' }}>
                                    {{ $lembaga->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Pilih lembaga tempat siswa terdaftar (Latiseducation / Tutorindonesia).</small>
                        @error('lembaga_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- NIS (Requirement 6b: required, unik, angka) -->
                    <div class="form-group mb-3">
                        <label for="nis" class="small font-weight-bold text-gray-700 mb-1">
                            NIS (Nomor Induk Siswa) <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="nis" id="nis" 
                               class="form-control @error('nis') is-invalid @enderror" 
                               value="{{ old('nis') }}" 
                               placeholder="Contoh: 10012005 (Hanya angka)" 
                               required>
                        <small class="form-text text-muted">NIS harus unik dan berupa angka.</small>
                        @error('nis')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Nama Siswa (Requirement 6c: required) -->
                    <div class="form-group mb-3">
                        <label for="nama" class="small font-weight-bold text-gray-700 mb-1">
                            Nama Lengkap Siswa <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nama" id="nama" 
                               class="form-control @error('nama') is-invalid @enderror" 
                               value="{{ old('nama') }}" 
                               placeholder="Masukkan nama lengkap siswa" 
                               required>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email Siswa (Requirement 6d: required, valid email) -->
                    <div class="form-group mb-3">
                        <label for="email" class="small font-weight-bold text-gray-700 mb-1">
                            Email Siswa <span class="text-danger">*</span>
                        </label>
                        <input type="email" name="email" id="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email') }}" 
                               placeholder="nama.siswa@example.com" 
                               required>
                        <small class="form-text text-muted">Format email harus valid.</small>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Foto Siswa (Requirement 6e: JPG/PNG, maksimal 100KB) -->
                    <div class="form-group mb-4">
                        <label for="foto" class="small font-weight-bold text-gray-700 mb-1">
                            Foto Siswa (Maksimal 100KB)
                        </label>
                        <div class="row align-items-center g-3">
                            <div class="col-auto">
                                <img id="previewImage" src="https://ui-avatars.com/api/?name=Preview&background=e2e8f0&color=4e73df&size=100" 
                                     alt="Preview Foto" class="rounded border shadow-sm" style="width: 75px; height: 75px; object-fit: cover;">
                            </div>
                            <div class="col">
                                <input type="file" name="foto" id="foto" 
                                       class="form-control @error('foto') is-invalid @enderror" 
                                       accept=".jpg,.jpeg,.png"
                                       onchange="validateAndPreviewImage(this)">
                                <small class="form-text text-muted d-block mt-1" id="fileHelpText">
                                    <i class="fas fa-info-circle me-1"></i> Format yang diizinkan hanya <strong>JPG</strong> dan <strong>PNG</strong> (ukuran maksimal <strong>100 KB</strong>).
                                </small>
                                <div class="text-danger small mt-1 d-none" id="fileSizeError">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Ukuran file melebihi 100 KB! Silakan pilih foto lain.
                                </div>
                                @error('foto')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex align-items-center justify-content-end gap-2">
                        <a href="{{ route('siswa.index') }}" class="btn btn-secondary px-4">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary px-4" id="btnSubmitSiswa">
                            <i class="fas fa-save me-1"></i> Simpan Data Siswa
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
    function validateAndPreviewImage(input) {
        var file = input.files[0];
        var preview = document.getElementById('previewImage');
        var errorMsg = document.getElementById('fileSizeError');
        var submitBtn = document.getElementById('btnSubmitSiswa');

        if (!file) {
            preview.src = "https://ui-avatars.com/api/?name=Preview&background=e2e8f0&color=4e73df&size=100";
            errorMsg.classList.add('d-none');
            submitBtn.disabled = false;
            return;
        }

        // Cek ekstensi
        var allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'Format Tidak Diizinkan',
                text: 'Format foto yang diizinkan hanya JPG dan PNG.'
            });
            input.value = '';
            preview.src = "https://ui-avatars.com/api/?name=Preview&background=e2e8f0&color=4e73df&size=100";
            return;
        }

        // Cek ukuran maksimal 100 KB
        var maxSizeBytes = 100 * 1024;
        if (file.size > maxSizeBytes) {
            errorMsg.classList.remove('d-none');
            submitBtn.disabled = true;
            Swal.fire({
                icon: 'warning',
                title: 'Ukuran Foto Terlalu Besar',
                text: 'Ukuran foto adalah ' + (file.size / 1024).toFixed(1) + ' KB. Batas maksimal adalah 100 KB.'
            });
            input.value = '';
            preview.src = "https://ui-avatars.com/api/?name=Preview&background=e2e8f0&color=4e73df&size=100";
            return;
        }

        errorMsg.classList.add('d-none');
        submitBtn.disabled = false;

        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
</script>
@endpush
