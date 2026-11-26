@extends('layouts.app')

@section('content')
<div class="container my-5">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible mb-4" role="alert">
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        {{-- Kolom Kiri: Profil Summary --}}
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    {{-- Avatar & Nama Pengguna --}}
                    <div class="d-flex align-items-center mb-3">
                        <span class="avatar rounded-circle me-3 bg-secondary-lt text-dark fw-bold fs-2" style="width: 52px; height: 52px;">
                            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                        </span>
                        <div>
                            <h3 class="mb-0 fw-bold">{{ Auth::user()->name }}</h3>
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- Profil Summary --}}
                    <h4 class="text-primary fw-bold mb-3">Profil Summary</h4>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Koleksi Buku</span>
                        <span class="fw-bold">{{ $checkoutCount }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Langganan</span>
                        <span class="fw-bold">{{ $subscriptionCount }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Transaksi --}}
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    {{-- Header Transaksi & Dropdown Filter --}}
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <h3 class="fw-bold fs-2 mb-0">Transaksi</h3>

                        {{-- Dropdown Filter Jenis Langganan / Transaksi --}}
                        <form method="GET" action="{{ route('profile') }}" id="filterForm">
                            <label class="form-label text-muted small mb-1">Jenis Transaksi</label>
                            <select name="filter" class="form-select form-select-sm" style="min-width: 170px;" onchange="this.form.submit()">
                                <option value="paket" {{ $filter == 'paket' ? 'selected' : '' }}>Paket Langganan</option>
                                <option value="buku" {{ $filter == 'buku' ? 'selected' : '' }}>Buku</option>
                            </select>
                        </form>
                    </div>

                    {{-- Daftar Transaksi Paket Langganan (Default) --}}
                    @if($filter == 'paket')
                        @if($subscriptions->isEmpty())
                            <div class="text-center py-5 text-muted">
                                <i class="fa-solid fa-receipt fs-1 mb-2 d-block text-secondary"></i>
                                Belum ada riwayat pembelian paket langganan.
                            </div>
                        @else
                            <div class="d-flex flex-column gap-3">
                                @foreach($subscriptions as $sub)
                                    <div class="card border p-3 shadow-none mb-0">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="text-muted small">
                                                {{ $sub->created_at ? $sub->created_at->format('d M Y, H:i') : '-' }}
                                            </div>
                                            <span class="badge bg-success-lt text-success fw-bold">Transaksi Sukses</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-end">
                                            <div>
                                                <h4 class="fw-bold mb-2">{{ $sub->subscriptionPackage->name_package ?? 'Paket Langganan' }}</h4>
                                                @if($sub->expired_date)
                                                    <span class="badge bg-danger text-white px-2 py-1">
                                                        Berlaku sd : {{ \Carbon\Carbon::parse($sub->expired_date)->format('d M Y') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-end">
                                                <div class="text-muted small mb-1">Total Pembayaran</div>
                                                <div class="fw-bold fs-3 text-dark">
                                                    Rp {{ number_format($sub->subscriptionPackage->price ?? 0, 0, ',', '.') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @else
                        {{-- Daftar Transaksi Buku --}}
                        @if($checkouts->isEmpty())
                            <div class="text-center py-5 text-muted">
                                <i class="fa-solid fa-book-open fs-1 mb-2 d-block text-secondary"></i>
                                Belum ada riwayat pembelian buku.
                            </div>
                        @else
                            <div class="d-flex flex-column gap-3">
                                @foreach($checkouts as $checkout)
                                    <div class="card border p-3 shadow-none mb-0">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="text-muted small">
                                                {{ $checkout->created_at ? $checkout->created_at->format('d M Y, H:i') : '-' }}
                                            </div>
                                            <span class="badge bg-success-lt text-success fw-bold">Transaksi Sukses</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-end">
                                            <div>
                                                <h4 class="fw-bold mb-2">Pembelian Buku ({{ $checkout->total_book }} Buku)</h4>
                                                <span class="badge bg-secondary-lt text-dark px-2 py-1">
                                                    Metode: {{ $checkout->payment_method }}
                                                </span>
                                            </div>
                                            <div class="text-end">
                                                <div class="text-muted small mb-1">Total Pembayaran</div>
                                                <div class="fw-bold fs-3 text-dark">
                                                    Rp {{ number_format($checkout->total_price ?? 0, 0, ',', '.') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection