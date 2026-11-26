@extends('layouts.app')

@section('content')
    <div class="container-xl py-4">
        <h2 class="fw-bold mb-4">Keranjang Belanja</h2>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" id="select-all"
                                @checked($carts->isNotEmpty())>
                            <span class="form-check-label fw-semibold">
                                <span id="cart-count">({{ $carts->count() }})</span> Buku Terpilih
                            </span>
                        </label>
                    </div>
                </div>

                @forelse ($carts as $cart)
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body d-flex align-items-center gap-3">
                            <input class="form-check-input item-checkbox m-0" type="checkbox"
                                value="{{ $cart->id }}" data-price="{{ $cart->display_price }}" checked
                                aria-label="Pilih {{ $cart->book->title ?? '' }}">

                            @if ($cart->book?->cover)
                                <img src="{{ asset('storage/' . $cart->book->cover) }}"
                                    alt="{{ $cart->book->title ?? '' }}" class="rounded"
                                    style="width: 80px; height: 110px; object-fit: cover;">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                    style="width: 80px; height: 110px;">
                                    <span class="text-muted small">Tanpa sampul</span>
                                </div>
                            @endif

                            <div class="flex-grow-1">
                                <div class="text-muted small">{{ $cart->book->writer ?? '' }}</div>
                                <h3 class="h4 fw-semibold mb-1">{{ $cart->book->title ?? '' }}</h3>
                                @if ($cart->subscriptionPackage)
                                    <span class="badge bg-green-lt">{{ $cart->subscriptionPackage->name_package }}</span>
                                @else
                                    <span class="badge bg-azure-lt">Satuan</span>
                                @endif
                            </div>

                            <div class="text-end">
                                <div class="fw-bold mb-2">Rp{{ number_format($cart->display_price, 0, ',', '.') }}</div>
                                <form action="{{ route('carts.destroy', $cart) }}" method="POST"
                                    onsubmit="return confirm('Hapus buku ini dari keranjang?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link text-danger p-0"
                                        aria-label="Hapus {{ $cart->book->title ?? '' }}" title="Hapus">
                                        <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center text-muted py-5">
                            Keranjang belanja Anda masih kosong.
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="h4 fw-bold mb-4">Ringkasan Keranjang</h3>
                        <div class="d-flex justify-content-between text-muted mb-3">
                            <span>Total Belanja (<span id="total-selected-count">{{ $carts->count() }}</span> Buku)</span>
                        </div>

                        <div class="mb-3" id="checkout-summary-items">
                            @foreach ($carts as $cart)
                                <div class="summary-row d-flex justify-content-between small mb-2 {{ $cart->needs_package ? 'text-danger' : 'text-muted' }}"
                                    data-cart-id="{{ $cart->id }}">
                                    <span class="text-truncate pe-3">{{ $cart->book->title ?? '' }}</span>
                                    <span class="fw-medium text-end">
                                        Rp{{ number_format($cart->display_price, 0, ',', '.') }}
                                        @if ($cart->needs_package)
                                            <br><small class="text-danger">Perlu membeli paket {{ $cart->subscriptionPackage->name_package ?? '' }}</small>
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <hr class="my-3">

                        <div class="d-flex justify-content-between fw-semibold mb-4">
                            <span>Subtotal</span>
                            <span id="total-price-text">Rp {{ number_format($totalPrice, 0, ',', '.') }}</span>
                        </div>

                        <form method="POST" action="{{ route('checkout.store') }}" id="form-checkout">
                            @csrf
                            <div id="checkout-input"></div>
                            <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-semibold"
                                id="btn-checkout" @disabled($carts->isEmpty())>
                                Checkout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const checkboxes = Array.from(document.querySelectorAll('.item-checkbox'));
            const selectAll = document.getElementById('select-all');
            const totalPriceText = document.getElementById('total-price-text');
            const totalCountText = document.getElementById('total-selected-count');
            const cartCount = document.getElementById('cart-count');
            const checkoutButton = document.getElementById('btn-checkout');
            const checkoutInputs = document.getElementById('checkout-input');

            const rupiah = (amount) => `Rp ${Number(amount).toLocaleString('id-ID')}`;

            function updateSummary() {
                const checked = checkboxes.filter((checkbox) => checkbox.checked);
                const total = checked.reduce((sum, checkbox) => sum + Number(checkbox.dataset.price), 0);

                totalPriceText.textContent = rupiah(total);
                totalCountText.textContent = checked.length;
                cartCount.textContent = `(${checked.length})`;

                checkboxes.forEach((checkbox) => {
                    const summaryRow = document.querySelector(
                        `.summary-row[data-cart-id="${checkbox.value}"]`,
                    );

                    summaryRow?.classList.toggle('d-none', !checkbox.checked);
                });

                checkoutButton.disabled = checked.length === 0;
                checkoutInputs.replaceChildren();

                checked.forEach((checkbox) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_carts[]';
                    input.value = checkbox.value;
                    checkoutInputs.appendChild(input);
                });
            }

            selectAll.addEventListener('change', () => {
                checkboxes.forEach((checkbox) => {
                    checkbox.checked = selectAll.checked;
                });
                updateSummary();
            });

            checkboxes.forEach((checkbox) => {
                checkbox.addEventListener('change', updateSummary);
            });

            updateSummary();
        });
    </script>
@endpush
