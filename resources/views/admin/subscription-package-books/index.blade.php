@extends('layouts.app')

@section('content')
<div class="container mt-5">
    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif
    <form action="{{ route('admin.subscription-package-books.store', $subscriptionPackage->id) }}" method="POST">
        @csrf
    <div class="d-flex justify-content-between">
        <h1>Data Buku yang akan ditambahkan ke paket {{ $subscriptionPackage->name_package }}</h1>
        {{-- btn akan disembunyikan jika belum bemilih buku (d-none) --}}
        {{-- (0) : kan diganti menjadi jumlah yg diceklis --}}
        <button class="d-none btn btn-primary" type="submit" id="btnBuku"><span id="jmlhBuku">(0)</span>Tambah Buku</button>
    </div>

    <table class="table table-bordered" id="books-table">
        <thead>
            <tr>
                <th>Pilih Buku</th>
                <th></th>
                <th>Sampul Buku</th>
                <th>Judul</th>
                <th>Kategori Buku</th>
                <th>Penulis</th>
                <th>Harga</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    </form>
</div>

<div class="modal" id="exampleModal4" tabindex="-1">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New report</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Name</label><input type="text" class="form-control" name="example-text-input" placeholder="Your report name" /></div><label class="form-label">Report type</label>
                <div class="form-selectgroup-boxes row mb-3">
                    <div class="col-md-6"><label class="form-selectgroup-item"><input type="radio" name="report-type" value="1" class="form-selectgroup-input" checked /><span class="form-selectgroup-label d-flex align-items-center p-3"><span class="me-3"><span class="form-selectgroup-check"></span></span><span class="form-selectgroup-label-content"><span class="form-selectgroup-title strong mb-1">Simple</span><span class="d-block text-secondary"> Provide only basic data needed for the report </span></span></span></label></div>
                    <div class="col-md-6"><label class="form-selectgroup-item"><input type="radio" name="report-type" value="1" class="form-selectgroup-input" /><span class="form-selectgroup-label d-flex align-items-center p-3"><span class="me-3"><span class="form-selectgroup-check"></span></span><span class="form-selectgroup-label-content"><span class="form-selectgroup-title strong mb-1">Advanced</span><span class="d-block text-secondary"> Insert charts and additional advanced analyses to be inserted in the report </span></span></span></label></div>
                </div>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="mb-3"><label class="form-label">Report url</label>
                            <div class="input-group input-group-flat"><span class="input-group-text"><a href="https://tabler.io/reports/">https://tabler.io/reports/</a></span><input type="text" class="form-control ps-0" value="report-01" autocomplete="off" /></div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="mb-3"><label class="form-label">Visibility</label><select class="form-select">
                                <option value="1" selected>Private</option>
                                <option value="2">Public</option>
                                <option value="3">Hidden</option>
                            </select></div>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="mb-3"><label class="form-label">Client name</label><input type="text" class="form-control" /></div>
                    </div>
                    <div class="col-lg-6">
                        <div class="mb-3"><label class="form-label">Reporting period</label><input type="date" class="form-control" /></div>
                    </div>
                    <div class="col-lg-12">
                        <div><label class="form-label">Additional information</label><textarea class="form-control" rows="3"></textarea></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer"><a href="#" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancel</a><a href="#" class="btn btn-primary ms-auto" data-bs-dismiss="modal"> <!-- Download SVG icon from http://tabler.io/icons/icon/plus -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false" class="icon">
                        <path d="M12 5l0 14" />
                        <path d="M5 12l14 0" />
                    </svg> Create new report </a></div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const table = $('#books-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.subscription-package-books', ['packageId' => $subscriptionPackage->id]) }}",
            columns: [
                {
                    //yg dikirim ke db ketika memilih ceklis buku adalah id nya
                    data: null,
                    orderable: false,
                    searchable: false,
                    //yg dikirim ke db ketika memilih ceklis buku adalah id nya
                    data: 'id',
                    name: 'id',
                    //mengganti tampilah angka id jadi checkbox
                    render: function(data){
                        //name menggunakan [] (array) karena bisa memilih lebih dari 1 buku
                        if (data.checkBook == true){
                            return " ";
                        }else {return `<input type="checkbox" value="${data.id}" class="inputBookId" name="bookIds[]">`};
                    }
                },
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'cover',
                    name: 'cover',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'title',
                    name: 'title'
                },
                {
                    data: 'category_name',
                    name: 'category_name'
                },
                {
                    data: 'writer',
                    name: 'writer'
                },
                {
                    data: 'price',
                    name: 'price'
                }
            ]
        });
        //fungsi yang akan dipanggil tiap ada buku yg dipilih
        function updateSelectedBook(){
            //ambil jumlah class= "inputBookId"
            let selectedCount= $(".inputBookId:checked").length;
            //jumlah munculin ke html (0)
            $("#jmlhBuku").text(selectedCount);

            //munculkan tombol dengan menghilangkan d-none
            if (selectedCount > 0){
                $("#btnBuku").removeClass('d-none');
            }else {
                //kalu yg dipilih 0, d-none ditambahkan biar btn ilang
                $("#btnBuku").addClass('d-none');
            }
        }
        //panggil fungsi tiap kali checkbox di ceklis atau sebaliknya (un-ceklis)
        $("#books-table tbody").on("change", ".inputBookId", function() {
            updateSelectedBook();
        })
    });
</script>
@endpush