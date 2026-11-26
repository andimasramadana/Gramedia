@extends('layouts.app')

@push('styles')
<style>
    #book-categories-table_wrapper > .dt-layout-row:has(.dt-info) {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 0.5rem;
        padding: 0.75rem 0.25rem 0;
        border-top: 1px solid #e6e8eb;
    }

    #book-categories-table_wrapper .dt-paging nav {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    #book-categories-table_wrapper .dt-paging-button {
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

    #book-categories-table_wrapper .dt-paging-button.current {
        border-color: #ced4da !important;
        background: #f8f9fa !important;
        color: #212529 !important;
    }

    #book-categories-table_wrapper .dt-paging-button:hover:not(.disabled):not(.current) {
        background: #f1f3f5 !important;
    }

    #book-categories-table_wrapper .dt-paging-button.disabled {
        opacity: 0.45;
    }

    @media (max-width: 575.98px) {
        #book-categories-table_wrapper > .dt-layout-row:last-child {
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Kategori Buku</h1>
        <div class="d-flex gap-2">
        <a href="{{ route('admin.book-categories.create') }}" class="btn btn-primary">Tambah Data</a>
        <a href="{{ route('admin.book-categories.export') }}" class="btn btn-green">Export Excel</a>
</div>
</div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body table-responsive">
            <table id="book-categories-table" class="table table-bordered table-vcenter w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kategori</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#book-categories-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: @json(route('admin.book-categories.index')),
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            order: [[1, 'asc']],
            drawCallback: function () {
                const table = this.api();
                const hasCategories = table.page.info().recordsTotal > 0;
                const wrapper = $(table.table().container());

                wrapper.children('.dt-layout-row').first().toggle(hasCategories);
                wrapper.children('.dt-layout-row').has('.dt-info').toggle(hasCategories);
            },
            language: {
                emptyTable: 'Belum ada kategori buku.',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                infoEmpty: 'Showing 0 to 0 of 0 entries',
                lengthMenu: 'Tampilkan _MENU_ kategori',
                loadingRecords: 'Memuat...',
                processing: 'Memproses...',
                search: 'Cari:',
                zeroRecords: 'Kategori tidak ditemukan',
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
