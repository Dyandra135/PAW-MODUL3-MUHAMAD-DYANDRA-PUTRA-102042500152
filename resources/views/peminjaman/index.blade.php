@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Daftar Peminjaman</h2>
        <p class="text-muted mb-0">Kelola transaksi peminjaman dan pengembalian buku.</p>
    </div>
    <a href="{{ route('peminjaman.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah Peminjaman
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 50px;">No</th>
                        <th>Nama Anggota</th>
                        <th>Judul Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Tanggal Kembali</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-3" style="width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- TODO:- Tampilkan daftar transaksi peminjaman menggunakan looping @forelse ($peminjaman as $index => $item) --}}
                    {{--      - Tampilkan data kolom: Nomor, Nama Anggota, Judul Buku, Tanggal Pinjam, Tanggal Kembali, Status, dan Tombol Aksi --}}
                    {{--      - Tampilkan pesan jika data peminjaman masih kosong pada blok @empty --}}

                    @forelse ($peminjaman as $index => $item)
                    <tr>
                        <td class="ps-3">
                            {{ $peminjaman->firstItem() + $index }}
                        </td>
                        <td>{{ $item->anggota?->nama ?? '-' }}</td>
                        <td>{{ $item->buku?->judul ?? '-' }}</td>
                        <td>
                            {{ $item->tanggal_pinjam
                    ? \Carbon\Carbon::parse($item->tanggal_pinjam)->format('d/m/Y')
                    : '-' }}
                        </td>
                        <td>
                            {{ $item->tanggal_kembali
                    ? \Carbon\Carbon::parse($item->tanggal_kembali)->format('d/m/Y')
                    : '-' }}
                        </td>
                        <td class="text-center">
                            <span
                                class="badge {{ $item->status === 'dikembalikan' ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ ucfirst($item->status ?? '-') }}
                            </span>
                        </td>
                        <td class="text-end pe-3">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('peminjaman.edit', $item->id) }}" class="btn btn-sm btn-warning"
                                    aria-label="Edit peminjaman">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                <form action="{{ route('peminjaman.destroy', $item->id) }}" method="POST"
                                    onsubmit="return confirm('Yakin ingin menghapus data peminjaman ini?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-sm btn-danger" aria-label="Hapus peminjaman">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            Belum ada data peminjaman.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($peminjaman->hasPages())
    <div class="card-footer bg-white py-3">
        {{ $peminjaman->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection