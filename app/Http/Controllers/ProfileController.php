<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil kandidat
     */
    public function index(): View
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    /**
     * Perbarui data profil kandidat: Nama, Position, dan Foto Kandidat
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'position'              => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'avatar'                => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'password'              => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required'         => 'Nama kandidat wajib diisi.',
            'position.required'     => 'Posisi kandidat wajib diisi.',
            'email.required'        => 'Email kandidat wajib diisi.',
            'email.email'           => 'Format email tidak valid.',
            'email.unique'          => 'Email sudah digunakan oleh akun lain.',
            'avatar.image'          => 'File foto kandidat harus berupa gambar.',
            'avatar.mimes'          => 'Format foto kandidat yang diizinkan hanya JPG dan PNG.',
            'avatar.max'            => 'Ukuran foto kandidat maksimal 2MB.',
            'password.min'          => 'Password minimal 6 karakter.',
            'password.confirmed'    => 'Konfirmasi password tidak cocok.',
        ]);

        $avatarPath = $user->avatar;
        if ($request->hasFile('avatar')) {
            // Hapus avatar lama jika ada
            if ($avatarPath && Storage::disk('public')->exists($avatarPath)) {
                Storage::disk('public')->delete($avatarPath);
            }

            $file = $request->file('avatar');
            $filename = 'avatar_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $avatarPath = $file->storeAs('avatars', $filename, 'public');
        }

        $user->name = $validated['name'];
        $user->position = $validated['position'];
        $user->email = $validated['email'];
        $user->avatar = $avatarPath;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('profile.index')
            ->with('success', 'Profil kandidat berhasil diperbarui.');
    }
}
