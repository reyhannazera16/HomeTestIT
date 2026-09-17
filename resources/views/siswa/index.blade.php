@extends('layouts.app')

@section('title', 'Data Siswa')
@section('page_title', 'Data Siswa')

@push('styles')
<style>
    .student-img-thumb {
        width: 44px;
        height: 44px;
        border-radius: 6px;
        object-fit: cover;
        border: 2px solid #e3e6f0;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .student-img-thumb:hover {
        transform: scale(1.1);
        border-color: #4e73df;
    }
    .dataTables_filter {
        display: none; /* Menggunakan Search Kustom Khusus Kolom NIS & Nama */
    }
    .filter-panel {
        background-color: #f8f9fc;
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
    }
    .badge-latis {
        background-color: #e8eaf6;
        color: #3f51b5;
        border: 1px solid #c5cae9;
        font-weight: 700;
        padding: 0.35rem 0.65rem;
        border-radius: 20px;
    }
    .badge-tutor {
        background-color: #e0f2f1;
        color: #00796b;
        border: 1px solid #b2dfdb;
        font-weight: 700;
        padding: 0.35rem 0.65rem;
        border-radius: 20px;
    }
</style>
@endpush

@section('content')
<!-- Page Heading (SB Admin 2 Standard) -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">Data Siswa</h1>
        <p class="text-muted small mb-0">Kelola dan pantau pendataan siswa lembaga Latiseducation dan Tutorindonesia</p>
    </div>
    <div class="d-flex align-items-center gap-2 mt-3 mt-sm-0">
        <!-- Tombol Ekspor Excel (Requirement 8) -->
        <a href="{{ route('siswa.export') }}" id="btnExportExcel" class="btn btn-sm btn-success shadow-sm">
            <i class="fas fa-file-excel fa-sm text-white-50 me-1"></i> Ekspor Excel
        </a>

        <!-- Tombol Tambah Siswa (Requirement 7) -->
        <a href="{{ route('siswa.create') }}" class="btn btn-sm btn-primary shadow-sm" id="btnTambahSiswa">
            <i class="fas fa-user-plus fa-sm text-white-50 me-1"></i> Tambah Siswa
        </a>
    </div>
</div>

