<?php

namespace App\Http\Controllers;

use App\Models\Lembaga;
use App\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SiswaController extends Controller
{
    /**
     * Tampilkan halaman daftar siswa dengan DataTables
     */
    public function index(): View
    {
        // Menggunakan prepared statement via Eloquent ORM
        $lembagas = Lembaga::orderBy('name', 'asc')->get();
        $siswas = Siswa::with('lembaga')->orderBy('id', 'desc')->get();

        return view('siswa.index', compact('lembagas', 'siswas'));
    }

    /**
     * Tampilkan form tambah siswa
     */
    public function create(): View
    {
        $lembagas = Lembaga::orderBy('name', 'asc')->get();
        return view('siswa.create', compact('lembagas'));
    }

    /**
     * Simpan data siswa baru ke database
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'lembaga_id' => ['required', 'exists:lembagas,id'],
            'nis'        => ['required', 'numeric', 'unique:siswas,nis'],
            'nama'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255'],
            'foto'       => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:100'],
        ], [
            'lembaga_id.required' => 'Pilihan lembaga wajib dipilih.',
            'lembaga_id.exists'   => 'Lembaga yang dipilih tidak valid.',
            'nis.required'        => 'NIS wajib diisi.',
            'nis.numeric'         => 'NIS harus berupa angka.',
            'nis.unique'          => 'NIS tersebut sudah terdaftar.',
            'nama.required'       => 'Nama siswa wajib diisi.',
            'email.required'      => 'Email wajib diisi.',
            'email.email'         => 'Format email tidak valid.',
            'foto.image'          => 'File yang diunggah harus berupa gambar.',
            'foto.mimes'          => 'Format foto yang diizinkan hanya JPG dan PNG.',
            'foto.max'            => 'Ukuran foto maksimal 100KB.',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $fotoPath = $file->storeAs('siswa', $filename, 'public');
        }

        // Eloquent model::create menggunakan PDO prepared statement
        Siswa::create([
            'lembaga_id' => $validated['lembaga_id'],
            'nis'        => $validated['nis'],
            'nama'       => $validated['nama'],
            'email'      => $validated['email'],
            'foto'       => $fotoPath,
        ]);

        return redirect()->route('siswa.index')
            ->with('success', 'Data siswa berhasil ditambahkan.');
    }

    /**
     * Tampilkan form edit siswa
     */
    public function edit(Siswa $siswa): View
    {
        $lembagas = Lembaga::orderBy('name', 'asc')->get();
        return view('siswa.edit', compact('siswa', 'lembagas'));
    }

    /**
     * Perbarui data siswa di database
     */
    public function update(Request $request, Siswa $siswa): RedirectResponse
    {
        $validated = $request->validate([
            'lembaga_id' => ['required', 'exists:lembagas,id'],
            'nis'        => ['required', 'numeric', Rule::unique('siswas', 'nis')->ignore($siswa->id)],
            'nama'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255'],
            'foto'       => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:100'],
        ], [
            'lembaga_id.required' => 'Pilihan lembaga wajib dipilih.',
            'lembaga_id.exists'   => 'Lembaga yang dipilih tidak valid.',
            'nis.required'        => 'NIS wajib diisi.',
            'nis.numeric'         => 'NIS harus berupa angka.',
            'nis.unique'          => 'NIS tersebut sudah digunakan oleh siswa lain.',
            'nama.required'       => 'Nama siswa wajib diisi.',
            'email.required'      => 'Email wajib diisi.',
            'email.email'         => 'Format email tidak valid.',
            'foto.image'          => 'File yang diunggah harus berupa gambar.',
            'foto.mimes'          => 'Format foto yang diizinkan hanya JPG dan PNG.',
            'foto.max'            => 'Ukuran foto maksimal 100KB.',
        ]);

        $fotoPath = $siswa->foto;

        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($fotoPath && Storage::disk('public')->exists($fotoPath)) {
                Storage::disk('public')->delete($fotoPath);
            }

            $file = $request->file('foto');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $fotoPath = $file->storeAs('siswa', $filename, 'public');
        }

        // Eloquent update menggunakan parameter binding
        $siswa->update([
            'lembaga_id' => $validated['lembaga_id'],
            'nis'        => $validated['nis'],
            'nama'       => $validated['nama'],
            'email'      => $validated['email'],
            'foto'       => $fotoPath,
        ]);

        return redirect()->route('siswa.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    /**
     * Hapus data siswa dan foto terkait dari database
     */
    public function destroy(Siswa $siswa): RedirectResponse
    {
        if ($siswa->foto && Storage::disk('public')->exists($siswa->foto)) {
            Storage::disk('public')->delete($siswa->foto);
        }

        $siswa->delete();

        return redirect()->route('siswa.index')
            ->with('success', 'Data siswa berhasil dihapus.');
    }

    /**
     * Ekspor data siswa ke format Excel (.xlsx)
     * Data yang diekspor sesuai dengan parameter filter & search aktif (Requirement 8)
     */
    public function export(Request $request): StreamedResponse
    {
        $query = Siswa::with('lembaga');

        // Filter Lembaga (prepared statement via Eloquent)
        $namaLembagaFilter = 'Semua Lembaga';
        if ($request->filled('lembaga_id')) {
            $query->where('lembaga_id', $request->lembaga_id);
            $selectedLembaga = Lembaga::find($request->lembaga_id);
            if ($selectedLembaga) {
                $namaLembagaFilter = $selectedLembaga->name;
            }
        }

        // Filter Search NIS & Nama (Requirement 7c & 8)
        $searchTerm = $request->input('search');
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nis', 'like', '%' . $searchTerm . '%')
                  ->orWhere('nama', 'like', '%' . $searchTerm . '%');
            });
        }

        $siswas = $query->orderBy('nis', 'asc')->get();

        // Buat Spreadsheet dengan PhpSpreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Siswa');

        // Judul Laporan
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'LAPORAN DATA SISWA LATISEDUCATION & TUTORINDONESIA');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Keterangan Filter
        $filterInfo = 'Filter Lembaga: ' . $namaLembagaFilter;
        if (!empty($searchTerm)) {
            $filterInfo .= ' | Kata Kunci Pencarian (NIS/Nama): "' . $searchTerm . '"';
        }
        $filterInfo .= ' | Total Data: ' . $siswas->count();

        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', $filterInfo);
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->getColor()->setRGB('555555');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A3', ''); // Spacing

        // Header Kolom
        $headers = ['No', 'NIS', 'Nama Siswa', 'Email', 'Lembaga', 'Tanggal Dibuat'];
        $columnLetters = ['A', 'B', 'C', 'D', 'E', 'F'];

        foreach ($headers as $index => $header) {
            $col = $columnLetters[$index];
            $sheet->setCellValue($col . '4', $header);
        }

        // Styling Header Tabel
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'], // Indigo Brand
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '312E81'],
                ],
            ],
        ];
        $sheet->getStyle('A4:F4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(26);

        // Isi Data Baris
        $row = 5;
        foreach ($siswas as $idx => $item) {
            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValueExplicit('B' . $row, (string)$item->nis, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $row, $item->nama);
            $sheet->setCellValue('D' . $row, $item->email);
            $sheet->setCellValue('E' . $row, $item->lembaga ? $item->lembaga->name : '-');
            $sheet->setCellValue('F' . $row, $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-');

            // Alignment
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Zebra background styling
            if ($row % 2 == 0) {
                $sheet->getStyle('A' . $row . ':F' . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F8FAFC');
            }

            $row++;
        }

        $lastRow = max(5, $row - 1);

        // Border Tabel Data
        $dataBorderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ];
        $sheet->getStyle('A5:F' . $lastRow)->applyFromArray($dataBorderStyle);

        // Auto-fit Column Widths
        foreach ($columnLetters as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Data_Siswa_' . date('Ymd_His') . '.xlsx';

        return new StreamedResponse(
            function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }
}
