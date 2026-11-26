@extends('layouts.app')

@section('content')
<div class="card mt-5 w-50 d-block mx-auto">
    <div class="card-header">
        <h1 class="mb-0">Edit Paket Langganan</h1>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.subscription-packages.update', $subscriptionPackage) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="name_package" class="form-label">Nama Paket</label>
                <input type="text" class="form-control @error('name_package') is-invalid @enderror" id="name_package" name="name_package" value="{{ old('name_package', $subscriptionPackage->name_package) }}" maxlength="255" required>
                @error('name_package')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="color" class="form-label">Warna Tema Paket</label>
                <div class="input-group">
                    <input type="color" class="form-control form-control-color" id="color_picker" value="{{ old('color', $subscriptionPackage->color ?? '#a7f1c6') }}" title="Pilih warna">
                    <input type="text" class="form-control @error('color') is-invalid @enderror" id="color" name="color" value="{{ old('color', $subscriptionPackage->color ?? '#a7f1c6') }}" placeholder="#a7f1c6">
                </div>
                <small class="form-hint text-muted">Pilih atau ketik kode warna HEX (misal: #a7f1c6, #a49dd0, #f1b8a7) untuk aksen latar belakang di beranda.</small>
                @error('color')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Harga (Rupiah)</label>
                <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $subscriptionPackage->price) }}" min="0" step="1" required>
                @error('price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="books" class="form-label">Buku dalam Paket</label>
                <input type="hidden" name="books_present" value="1">
                @php($selectedBookIds = old('books', $subscriptionPackage->books->modelKeys()))
                <select id="books" name="books[]" class="form-select @error('books') is-invalid @enderror" multiple size="8">
                    @foreach($books as $book)
                        <option value="{{ $book->id }}" @selected(in_array($book->id, $selectedBookIds))>
                            {{ $book->title }} — {{ $book->writer }}
                        </option>
                    @endforeach
                </select>
                <small class="form-hint">Pilih satu atau beberapa buku. Hapus semua pilihan untuk mengosongkan paket.</small>
                @error('books')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                @error('books.*')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                @if($books->isEmpty())
                    <p class="text-muted mt-2 mb-0">Belum ada buku yang tersedia untuk ditambahkan.</p>
                @endif
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="{{ route('admin.subscription-packages.index') }}" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const picker = document.getElementById('color_picker');
        const text = document.getElementById('color');

        if (picker && text) {
            picker.addEventListener('input', function () {
                text.value = picker.value;
            });
            text.addEventListener('input', function () {
                if (/^#[0-9A-F]{6}$/i.test(text.value)) {
                    picker.value = text.value;
                }
            });
        }
    });
</script>
@endpush
