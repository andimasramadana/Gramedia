@extends('layouts.app')

@push('styles')
    <style>
        .description-clamped {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .style-width-label {
            min-width: 110px;
            display: inline-block;
        }
    </style>
@endpush

@section('content')
<form method="POST" action="{{ route('carts.store') }}">
    @csrf

    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any() || session('error'))
    <div class="alert alert-danger">Terjadi kesalahan, silakan coba lagi.</div>
    @endif

    <div class="container-xl py-5" style="padding-bottom: 110px !important;">
        <div class="row g-4">
            <div class="col-lg-5 col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="text-center rounded bg-light p-4 mb-3"
                        style="min-height: 380px; display: flex; align-items: center; justify-content: center;">
                        @if ($book->cover)
                            <img src="{{ asset('storage/' . $book->cover) }}" alt="{{ $book->title }}"
                                class="img-fluid rounded shadow-sm" style="max-height: 350px; object-fit: contain;">
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-7 col-md-6">
                <h1 class="h2 fw-bold text-dark mb-0">{{ $book->title }}</h1>

                <div class="mb-4">
                    <label class="form-label fw-bold text-muted small text-uppercase mb-2">Opsi Pembelian</label>
                    <div class="row g-2">
                        @foreach ($book->subscriptionPackageBooks as $subscriptionPackageBook)
                            <div class="col-auto">
                                <label class="form-selectgroup-item">
                                    <input type="radio" name="purchase_option"
                                        value="{{ $subscriptionPackageBook->subscription_package_id }}"
                                        class="form-selectgroup-input">
                                    <span class="form-selectgroup-label d-flex flex-column align-items-start p-3 border rounded bg-light"
                                        style="min-width: 150px;">
                                        <span class="text-muted small">
                                            {{ $subscriptionPackageBook->subscriptionPackage->name_package }}
                                        </span>
                                        <span class="h3 fw-bold text-dark mb-0 mt-2">
                                            Rp{{ number_format($subscriptionPackageBook->subscriptionPackage->price, 0, ',', '.') }}
                                        </span>
                                    </span>
                                </label>
                            </div>
                        @endforeach

                        <div class="col-auto">
                            <label class="form-selectgroup-item">
                                <input type="radio" name="purchase_option" value="none" class="form-selectgroup-input" checked>
                                <span class="form-selectgroup-label d-flex flex-column align-items-start p-3 border rounded bg-light"
                                    style="min-width: 150px;">
                                    <span class="text-muted small">Satuan</span>
                                    <span class="h3 fw-bold text-dark mb-0 mt-2">
                                        Rp{{ number_format($book->price, 0, ',', '.') }}
                                    </span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="card bg-light border-0 rounded-3 mb-4">
                    <div class="card-body p-3">
                        <h5 class="mb-1 fw-bold">Konten ini dapat dibaca melalui aplikasi Gramedia Digital</h5>
                        <p class="text-muted small mb-3">
                            Download aplikasi Gramedia Digital yang tersedia di seluruh perangkat iOS dan Android
                        </p>
                        <div class="d-flex gap-2">
                            <a href="#" class="btn btn-dark btn-sm">
                                <i class="fa-brands fa-google-play me-1"></i> Google Play
                            </a>
                            <a href="#" class="btn btn-dark btn-sm">
                                <i class="fa-brands fa-apple me-1"></i> App Store
                            </a>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                @if ($book->description)
                    <div class="mb-4">
                        <h3 class="h4 fw-bold mb-2">Deskripsi</h3>
                        <div class="text-muted description-clamped" id="bookDescription">
                            {!! $book->description !!}
                        </div>
                        <button type="button" class="btn btn-link p-0 small fw-bold mt-2" id="btnReadMore">
                            <span id="readMoreText">Baca Selengkapnya</span>
                            <i class="fa-solid fa-chevron-down ms-1" id="readMoreIcon"></i>
                        </button>
                    </div>
                @endif

                <div class="mb-4">
                    <h3 class="h4 fw-bold mb-3">Informasi Buku</h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex mb-2">
                                <span class="text-muted style-width-label">Bahasa</span>
                                <span class="fw-semibold">: {{ $book->language }}</span>
                            </div>
                            <div class="d-flex mb-2">
                                <span class="text-muted style-width-label">Penerbit</span>
                                <span class="fw-semibold">: {{ $book->publisher }}</span>
                            </div>
                            <div class="d-flex mb-2">
                                <span class="text-muted style-width-label">Penulis</span>
                                <span class="fw-semibold">: {{ $book->writer }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex mb-2">
                                <span class="text-muted style-width-label">Tanggal Rilis</span>
                                <span class="fw-semibold">: {{ $book->release_date?->format('d F Y') }}</span>
                            </div>
                            <div class="d-flex mb-2">
                                <span class="text-muted style-width-label">Halaman</span>
                                <span class="fw-semibold">: {{ $book->page_of_book }} Halaman</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="fixed-bottom bg-white border-top shadow-lg py-2" style="z-index: 1020;">
        <div class="container-xl d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                @if ($book->cover)
                    <img src="{{ asset('storage/' . $book->cover) }}" alt="{{ $book->title }}"
                        class="rounded border" style="width: 40px; height: 50px; object-fit: cover;">
                @endif
                <div>
                    <h4 class="mb-0 text-truncate fw-bold" style="max-width: 400px;">{{ $book->title }}</h4>
                    <span class="fw-bold text-dark">Rp{{ number_format($book->price, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <input type="hidden" name="book_id" value="{{ $book->id }}">
                <button type="submit" name="action" value="add_to_cart" class="btn btn-outline-primary px-4 py-2 fw-medium">Tambah ke Keranjang</button>
                <button type="submit" name="action" value="buy_now" class="btn btn-primary px-4 py-2 fw-medium">Beli Sekarang</button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
    <script>
        const description = document.getElementById('bookDescription');
        const readMoreButton = document.getElementById('btnReadMore');
        const readMoreText = document.getElementById('readMoreText');
        const readMoreIcon = document.getElementById('readMoreIcon');

        if (description && readMoreButton && readMoreText && readMoreIcon) {
            readMoreButton.addEventListener('click', () => {
                const isClamped = description.classList.toggle('description-clamped');
                readMoreText.textContent = isClamped ? 'Baca Selengkapnya' : 'Tutup';
                readMoreIcon.classList.toggle('fa-chevron-down', isClamped);
                readMoreIcon.classList.toggle('fa-chevron-up', !isClamped);
            });
        }
    </script>
@endpush
