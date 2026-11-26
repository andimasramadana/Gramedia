@extends('layouts.app')

@push('styles')
<style>
    #subscription-packages-table_wrapper > .dt-layout-row:has(.dt-info) {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 0.5rem;
        padding: 0.75rem 0.25rem 0;
        border-top: 1px solid #e6e8eb;
    }

    #subscription-packages-table_wrapper .dt-paging nav {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    #subscription-packages-table_wrapper .dt-paging-button {
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

    #subscription-packages-table_wrapper .dt-paging-button.current {
        border-color: #ced4da !important;
        background: #f8f9fa !important;
        color: #212529 !important;
    }

    #subscription-packages-table_wrapper .dt-paging-button:hover:not(.disabled):not(.current) {
        background: #f1f3f5 !important;
    }

    #subscription-packages-table_wrapper .dt-paging-button.disabled {
        opacity: 0.45;
    }

    @media (max-width: 575.98px) {
        #subscription-packages-table_wrapper > .dt-layout-row:last-child {
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Paket Langganan</h1>
        <div class="d-flex gap-2">
        <a href="{{ route('admin.subscription-packages.create') }}" class="btn btn-primary">Tambah Data</a>
        <a href="{{ route('admin.subscription-packages.export') }}" class="btn btn-green">Export Excel</a>
    </div>
</div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-circle-check fs-4 me-2"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-triangle-exclamation fs-4 me-2"></i>
                <div>{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body table-responsive">
            <table id="subscription-packages-table" class="table table-bordered table-vcenter w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Paket</th>
                        <th>Warna</th>
                        <th>Harga</th>
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
        $('#subscription-packages-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: @json(route('admin.subscription-packages.index')),
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name_package', name: 'name_package' },
                { data: 'color_badge', name: 'color', orderable: false, searchable: false },
                { data: 'price', name: 'price' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            order: [[1, 'asc']],
            drawCallback: function () {
                const table = this.api();
                const hasPackages = table.page.info().recordsTotal > 0;
                const wrapper = $(table.table().container());

                wrapper.children('.dt-layout-row').first().toggle(hasPackages);
                wrapper.children('.dt-layout-row').has('.dt-info').toggle(hasPackages);
            },
            language: {
                emptyTable: 'Belum ada paket langganan.',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                infoEmpty: 'Showing 0 to 0 of 0 entries',
                lengthMenu: 'Tampilkan _MENU_ paket',
                loadingRecords: 'Memuat...',
                processing: 'Memproses...',
                search: 'Cari:',
                zeroRecords: 'Paket tidak ditemukan',
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
