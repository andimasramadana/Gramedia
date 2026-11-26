@extends('layouts.app')
@push('styles')
<style>
    /* ===== GRAMEDIA-STYLE BANNER CAROUSEL ===== */
    .banner-section {
        padding: 16px 24px 0 24px;
    }
    .banner-outer {
        position: relative;
    }
    .banner-carousel-container {
        border-radius: 16px;
        overflow: hidden;
    }
    .banner-carousel-container .carousel-item img {
        width: 100%;
        height: 320px;
        object-fit: cover;
        object-position: center;
        display: block;
    }
    @media (max-width: 768px) {
        .banner-carousel-container .carousel-item img {
            height: 180px;
        }
        .banner-section {
            padding: 12px 12px 0 12px;
        }
    }
    /* Tombol panah kiri/kanan — overlay di tepi banner */
    .banner-nav-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.85);
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        color: #334155;
        z-index: 5;
    }
    .banner-nav-btn:hover {
        background: #ffffff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.22);
    }
    .banner-nav-prev {
        left: 10px;
    }
    .banner-nav-next {
        right: 10px;
    }
    /* Dots di bawah carousel */
    .banner-dots {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
        margin-top: 14px;
        padding-bottom: 4px;
    }
    .banner-dots .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #cbd5e1;
        border: none;
        padding: 0;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .banner-dots .dot.active {
        background: #0066cc;
        width: 24px;
        border-radius: 6px;
    }
    /* Sembunyikan kontrol bawaan Bootstrap carousel */
    #carousel-banner .carousel-indicators,
    #carousel-banner .carousel-control-prev,
    #carousel-banner .carousel-control-next {
        display: none !important;
    }

    /* ===== SLICK SLIDER (Buku Terbaru) ===== */
    .book-card-img {
        height: 180px;
        object-fit: contain;
    }
    .slick-dots {
        display: flex !important;
        justify-content: center;
        list-style: none;
        padding: 0;
        margin-top: 16px;
        gap: 8px;
    }
    .slick-dots li button {
        font-size: 0;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #cbd5e1;
        border: none;
        padding: 0;
        cursor: pointer;
        transition: all 0.3s;
    }
    .slick-dots li.slick-active button {
        background: #066fd1;
        width: 24px;
        border-radius: 10px;
    }
</style>
@endpush

@section('content')
@if(session('success'))
<div class="container-xl mt-3">
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
@endif
@if(session('error'))
<div class="container-xl mt-3">
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
@endif

{{-- Carousel Banner (Gramedia Digital style) --}}
<div class="banner-section">
    <div class="banner-outer">
        <div id="carousel-banner" class="banner-carousel-container carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="{{ asset('images/1.png') }}" alt="Banner 1">
                </div>
                <div class="carousel-item">
                    <img src="{{ asset('images/2.png') }}" alt="Banner 2">
                </div>
                <div class="carousel-item">
                    <img src="{{ asset('images/3.png') }}" alt="Banner 3">
                </div>
                <div class="carousel-item">
                    <img src="{{ asset('images/4.png') }}" alt="Banner 4">
                </div>
                <div class="carousel-item">
                    <img src="{{ asset('images/5.png') }}" alt="Banner 5">
                </div>
                <div class="carousel-item">
                    <img src="{{ asset('images/6.png') }}" alt="Banner 6">
                </div>
            </div>
        </div>

        {{-- Tombol Prev --}}
        <button class="banner-nav-btn banner-nav-prev" type="button" data-bs-target="#carousel-banner" data-bs-slide="prev" aria-label="Previous">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
            </svg>
        </button>

        {{-- Tombol Next --}}
        <button class="banner-nav-btn banner-nav-next" type="button" data-bs-target="#carousel-banner" data-bs-slide="next" aria-label="Next">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
            </svg>
        </button>
    </div>

    {{-- Dots indicator --}}
    <div class="banner-dots" id="banner-dots">
        <button class="dot active" data-index="0" aria-label="Slide 1"></button>
        <button class="dot" data-index="1" aria-label="Slide 2"></button>
        <button class="dot" data-index="2" aria-label="Slide 3"></button>
        <button class="dot" data-index="3" aria-label="Slide 4"></button>
        <button class="dot" data-index="4" aria-label="Slide 5"></button>
        <button class="dot" data-index="5" aria-label="Slide 6"></button>
    </div>
