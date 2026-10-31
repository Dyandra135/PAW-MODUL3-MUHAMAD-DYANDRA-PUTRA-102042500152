@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="fw-bold mb-1">Edit Peminjaman</h3>
                <p class="text-muted small mb-0">Perbarui anggota, buku, atau tanggal pinjam.</p>
            </div>
            <a href="{{ route('peminjaman.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('peminjaman.update', $peminjaman) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="anggota_id" class="form-label">Anggota Peminjam</label>
                        <select name="anggota_id" id="anggota_id" class="form-select @error('anggota_id') is-invalid @enderror" required>
                            @foreach ($anggota as $item)
                                <option value="{{ $item->id }}" {{ old('anggota_id', $peminjaman->anggota_id) == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('anggota_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="buku_id" class="form-label">Buku</label>
                        <select name="buku_id" id="buku_id" class="form-select @error('buku_id') is-invalid @enderror" required>
                            @foreach ($buku as $item)
                                <option value="{{ $item->id }}" {{ old('buku_id', $peminjaman->buku_id) == $item->id ? 'selected' : '' }}>
                                    {{ $item->judul }} (Stok: {{ $item->stok }})
                                </option>
                            @endforeach
                        </select>
                        @error('buku_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="tanggal_pinjam" class="form-label">Tanggal Pinjam</label>
                        <input type="date" name="tanggal_pinjam" id="tanggal_pinjam"
                            class="form-control @error('tanggal_pinjam') is-invalid @enderror"
                            value="{{ old('tanggal_pinjam', $peminjaman->tanggal_pinjam) }}" required>
                        @error('tanggal_pinjam')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('peminjaman.index') }}" class="btn btn-light border">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
