<?php

namespace App\Http\Controllers;

use App\Models\MataKuliah;
use App\Models\Material;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MaterialController extends Controller
{
    public function create(MataKuliah $course): View|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!Auth::user()->isDosen() || $course->dosen_id !== Auth::id()) {
            abort(403, 'Akses ditolak. Anda bukan dosen pengampu mata kuliah ini.');
        }

        $nextMinggu = ($course->materials()->max('minggu_ke') ?? 0) + 1;

        return view('materials.create', compact('course', 'nextMinggu'));
    }

    public function store(Request $request, MataKuliah $course): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!Auth::user()->isDosen() || $course->dosen_id !== Auth::id()) {
            abort(403, 'Akses ditolak. Anda bukan dosen pengampu mata kuliah ini.');
        }

        // TODO 5: Validasi data input, simpan materi pembelajaran baru, dan redirect ke detail mata kuliah.
        // Petunjuk:
        // 1. Periksa kelayakan input (judul, konten, minggu_ke, urutan) menggunakan validasi Laravel pada $request.
        // 2. Simpan materi baru yang terhubung ke mata kuliah ($course) melalui model Material atau relasi $course->materials().
        // 3. Kembalikan redirect ke route detail mata kuliah ('courses.show') dengan pesan sukses.
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'konten' => ['required', 'string'],
            'minggu_ke' => ['required', 'integer', 'min:1'],
            'urutan' => ['required', 'integer', 'min:1'],
        ]);

        $course->materials()->create($validated);
        
        return redirect()->route('courses.show', $course->id)
            ->with('success', "Materi \"{$validated['judul']}\" berhasil ditambahkan ke mata kuliah \"{$course->nama_mk}\".");
    }

    public function edit(Material $material): View|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!Auth::user()->isDosen() || $material->course->dosen_id !== Auth::id()) {
            abort(403, 'Akses ditolak. Anda bukan dosen pengampu mata kuliah materi ini.');
        }

        return view('materials.edit', compact('material'));
    }

    public function update(Request $request, Material $material): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!Auth::user()->isDosen() || $material->course->dosen_id !== Auth::id()) {
            abort(403, 'Akses ditolak. Anda bukan dosen pengampu mata kuliah materi ini.');
        }

        // TODO 7: Validasi data input, perbarui data materi pembelajaran, dan redirect ke detail mata kuliah.
        // Petunjuk:
        // 1. Periksa kelayakan input (judul, konten, minggu_ke, urutan) menggunakan validasi Laravel pada $request.
        // 2. Perbarui data materi ($material) menggunakan data yang telah divalidasi.
        // 3. Kembalikan redirect ke route detail mata kuliah ('courses.show') terkait dengan pesan sukses.
    }

    public function destroy(Material $material): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!Auth::user()->isDosen() || $material->course->dosen_id !== Auth::id()) {
            abort(403, 'Akses ditolak. Anda bukan dosen pengampu mata kuliah materi ini.');
        }

        // TODO 7: Hapus data materi pembelajaran dan redirect ke detail mata kuliah.
        // Petunjuk:
        // 1. Simpan id mata kuliah ($material->course_id) sebelum materi dihapus agar rute redirect tetap valid.
        // 2. Hapus data materi ($material) dari basis data.
        // 3. Kembalikan redirect ke route detail mata kuliah ('courses.show') dengan pesan sukses.
    }
}