</div>

{{-- Paket Langganan --}}
{{-- Paket Langganan --}}
<div class="mt-4 p-5">
    <div class="d-flex align-items-center mb-4" style="gap: 18px; padding-left: 4px;">
        <span style="display: inline-flex; align-items: center; justify-content: center; width: 54px; height: 42px; flex: 0 0 54px; border-radius: 12px; background-color: #fff4d6; color: #ffcf40;">
            <i class="fa-solid fa-crown" style="font-size: 20px;" aria-hidden="true"></i>
        </span>
        <h2 class="mb-0" style="font-size: 29px; font-weight: 600; line-height: 1.2; color: #111;">Paket Langganan</h2>
    </div>
    <div class="row g-4">
        @forelse($subscriptionPackages ?? $packages ?? [] as $package)
        @php
            $cardColor = !empty($package->color) ? $package->color : '#a49dd0';
        @endphp
        <div class="col-md-4">
            <a href="{{ route('subscription-packages.show', $package->id ?? $package) }}" class="text-decoration-none">
                <div class="card h-100" style="background: linear-gradient(135deg, #ffffff 0%, {{ $cardColor }} 100%);">
                    <div class="card-body row">
                        <div class="col-4"></div>
                        <div class="col-6 text-center text-dark">
                            <h2 style="font-weight: bold">{{ strtoupper($package->name_package) }}</h2>
                            <p class="text-secondary" style="font-weight: bold; margin: 0 !important">Package</p>
                            @if(!empty($package->description))
                                <p class="small mb-2">{{ $package->description }}</p>
                            @endif
                            <div>
                                Rp <span style="font-size: 2rem; font-weight: bold;" class="text-warning">{{ number_format($package->price ?? 0, 0, ',', '.') }}</span><br>
                                <span class="text-secondary" style="font-weight: bold; margin: 0 !important">/30 days</span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @empty
        <p class="text-center">Tidak ada paket langganan.</p>
        @endforelse
    </div>
</div>

{{-- Buku Terbaru --}}
<div class="mt-4">
    <div class="d-flex align-items-center justify-content-between mb-4 px-5">
        <div class="d-flex align-items-center gap-2">
            <h2 class="mt-3 text-dark mb-0" style="font-weight: bold;">
                @if(!empty($search))
                    Hasil Pencarian: "{{ $search }}"
                    @if(isset($selectedCategory))
                        <span class="text-muted fs-4">di Kategori {{ $selectedCategory->name }}</span>
                    @endif
                @elseif(isset($selectedCategory))
                    Kategori: {{ $selectedCategory->name }}
                @else
                    Buku Baru Dirilis
                @endif
            </h2>
        </div>
        <div class="d-flex gap-2">
            @if(!empty($search))
                <a href="{{ route('home', array_filter(['category' => request('category')])) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-xmark me-1"></i> Hapus Pencarian
                </a>
            @endif
            @if(isset($selectedCategory))
                <a href="{{ route('home', array_filter(['search' => request('search')])) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-xmark me-1"></i> Reset Kategori
                </a>
            @endif
        </div>
    </div>
    <div id="wrapper-slider">
        @forelse($latestBooks as $book)
        <div class="px-2">
            <div class="card h-100">
                <div class="card-body p-3">
                    <a href="{{ route('books.show', $book) }}">
                        <img src="{{ $book->cover ? asset('storage/' . $book->cover) : asset('images/no-cover.png') }}"
                            alt="{{ $book->title }}" class="d-block mx-auto book-card-img mb-2">
                    </a>
                    <div class="d-flex gap-2 mt-2">
                        <span class="badge bg-secondary-subtle text-secondary"><i class="fa-solid fa-mobile"></i></span>
                        <span class="badge bg-secondary-subtle text-secondary">3+</span>
                    </div>
                    <div class="mt-2">
                        <span style="font-size: 0.8rem;" class="text-secondary">{{ $book->writer ?? '-' }}</span>
                        <h5 class="mb-1" style="font-size: 0.95rem; font-weight: 600;">
                            <a href="{{ route('books.show', $book) }}" class="text-reset text-decoration-none">{{ $book->title }}</a>
                        </h5>
                        <h6 style="font-size: 1rem; font-weight: bold;" class="text-primary mb-0">{{ number_format($book->price, 0, ',', ',')}}</h6>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <p class="text-center">Tidak Ada Buku Terbaru</p>
        @endforelse
    </div>
