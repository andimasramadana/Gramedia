@extends('layouts.app')

@section('content')
{{-- Banner Header Paket Langganan — Full Width (di luar container) --}}
<div style="width: 100%; overflow: hidden; line-height: 0;">
    <img src="{{ asset('images/fiction-package.jpg') }}"
         alt="{{ $subscriptionPackage->name_package }}"
         style="width: 100%; height: 320px; object-fit: cover; object-position: center; display: block;">
</div>

<div class="container-xl my-4 py-2">
    <nav aria-label="breadcrumb">
    </nav>

    {{-- Bar Langganan Sekarang --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-3 mb-4">
        <div>
            <h1 class="h2 fw-bold text-dark mb-0">{{ $subscriptionPackage->name_package }}</h1>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="text-dark fw-bold" style="font-size: 1.35rem;">
                    Rp{{ number_format($subscriptionPackage->price, 0, ',', '.') }}
                </span>
                @if($subscriptionPackage->price > 0)
                    @php
                        $originalPrice = (int) round($subscriptionPackage->price / 0.75);
                    @endphp
                    <span class="badge" style="background-color: #fee2e2; color: #dc2626; font-size: 0.75rem; font-weight: 600; padding: 4px 8px; border-radius: 4px;">25%</span>
                    <span class="text-secondary text-decoration-line-through small" style="color: #9ca3af !important;">
                        Rp{{ number_format($originalPrice, 0, ',', '.') }}
                    </span>
                @endif
            </div>
<a href="{{ route('subscription-packages.payment', $subscriptionPackage->id) }}" class="btn text-white" style="background-color: #1e40af; border-color: #1e40af;">
                Langganan Sekarang
                </a>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <p class="text-secondary mb-0">Buku pilihan dalam paket langganan ini.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <form action="{{ route('subscription-packages.show', $subscriptionPackage) }}" method="GET" class="d-flex align-items-center gap-2">
                <div class="input-icon">
                    <input type="text" name="search" value="{{ $search }}" class="form-control form-control-rounded" placeholder="Cari judul atau penulis" style="min-width: 240px;">
                    <button type="submit" class="input-icon-addon border-0 bg-transparent text-secondary p-0" title="Cari">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
            </form>

            @if($search !== '')
                <a href="{{ route('subscription-packages.show', $subscriptionPackage) }}" class="btn btn-outline-danger btn-icon" title="Hapus pencarian">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </div>
    </div>

    <div class="row g-4">
        @forelse($books as $book)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card h-100 shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-body p-3 d-flex flex-column">
                        <div class="text-center mb-3 bg-light rounded py-3">
                            <img src="{{ $book->cover ? asset('storage/'.$book->cover) : asset('images/no-cover.png') }}"
                                 alt="{{ $book->title }}"
                                 class="img-fluid"
                                 style="height: 180px; object-fit: contain;">
                        </div>
                        <div class="d-flex gap-2 mb-2">
                            <span class="badge bg-secondary-subtle text-secondary">Digital</span>
                            <span class="badge bg-primary-subtle text-primary">{{ $book->bookCategory?->name ?? 'Buku Paket' }}</span>
                        </div>
                        <div class="text-secondary small mb-1 text-truncate" title="{{ $book->writer }}">
                            {{ $book->writer ?? '-' }}
                        </div>
                        <h2 class="card-title mb-2 text-dark" style="font-size: 1rem; font-weight: 600; min-height: 2.5rem; line-height: 1.3;">
                            {{ $book->title }}
                        </h2>
                        <div class="mt-auto pt-2">
                            @if($book->price == 0)
                                <span class="text-success fw-bold fs-4">Gratis</span>
                            @else
                                <span class="text-primary fw-bold fs-4">Rp {{ number_format($book->price, 0, ',', '.') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 py-5 text-center">
                <div class="text-muted">
                    <i class="fa-solid fa-book-open fs-1 mb-3 d-block opacity-50"></i>
                    <h2 class="h4 text-secondary">Tidak ada buku yang ditemukan</h2>
                    @if($search !== '')
                        <p class="mb-3">Tidak ada buku dalam paket ini yang cocok dengan pencarian "{{ $search }}".</p>
                        <a href="{{ route('subscription-packages.show', $subscriptionPackage) }}" class="btn btn-outline-primary">Lihat Semua Buku Paket</a>
                    @else
                        <p class="mb-0">Belum ada buku yang ditambahkan ke paket {{ $subscriptionPackage->name_package }}.</p>
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    @if($books->hasPages())
        <div class="d-flex justify-content-center mt-5">
            {{ $books->links() }}
        </div>
    @endif
</div>
@endsection