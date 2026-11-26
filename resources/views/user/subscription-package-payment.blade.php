@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row">
            {{-- Kolom Scan QR Code --}}
            <div class="col-md-8 mb-4">
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-body text-center p-4">
                        <h3 class="fw-bold">Scan QR Code</h3>
                        <p class="text-secondary">Gunakan aplikasi e-wallet atau m-banking Anda untuk melakukan pembayaran</p>
                        
                        <div class="d-flex justify-content-center my-4">
                            {!! QrCode::size(250)->generate($subscriptionPackage->id); !!}
                        </div>
                    </div>  
                </div>
            </div>

            {{-- Kolom Detail Pembayaran --}}
            <div class="col-md-4">
                <div class="card shadow-sm border-0 p-4" style="border-radius: 12px;">
                    <h3 class="h4 fw-bold mb-3">Detail Pembayaran</h3>
                    
                    @php
                        // Perhitungan harga awal (diskon 20%)
                        $discountAmount = $subscriptionPackage->price * 0.2;
                        $priceBeforeDiscount = $subscriptionPackage->price + $discountAmount;
                    @endphp

                    <div class="d-flex justify-content-between mb-2">
                        <b>Total Item</b>
                        <b>1</b>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <b>Harga</b>
                        <span class="fw-bold">Rp {{ number_format($priceBeforeDiscount, 0, ',', '.') }}</span>
                    </div>  

                    <div class="d-flex justify-content-between mb-2">
                        <b>Diskon</b>
                        <span class="fw-bold text-danger">Rp {{ number_format($discountAmount, 0, ',', '.') }}</span>
                    </div>                     
                    
                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold fs-5"></span>
                        <h3 class="text-primary fw-bold mb-0">Rp {{ number_format($subscriptionPackage->price, 0, ',', '.') }}</h3>
                    </div>

                    <form action="{{ route('subscription-packages.payment.send', $subscriptionPackage->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">
                            Bayar Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection