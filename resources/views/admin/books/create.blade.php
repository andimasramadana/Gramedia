@extends('layouts.app')

@section('content')
<div class="card w-75 d-block mx-auto mt-3">
    <div class="card-header">
        <h1>Tambah Buku</h1>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.books.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="book_category_id" class="form-label">Kategori Buku</label>
                    <select name="book_category_id" id="book_category_id" class="form-select @error('book_category_id') is-invalid @enderror" required>
                        <option value="">Pilih Kategori Buku</option>
                        @foreach ($bookCategories as $bookCategory)
                            <option value="{{ $bookCategory->id }}" @selected((string) old('book_category_id') === (string) $bookCategory->id)>{{ $bookCategory->name }}</option>
                        @endforeach
                    </select>
                    @error('book_category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="title" class="form-label">Judul Buku</label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" id="title" value="{{ old('title') }}" maxlength="255" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="writer" class="form-label">Penulis</label>
                    <input type="text" class="form-control @error('writer') is-invalid @enderror" name="writer" id="writer" value="{{ old('writer') }}" maxlength="255" required>
                    @error('writer')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="publisher" class="form-label">Penerbit</label>
                    <input type="text" class="form-control @error('publisher') is-invalid @enderror" name="publisher" id="publisher" value="{{ old('publisher') }}" maxlength="255" required>
                    @error('publisher')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="price" class="form-label">Harga (Rupiah)</label>
                    <input type="number" class="form-control @error('price') is-invalid @enderror" name="price" id="price" value="{{ old('price') }}" min="0" step="any" required>
                    @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="language" class="form-label">Bahasa</label>
                    <select class="form-select @error('language') is-invalid @enderror" name="language" id="language" required>
                        <option value="">Pilih Bahasa</option>
                        <option value="Indonesia" @selected(old('language') === 'Indonesia')>Indonesia</option>
                        <option value="English" @selected(old('language') === 'English')>English</option>
                        <option value="Japan" @selected(old('language') === 'Japan')>Japan</option>
                        <option value="Arabic" @selected(old('language') === 'Arabic')>Arabic</option>
                    </select>
                    @error('language')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="release_date" class="form-label">Tanggal Rilis</label>
                    <input type="date" class="form-control @error('release_date') is-invalid @enderror" name="release_date" id="release_date" value="{{ old('release_date') }}" required>
                    @error('release_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="page_of_book" class="form-label">Jumlah Halaman</label>
                    <input type="number" class="form-control @error('page_of_book') is-invalid @enderror" name="page_of_book" id="page_of_book" value="{{ old('page_of_book') }}" min="1" step="1" required>
                    @error('page_of_book')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="cover" class="form-label">Sampul Buku</label>
                <input type="file" class="form-control @error('cover') is-invalid @enderror" name="cover" id="cover" accept="image/jpeg,image/png,image/webp" required>
                <div class="form-hint">Format JPG, PNG, atau WebP. Maksimal 2 MB.</div>
                @error('cover')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="description-editor" class="form-label">Deskripsi Buku</label>
                <div id="description-editor"></div>
                <input type="hidden" name="description" id="description-input" value="{{ old('description') }}">
                @error('description')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Simpan Buku</button>
            <a href="{{ route('admin.books.index') }}" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const descriptionForm = document.querySelector('form[action="{{ route('admin.books.store') }}"]');
    const descriptionInput = document.getElementById('description-input');
    const quill = new Quill('#description-editor', {
        theme: 'snow'
    });

    if (descriptionInput.value) {
        quill.clipboard.dangerouslyPasteHTML(descriptionInput.value);
        descriptionInput.value = quill.root.innerHTML;
    }

    quill.on('text-change', function () {
        descriptionInput.value = quill.root.innerHTML;
    });

    descriptionForm.addEventListener('submit', function () {
        descriptionInput.value = quill.root.innerHTML;
    });
</script>
@endpush