</div>

{{-- Buku Gratis --}}
<div class="mt-4">
    <div class="d-flex align-items-center gap-2 mb-4 mx-5">
        <h2 class="mt-3 text-dark" style="font-weight: bold;">Buku Gratis</h2>
    </div>

        <div class="row g-3">
        @forelse($freeBooks as $book)
        <div class="col-md-4">
            <div class="card d-flex flex-column h-100">
                <div class="row row-0 flex-fill align-items-center">
                    <div class="col-md-4 p-2 text-center">
                        <a href="{{ route('books.show', $book) }}">
                            <img src="{{ $book->cover ? asset('storage/' . $book->cover) : asset('images/no-cover.png') }}"
                                alt="{{ $book->title }}" class="img-fluid rounded" style="max-height: 120px; object-fit: contain;">
                        </a>
                    </div>
                    <div class="col-md-8">
                        <div class="card-body p-3 d-flex flex-column h-100">
                            <div class="card-title mb-2">
                                <span class="badge bg-light text-dark border"><i class="fa-solid fa-mobile me-1"></i>{{ $book->bookCategory?->name ?? 'PDF' }}</span>
                            </div>
                            <div class="text-secondary" style="font-size: 0.85rem;">
                                {{ $book->writer ?? '-' }} <br>
                                <strong class="text-dark" style="font-size: 0.95rem;">
                                    <a href="{{ route('books.show', $book) }}" class="text-reset text-decoration-none">{{ $book->title }}</a>
                                </strong>
                            </div>
                            <div class="d-flex align-items-center pt-2 mt-auto">
                                <h5 class="mb-0">
                                    <span class="text-success fw-bold fs-5">Rp 0</span>
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <p class="text-center text-secondary py-3">Tidak Ada Buku Gratis</p>
        </div>
        @endforelse
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // === Banner Carousel dots sync ===
        var bannerEl = document.getElementById('carousel-banner');
        var dots = document.querySelectorAll('#banner-dots .dot');

        if (bannerEl) {
            bannerEl.addEventListener('slid.bs.carousel', function(e) {
                dots.forEach(function(d) { d.classList.remove('active'); });
                dots[e.to].classList.add('active');
            });

            dots.forEach(function(dot) {
                dot.addEventListener('click', function() {
                    var bsCarousel = bootstrap.Carousel.getOrCreateInstance(bannerEl);
                    bsCarousel.to(parseInt(this.dataset.index));
                });
            });
        }

        // === Slick slider untuk Buku Terbaru ===
        const slider = $('#wrapper-slider');
        if (slider.children('.px-2').length > 1) {
            slider.slick({
                dots: true,
                infinite: false,
                speed: 300,
                slidesToShow: 4,
                slidesToScroll: 4,
                responsive: [{
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 3,
                            infinite: true,
                            dots: true
                        }
                    },
                    {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 2
                        }
                    },
                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1
                        }
                    }
                ]
            });
        }
    });
</script>
@endpush
