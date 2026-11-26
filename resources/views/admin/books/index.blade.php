@extends('layouts.app')

@push('styles')
<style>
    #books-table_wrapper > .dt-layout-row:has(.dt-info) {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 0.5rem;
        padding: 0.75rem 0.25rem 0;
        border-top: 1px solid #e6e8eb;
    }

    #books-table_wrapper .dt-paging nav {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    #books-table_wrapper .dt-paging-button {
        display: inline-flex;
        min-width: 2.5rem;
        height: 2.5rem;
        align-items: center;
        justify-content: center;
        padding: 0 0.65rem;
        border: 1px solid transparent !important;
        border-radius: 0.25rem !important;
        background: transparent !important;
        color: #475467 !important;
        text-decoration: none;
    }

    #books-table_wrapper .dt-paging-button.current {
        border-color: #ced4da !important;
        background: #f8f9fa !important;
        color: #212529 !important;
    }

    #books-table_wrapper .dt-paging-button:hover:not(.disabled):not(.current) {
        background: #f1f3f5 !important;
    }

    #books-table_wrapper .dt-paging-button.disabled {
        opacity: 0.45;
    }

    @media (max-width: 575.98px) {
        #books-table_wrapper > .dt-layout-row:last-child {
            justify-content: center;
        }
    }

    .book-detail-modal {
        width: min(48rem, calc(100% - 2rem));
        max-width: none;
        max-height: calc(100% - 2rem);
        padding: 0;
        overflow: auto;
        border: 0;
        border-radius: 0.5rem;
        box-shadow: 0 1rem 3rem rgb(0 0 0 / 20%);
    }

    .book-detail-modal::backdrop {
        background: rgb(0 0 0 / 50%);
    }

    .book-detail-modal__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e6e8eb;
    }

    .book-detail-modal__header h2 {
        margin: 0;
        font-size: 1.25rem;
    }

    .book-detail-modal__body {
        padding: 1.25rem;
    }

    .book-detail-modal__body .book-cover {
        width: 6rem;
        max-height: 8rem;
        object-fit: cover;
    }
</style>
@endpush

@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Buku</h1>
        <div class="d-flex gap-2">
        <a href="{{ route('admin.books.create') }}" class="btn btn-primary">Tambah Buku</a>
        <a href="{{ route('admin.books.export') }}" class="btn btn-green">Export Excel</a>
    </div>
</div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body table-responsive">
            <table id="books-table" class="table table-bordered table-vcenter w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Sampul</th>
                        <th>Judul</th>
                        <th>Penulis</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<dialog id="book-detail-modal" class="book-detail-modal" aria-labelledby="book-detail-title">
    <div class="book-detail-modal__header">
        <h2 id="book-detail-title">Detail Buku</h2>
        <button type="button" class="btn-close" data-book-detail-close aria-label="Tutup"></button>
    </div>
    <div id="book-detail-content" class="book-detail-modal__body"></div>
</dialog>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#books-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: @json(route('admin.books.index')),
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'cover', name: 'cover', orderable: false, searchable: false },
                { data: 'title', name: 'title' },
                { data: 'writer', name: 'writer' },
                { data: 'category_name', name: 'bookCategory.name' },
                { data: 'price', name: 'price' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            order: [[2, 'asc']],
            language: {
                emptyTable: 'Belum ada buku.',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ buku',
                infoEmpty: 'Menampilkan 0 sampai 0 dari 0 buku',
                lengthMenu: 'Tampilkan _MENU_ buku',
                loadingRecords: 'Memuat...',
                processing: 'Memproses...',
                search: 'Cari:',
                zeroRecords: 'Buku tidak ditemukan',
                paginate: {
                    first: '«',
                    last: '»',
                    next: '›',
                    previous: '‹',
                },
            },
        });
    });
</script>
@endpush

@push('scripts')
<script>
    $(document).on('click', '.btn-detail', function () {
        const button = this;
        const book = {
            cover: button.dataset.cover,
            title: button.dataset.title,
            category: button.dataset.category,
            price: button.dataset.price,
            writer: button.dataset.writer,
            publisher: button.dataset.publisher,
            language: button.dataset.language,
            release: button.dataset.release,
            pages: button.dataset.pages,
            description: button.dataset.description,
        };
        const escapeHtml = (value) => String(value ?? '-').replace(/[&<>"']/g, (character) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        })[character]);
        const description = new DOMParser()
            .parseFromString(book.description || '', 'text/html')
            .body.textContent
            .trim();
        const detailTable = `
            <table class="table table-bordered table-vcenter mb-0">
                <tbody>
                    <tr><th scope="row">Sampul</th><td><img class="book-cover" src="${escapeHtml(book.cover)}" alt="Sampul ${escapeHtml(book.title)}"></td></tr>
                    <tr><th scope="row">Judul</th><td>${escapeHtml(book.title)}</td></tr>
                    <tr><th scope="row">Penulis</th><td>${escapeHtml(book.writer)}</td></tr>
                    <tr><th scope="row">Kategori</th><td>${escapeHtml(book.category)}</td></tr>
                    <tr><th scope="row">Penerbit</th><td>${escapeHtml(book.publisher)}</td></tr>
                    <tr><th scope="row">Harga</th><td>${escapeHtml(book.price)}</td></tr>
                    <tr><th scope="row">Bahasa</th><td>${escapeHtml(book.language)}</td></tr>
                    <tr><th scope="row">Tanggal Rilis</th><td>${escapeHtml(book.release)}</td></tr>
                    <tr><th scope="row">Jumlah Halaman</th><td>${escapeHtml(book.pages)}</td></tr>
                    <tr><th scope="row">Deskripsi</th><td>${escapeHtml(description || '-')}</td></tr>
                </tbody>
            </table>
        `;

        document.getElementById('book-detail-content').innerHTML = detailTable;
        document.getElementById('book-detail-modal').showModal();
    });

    $(document).on('click', '[data-book-detail-close]', function () {
        document.getElementById('book-detail-modal').close();
    });

    $('#book-detail-modal').on('click', function (event) {
        if (event.target === this) {
            this.close();
        }
    });
</script>
@endpush
