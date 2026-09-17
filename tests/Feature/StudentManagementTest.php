<?php

namespace Tests\Feature;

use App\Models\Lembaga;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    /**
     * Test 1: Halaman login dapat diakses dan menampilkan branding
     */
    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Latiseducation & Tutorindonesia', false);
        $response->assertSee('Masuk ke Sistem');
    }

    /**
     * Test 2: Login berhasil dengan session management dan regenerasi ID sesi
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::where('email', 'admin@latis.com')->first();
        if (!$user) {
            $user = User::factory()->create([
                'email' => 'admin@latis.com',
                'password' => bcrypt('password123'),
                'position' => 'Fullstack Web Developer',
            ]);
        }

        $response = $this->post('/login', [
            'email' => 'admin@latis.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('siswa.index'));
    }

    /**
     * Test 3: Route terproteksi session (guest akan diarahkan ke login)
     */
    public function test_unauthenticated_user_cannot_access_siswa_or_profile(): void
    {
        $responseSiswa = $this->get('/siswa');
        $responseSiswa->assertRedirect(route('login'));

        $responseProfile = $this->get('/profile');
        $responseProfile->assertRedirect(route('login'));
    }

    /**
     * Test 4: Halaman Siswa menampilkan DataTables, Lembaga, dan daftar data
     */
    public function test_authenticated_user_can_view_siswa_page(): void
    {
        $user = User::where('email', 'admin@latis.com')->first();

        $response = $this->actingAs($user)->get('/siswa');

        $response->assertStatus(200);
        $response->assertSee('Daftar Siswa Terdaftar');
        $response->assertSee('Filter Lembaga');
        $response->assertSee('Latiseducation');
        $response->assertSee('Tutorindonesia');
        $response->assertSee('siswaTable');
        $response->assertSee('Ekspor Excel');
        $response->assertSee('Profil Kandidat');
    }

    /**
     * Test 5: Validasi CRUD Siswa (NIS required, numeric, unique; Lembaga valid; Foto max 100KB)
     */
    public function test_siswa_validation_rules(): void
    {
        $user = User::where('email', 'admin@latis.com')->first();

        // Testing NIS harus angka dan unik
        $existing = Siswa::first();

        $response = $this->actingAs($user)->post('/siswa', [
            'lembaga_id' => 99999, // Lembaga tidak ada
            'nis' => 'bukan-angka',
            'nama' => '',
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors(['lembaga_id', 'nis', 'nama', 'email']);

        // Testing duplikasi NIS
        if ($existing) {
            $responseDup = $this->actingAs($user)->post('/siswa', [
                'lembaga_id' => $existing->lembaga_id,
                'nis' => $existing->nis,
                'nama' => 'Test Siswa',
                'email' => 'test@example.com',
            ]);
            $responseDup->assertSessionHasErrors(['nis']);
        }
    }

    /**
     * Test 6: Validasi ukuran foto maksimal 100KB dan format JPG/PNG
     */
    public function test_siswa_photo_validation(): void
    {
        $user = User::where('email', 'admin@latis.com')->first();
        $lembaga = Lembaga::first();

        Storage::fake('public');

        // Foto melebihi 100KB (150KB)
        $oversizedPhoto = UploadedFile::fake()->create('foto_besar.jpg', 150, 'image/jpeg');

        $response = $this->actingAs($user)->post('/siswa', [
            'lembaga_id' => $lembaga->id,
            'nis' => '99887766',
            'nama' => 'Foto Terlalu Besar',
            'email' => 'foto@example.com',
            'foto' => $oversizedPhoto,
        ]);

        $response->assertSessionHasErrors(['foto']);

        // Foto format tidak diizinkan (.pdf)
        $invalidFile = UploadedFile::fake()->create('dokumen.pdf', 50, 'application/pdf');

        $responsePdf = $this->actingAs($user)->post('/siswa', [
            'lembaga_id' => $lembaga->id,
            'nis' => '99887767',
            'nama' => 'File Pdf',
            'email' => 'pdf@example.com',
            'foto' => $invalidFile,
        ]);

        $responsePdf->assertSessionHasErrors(['foto']);
    }

    /**
     * Test 7: Berhasil menambah data siswa baru dan tersimpan di database
     */
    public function test_can_create_siswa_successfully(): void
    {
        $user = User::where('email', 'admin@latis.com')->first();
        $lembaga = Lembaga::where('name', 'Latiseducation')->first() ?? Lembaga::first();

        Storage::fake('public');
        $validPhoto = UploadedFile::fake()->image('siswa.png', 80, 80)->size(50); // 50 KB

        $response = $this->actingAs($user)->post('/siswa', [
            'lembaga_id' => $lembaga->id,
            'nis' => '10099887',
            'nama' => 'Muhammad Rizqi Testing',
            'email' => 'rizqi.test@example.com',
            'foto' => $validPhoto,
        ]);

        $response->assertRedirect(route('siswa.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('siswas', [
            'nis' => '10099887',
            'nama' => 'Muhammad Rizqi Testing',
            'email' => 'rizqi.test@example.com',
            'lembaga_id' => $lembaga->id,
        ]);
    }

    /**
     * Test 8: Berhasil mengupdate data siswa
     */
    public function test_can_update_siswa_successfully(): void
    {
        $user = User::where('email', 'admin@latis.com')->first();
        $siswa = Siswa::where('nis', '10099887')->first() ?? Siswa::first();
        $lembagaTutor = Lembaga::where('name', 'Tutorindonesia')->first() ?? Lembaga::first();

        $response = $this->actingAs($user)->put('/siswa/' . $siswa->id, [
            'lembaga_id' => $lembagaTutor->id,
            'nis' => $siswa->nis,
            'nama' => 'Muhammad Rizqi Updated',
            'email' => 'rizqi.updated@example.com',
        ]);

        $response->assertRedirect(route('siswa.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('siswas', [
            'id' => $siswa->id,
            'nama' => 'Muhammad Rizqi Updated',
            'email' => 'rizqi.updated@example.com',
            'lembaga_id' => $lembagaTutor->id,
        ]);
    }

    /**
     * Test 9: Berhasil menghapus data siswa
     */
    public function test_can_delete_siswa_successfully(): void
    {
        $user = User::where('email', 'admin@latis.com')->first();
        $siswa = Siswa::where('nis', '10099887')->first();

        if ($siswa) {
            $response = $this->actingAs($user)->delete('/siswa/' . $siswa->id);
            $response->assertRedirect(route('siswa.index'));
            $this->assertDatabaseMissing('siswas', ['id' => $siswa->id]);
        }
    }

    /**
     * Test 10: Ekspor Excel berfungsi dan menghasilkan response attachment .xlsx
     */
    public function test_export_excel_returns_xlsx_stream(): void
    {
        $user = User::where('email', 'admin@latis.com')->first();
        $latis = Lembaga::where('name', 'Latiseducation')->first();

        // 1. Ekspor seluruh siswa
        $responseAll = $this->actingAs($user)->get('/siswa/export');
        $responseAll->assertStatus(200);
        $this->assertTrue(
            str_contains($responseAll->headers->get('content-type'), 'spreadsheetml') ||
            str_contains($responseAll->headers->get('content-type'), 'octet-stream')
        );

        // 2. Ekspor dengan filter lembaga & search aktif
        $responseFiltered = $this->actingAs($user)->get('/siswa/export?lembaga_id=' . ($latis ? $latis->id : 1) . '&search=Ahmad');
        $responseFiltered->assertStatus(200);
        $this->assertStringContainsString('attachment; filename=', $responseFiltered->headers->get('content-disposition'));
    }

    /**
     * Test 11: Profil kandidat menampilkan Nama, Posisi, dan Image
     */
    public function test_candidate_profile_can_be_viewed_and_updated(): void
    {
        $user = User::where('email', 'admin@latis.com')->first();

        $response = $this->actingAs($user)->get('/profile');
        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($user->position);
        $response->assertSee('Profil Kandidat');

        // Update profil
        $responseUpdate = $this->actingAs($user)->put('/profile', [
            'name' => 'Kandidat Senior Engineer',
            'position' => 'Senior Fullstack Engineer',
            'email' => 'admin@latis.com',
        ]);

        $responseUpdate->assertRedirect(route('profile.index'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Kandidat Senior Engineer',
            'position' => 'Senior Fullstack Engineer',
        ]);
    }

    /**
     * Test 12: Logout membersihkan session dan redirect ke login
     */
    public function test_user_can_logout_and_session_is_invalidated(): void
    {
        $user = User::where('email', 'admin@latis.com')->first();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }
}
