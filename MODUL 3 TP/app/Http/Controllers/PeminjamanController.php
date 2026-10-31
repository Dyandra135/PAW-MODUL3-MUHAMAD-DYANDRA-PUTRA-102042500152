<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamanController extends Controller
{
    /**
     * Menampilkan daftar semua transaksi peminjaman buku.
     */
    public function index()
    {
        $peminjaman = Peminjaman::with(['anggota', 'buku'])
            ->latest()
            ->paginate(10);

        return view('peminjaman.index', compact('peminjaman'));
    }

    /**
     * Menampilkan form peminjaman buku.
     */
    public function create()
    {
        $anggota = Anggota::orderBy('nama')->get();
        // Buku dengan stok 0 tidak boleh dipilih
        $buku = Buku::where('stok', '>', 0)->orderBy('judul')->get();

        return view('peminjaman.create', compact('anggota', 'buku'));
    }

    /**
     * Menyimpan transaksi peminjaman buku baru.
     */
    public function store(Request $request)
    {
        // TODO:- Lakukan validasi input: anggota_id, buku_id, dan tanggal_pinjam
        //      - Simpan data transaksi ke database menggunakan Peminjaman::create() dengan status 'Dipinjam'
        //      - Redirect ke halaman index peminjaman dengan pesan sukses
        $validasi = $request->validate([
            'anggota_id' => 'required|exists:anggota,id',
            'buku_id' => 'required|exists:buku,id',
            'tanggal_pinjam' => 'required|date',
        ], [
            'anggota_id.required' => 'Anggota wajib dipilih.',
            'anggota_id.exists' => 'Anggota yang dipilih tidak valid.',
            'buku_id.required' => 'Buku wajib dipilih.',
            'buku_id.exists' => 'Buku yang dipilih tidak valid.',
            'tanggal_pinjam.required' => 'Tanggal pinjam wajib diisi.',
            'tanggal_pinjam.date' => 'Format tanggal pinjam tidak valid.',
        ]);
        
        try {
            DB::transaction(function()use($validasi){
                $buku = Buku::lockForUpdate()->findOrFail($validasi['buku_id']);
                if ($buku->stok <= 1) {
                    throw new \Exception("Buku abis.");
                }

                Peminjaman::create($validasi + ['status' => 'Dipinjam']);
                $buku->decrement('stok');
            });
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan peminjaman: ' . $e->getMessage());
        }

        return redirect()->route('peminjaman.index')->with('success', 'Peminjaman berhasil dilakukan');
    }

    /**
     * Menampilkan detail peminjaman buku.
     */
    public function show(Peminjaman $peminjaman)
    {
        $peminjaman->load(['anggota', 'buku']);

        return view('peminjaman.show', compact('peminjaman'));
    }

    /** Menampilkan form untuk mengubah transaksi peminjaman yang masih aktif. */
    public function edit(Peminjaman $peminjaman)
    {
        if ($peminjaman->status !== 'Dipinjam') {
            return redirect()->route('peminjaman.index')
                ->with('error', 'Transaksi yang sudah dikembalikan tidak dapat diedit.');
        }

        $anggota = Anggota::orderBy('nama')->get();
        // Sertakan buku transaksi saat ini walaupun stoknya 0.
        $buku = Buku::where('stok', '>', 0)
            ->orWhere('id', $peminjaman->buku_id)
            ->orderBy('judul')->get();

        return view('peminjaman.edit', compact('peminjaman', 'anggota', 'buku'));
    }

    /** Memperbarui transaksi dan menjaga stok buku tetap konsisten. */
    public function update(Request $request, Peminjaman $peminjaman)
    {
        if ($peminjaman->status !== 'Dipinjam') {
            return redirect()->route('peminjaman.index')
                ->with('error', 'Transaksi yang sudah dikembalikan tidak dapat diedit.');
        }

        $validated = $request->validate([
            'anggota_id' => 'required|exists:anggota,id',
            'buku_id' => 'required|exists:buku,id',
            'tanggal_pinjam' => 'required|date',
        ]);

        try {
            DB::transaction(function () use ($validated, $peminjaman) {
                $current = Peminjaman::lockForUpdate()->findOrFail($peminjaman->id);
                if ($current->status !== 'Dipinjam') {
                    throw new \RuntimeException('Transaksi sudah dikembalikan dan tidak dapat diedit.');
                }

                if ((int) $validated['buku_id'] !== (int) $current->buku_id) {
                    $bukuLama = Buku::lockForUpdate()->findOrFail($current->buku_id);
                    $bukuBaru = Buku::lockForUpdate()->findOrFail($validated['buku_id']);
                    if ($bukuBaru->stok < 1) {
                        throw new \RuntimeException('Stok buku pengganti sedang habis.');
                    }

                    $bukuLama->increment('stok');
                    $bukuBaru->decrement('stok');
                }

                $current->update($validated);
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('peminjaman.index')->with('success', 'Data peminjaman berhasil diperbarui.');
    }

    /**
     * Memproses pengembalian buku.
     */
    public function kembali(Peminjaman $peminjaman)
    {
        // Tolak jika buku sudah dikembalikan sebelumnya
        if ($peminjaman->status === 'Dikembalikan') {
            return redirect()->route('peminjaman.index')
                ->with('error', 'Buku pada transaksi ini sudah pernah dikembalikan sebelumnya.');
        }

        try {
            DB::transaction(function () use ($peminjaman) {
                // Tambahkan stok buku sebesar 1
                $buku = Buku::lockForUpdate()->findOrFail($peminjaman->buku_id);
                $buku->increment('stok');

                // Update status dan tanggal kembali
                $peminjaman->update([
                    'status' => 'Dikembalikan',
                    'tanggal_kembali' => now()->toDateString(),
                ]);
            });

            return redirect()->route('peminjaman.index')
                ->with('success', 'Buku berhasil dikembalikan!');
        } catch (\Exception $e) {
            return redirect()->route('peminjaman.index')
                ->with('error', 'Gagal memproses pengembalian: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus catatan peminjaman.
     */
    public function destroy(Peminjaman $peminjaman)
    {
        // Jika masih dipinjam, kembalikan stok buku sebelum dihapus
        if ($peminjaman->status === 'Dipinjam') {
            $peminjaman->buku()->increment('stok');
        }

        $peminjaman->delete();

        return redirect()->route('peminjaman.index')
            ->with('success', 'Data peminjaman berhasil dihapus!');
    }
}