<!-- SB Admin 2 Stat Cards -->
<div class="row mb-4">
    <!-- Total Siswa -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Siswa Terdaftar</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $siswas->count() }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Latiseducation -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Latiseducation</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $siswas->where('lembaga.name', 'Latiseducation')->count() }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-building fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tutorindonesia -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Tutorindonesia</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $siswas->where('lembaga.name', 'Tutorindonesia')->count() }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-book-reader fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Status -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Status Filter Aktif</div>
                        <div class="small font-weight-bold text-gray-800" id="statFilterActiveText">
                            Semua Lembaga
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-filter fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main DataTables Card (SB Admin 2 Standard) -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-table me-2"></i>Daftar Siswa Terdaftar
        </h6>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill small" id="activeFilterBadge">
            <i class="fas fa-info-circle me-1"></i> Menampilkan semua data
        </span>
    </div>

    <div class="card-body">

        <!-- Filter & Search Toolbar (Requirement 7c & 7d) -->
        <div class="filter-panel">
            <div class="row g-3 align-items-end">
                <!-- Filter Dropdown Lembaga (Requirement 7d: dari database) -->
                <div class="col-md-4 col-sm-6">
                    <label for="filterLembaga" class="small font-weight-bold text-gray-700 mb-1">
                        <i class="fas fa-funnel-dollar text-primary me-1"></i> Filter Lembaga Siswa:
                    </label>
                    <select id="filterLembaga" class="form-select form-select-sm">
                        <option value="" data-id="">-- Semua Lembaga --</option>
                        @foreach($lembagas as $lembaga)
                            <option value="{{ $lembaga->name }}" data-id="{{ $lembaga->id }}">
                                {{ $lembaga->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Custom Search: Khusus Kolom NIS & Nama (Requirement 7c) -->
                <div class="col-md-5 col-sm-6">
                    <label for="customSearchInput" class="small font-weight-bold text-gray-700 mb-1">
                        <i class="fas fa-search text-primary me-1"></i> Cari Siswa (Khusus NIS &amp; Nama):
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-gray-400"></i></span>
                        <input type="text" id="customSearchInput" class="form-control" placeholder="Ketik nomor NIS atau nama siswa...">
                        <button class="btn btn-outline-secondary" type="button" id="btnClearSearch" title="Hapus Pencarian">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-3 col-sm-12 text-md-end">
                    <button class="btn btn-sm btn-outline-secondary" type="button" id="btnResetAllFilters">
                        <i class="fas fa-undo me-1"></i> Reset Filter
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabel DataTables (Requirement 7) -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle w-100" id="siswaTable" width="100%" cellspacing="0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 40px;">No</th>
                        <th class="text-center" style="width: 65px;">Foto</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Lembaga</th>
                        <th>Email</th>
                        <th>Tanggal Dibuat</th>
                        <th class="text-center" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($siswas as $index => $item)
                        <tr>
                            <td class="text-center font-weight-bold text-secondary">{{ $loop->iteration }}</td>
                            <td class="text-center">
                                <img src="{{ $item->foto_url }}" alt="{{ $item->nama }}" class="student-img-thumb" 
                                     onclick="showImageModal('{{ $item->foto_url }}', '{{ $item->nama }}')" title="Klik untuk memperbesar foto">
                            </td>
                            <td>
                                <span class="font-weight-bold text-dark">{{ $item->nis }}</span>
                            </td>
                            <td>
                                <span class="font-weight-bold text-primary">{{ $item->nama }}</span>
                            </td>
                            <td>
                                @if($item->lembaga && $item->lembaga->name === 'Latiseducation')
                                    <span class="badge-latis"><i class="fas fa-building me-1"></i> Latiseducation</span>
                                @elseif($item->lembaga && $item->lembaga->name === 'Tutorindonesia')
                                    <span class="badge-tutor"><i class="fas fa-graduation-cap me-1"></i> Tutorindonesia</span>
                                @else
                                    <span class="badge bg-light text-dark border">{{ $item->lembaga->name ?? '-' }}</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted"><i class="fas fa-envelope text-gray-400 me-1"></i>{{ $item->email }}</small>
                            </td>
                            <td>
                                <small class="text-secondary">{{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-' }}</small>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <!-- Action Button Edit (Requirement 7a) -->
                                    <a href="{{ route('siswa.edit', $item->id) }}" class="btn btn-outline-primary" title="Edit Data Siswa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <!-- Action Button Delete -->
                                    <button type="button" class="btn btn-outline-danger" title="Hapus Data Siswa" onclick="deleteSiswa('{{ $item->id }}', '{{ $item->nama }}')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                                <form id="delete-form-{{ $item->id }}" action="{{ route('siswa.destroy', $item->id) }}" method="POST" class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- Modal Preview Foto Siswa -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title font-weight-bold" id="imageModalLabel">
                    <i class="fas fa-image me-1"></i> Foto Siswa
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img src="" id="modalImageTarget" class="img-fluid rounded border shadow-sm" style="max-height: 380px; object-fit: contain;">
                <h6 class="font-weight-bold text-gray-800 mt-3 mb-0" id="modalImageCaption"></h6>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Custom DataTables Filter: KHUSUS kolom NIS & Nama Siswa (Requirement 7c)
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                var searchTerm = $('#customSearchInput').val().toLowerCase().trim();
                var selectedLembaga = $('#filterLembaga').val().toLowerCase().trim();

                // data[2] = Kolom NIS
                // data[3] = Kolom Nama Siswa
                // data[4] = Kolom Lembaga
                var nis = (data[2] || '').toLowerCase();
                var nama = (data[3] || '').toLowerCase();
                var lembaga = (data[4] || '').toLowerCase();

                // 1. Cek pencarian HANYA pada NIS dan Nama
                var matchSearch = true;
                if (searchTerm !== '') {
                    matchSearch = (nis.indexOf(searchTerm) !== -1 || nama.indexOf(searchTerm) !== -1);
                }

                // 2. Cek filter dropdown Lembaga
                var matchLembaga = true;
                if (selectedLembaga !== '') {
                    matchLembaga = (lembaga.indexOf(selectedLembaga) !== -1);
                }

                return matchSearch && matchLembaga;
            }
        );

        // Inisialisasi DataTables SB Admin
        var table = $('#siswaTable').DataTable({
            responsive: true,
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Semua"]],
            order: [[0, 'asc']],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Cari...",
                lengthMenu: "Tampilkan _MENU_ entri per halaman",
                zeroRecords: "Tidak ada data siswa yang cocok dengan filter",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ siswa",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 siswa",
                infoFiltered: "(disaring dari _MAX_ total siswa)",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "<i class='fas fa-chevron-right'></i>",
                    previous: "<i class='fas fa-chevron-left'></i>"
                }
            }
        });

        // Event listener saat input search kustom diketik
        $('#customSearchInput').on('keyup input', function() {
            table.draw();
            updateExportUrlAndBadge();
        });

        // Tombol Hapus Search
        $('#btnClearSearch').on('click', function() {
            $('#customSearchInput').val('');
            table.draw();
            updateExportUrlAndBadge();
        });

        // Event listener saat dropdown lembaga dipilih (Requirement 7d)
        $('#filterLembaga').on('change', function() {
            table.draw();
            updateExportUrlAndBadge();
        });

        // Tombol Reset Semua Filter
        $('#btnResetAllFilters').on('click', function() {
            $('#filterLembaga').val('');
            $('#customSearchInput').val('');
            table.draw();
            updateExportUrlAndBadge();
        });

        // Update URL Ekspor Excel secara dinamis (Requirement 8)
        function updateExportUrlAndBadge() {
            var selectedLembagaId = $('#filterLembaga option:selected').data('id') || '';
            var selectedLembagaName = $('#filterLembaga').val();
            var searchVal = $('#customSearchInput').val().trim();

            var exportUrl = "{{ route('siswa.export') }}";
            var params = [];

            if (selectedLembagaId) {
                params.push('lembaga_id=' + encodeURIComponent(selectedLembagaId));
            }
            if (searchVal) {
                params.push('search=' + encodeURIComponent(searchVal));
            }

            if (params.length > 0) {
                exportUrl += '?' + params.join('&');
            }

            $('#btnExportExcel').attr('href', exportUrl);

            // Update badge dan stat card
            var badgeText = '';
            var statText = selectedLembagaName ? selectedLembagaName : 'Semua Lembaga';

            if (selectedLembagaName && searchVal) {
                badgeText = 'Filter: ' + selectedLembagaName + ' & Cari: "' + searchVal + '"';
                statText += ' ("' + searchVal + '")';
            } else if (selectedLembagaName) {
                badgeText = 'Filter: ' + selectedLembagaName;
            } else if (searchVal) {
                badgeText = 'Pencarian: "' + searchVal + '"';
                statText = 'Cari: "' + searchVal + '"';
            } else {
                badgeText = 'Menampilkan semua data';
            }

            $('#activeFilterBadge').html('<i class="fas fa-info-circle me-1"></i> ' + badgeText);
            $('#statFilterActiveText').text(statText);
        }

        // Jalankan sinkronisasi awal
        updateExportUrlAndBadge();
    });

    // Preview Foto Modal
    function showImageModal(url, name) {
        $('#modalImageTarget').attr('src', url);
        $('#modalImageCaption').text(name);
        var modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
        modal.show();
    }

    // Konfirmasi Hapus Siswa
    function deleteSiswa(id, name) {
        Swal.fire({
            title: 'Hapus Data Siswa?',
            text: "Data siswa '" + name + "' akan dihapus secara permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            cancelButtonColor: '#858796',
            confirmButtonText: '<i class="fas fa-trash-alt me-1"></i> Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
@endpush
