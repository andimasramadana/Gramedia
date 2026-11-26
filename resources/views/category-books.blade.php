@extends('layouts.app')

@section('content')
<div class="container-xl my-4 py-2">
    {{-- Breadcrumb & Header Kategori --}}
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h2 mb-1 text-dark" style="font-weight: 700;">
                Buku {{ $category->name }}
            </h1>
            <p class="text-secondary mb-0">Menampilkan daftar buku dalam kategori <strong>{{ $category->name }}</strong></p>
        </div>

        {{-- Search bar & Dropdown sorting --}}
        <div class="d-flex flex-wrap align-items-center gap-2">
            {{-- Search Bar khusus kategori (cari judul / penulis) --}}
            <form action="{{ route('categories.books', $category) }}" method="GET" class="d-flex align-items-center gap-2">
                @if(request('sort'))
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                @endif
                <div class="input-icon">
                    <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control form-control-rounded" placeholder="cari judul atau penulis" style="min-width: 240px;">
                    <button type="submit" class="input-icon-addon border-0 bg-transparent text-secondary p-0" title="Cari">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                            <path d="M3 10a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                            <path d="M21 21l-6 -6" />
                        </svg>
                    </button>
                </div>
            </form>

            {{-- Dropdown Sorting (Harga Termurah, Termahal, Terbaru) --}}
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid  -arrow-down-wide-short me-1"></i>
                    @if($sort === 'price_asc')
                        Harga termurah
                    @elseif($sort === 'price_desc')
                        Harga termahal
                    @else
                        Buku terbaru
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li>
                        <a class="dropdown-item {{ $sort === 'price_asc' ? 'active' : '' }}" 
                           href="{{ route('categories.books', array_filter(['category' => $category->id, 'sort' => 'price_asc', 'search' => $search])) }}">
                            <i class="fa-solid fa-arrow-up-1-9 me-2"></i> Harga termurah
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ $sort === 'price_desc' ? 'active' : '' }}" 
                           href="{{ route('categories.books', array_filter(['category' => $category->id, 'sort' => 'price_desc', 'search' => $search])) }}">
                            <i class="fa-solid fa-arrow-down-9-1 me-2"></i> Harga termahal
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ empty($sort) || $sort === 'newest' ? 'active' : '' }}" 
                           href="{{ route('categories.books', array_filter(['category' => $category->id, 'sort' => 'newest', 'search' => $search])) }}">
                            <i class="fa-solid fa-sparkles me-2"></i> Buku terbaru
                        </a>
                    </li>
                </ul>
            </div>

            @if(!empty($search))
                <a href="{{ route('categories.books', array_filter(['category' => $category->id, 'sort' => $sort])) }}" class="btn btn-outline-danger btn-icon" title="Hapus Pencarian">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </div>
    </div>

    {{-- Daftar Buku --}}
    <div class="row g-4">
        @forelse($books as $book)
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                <div class="card-body p-3 d-flex flex-column">
                    <div class="text-center mb-3 bg-light rounded py-3">
                        <img src="{{ $book->cover ? asset('storage/' . $book->cover) : asset('images/no-cover.png') }}"
                             alt="{{ $book->title }}" 
                             class="img-fluid" 
                             style="height: 180px; object-fit: contain;">
                    </div>
                    <div class="d-flex gap-2 mb-2">
                        <span class="badge bg-secondary-subtle text-secondary"><i class="fa-solid fa-mobile me-1"></i>Digital</span>
                        <span class="badge bg-primary-subtle text-primary">{{ $category->name }}</span>
                    </div>
                    <div class="text-secondary small mb-1 text-truncate" title="{{ $book->writer }}">
                        {{ $book->writer ?? '-' }}
                    </div>
                    <h5 class="card-title mb-2 text-dark" style="font-size: 1rem; font-weight: 600; min-height: 2.5rem; line-height: 1.3;">
                        {{ $book->title }}
                    </h5>
                    <div class="mt-auto pt-2 border-top d-flex align-items-center justify-content-between">
                        <div>
                            @if($book->price == 0)
                                <span class="text-success fw-bold fs-4">Gratis</span>
                            @else
                                <span class="text-primary fw-bold fs-4">Rp {{ number_format($book->price, 0, ',', '.') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 py-5 text-center">
            <div class="text-muted">
                <i class="fa-solid fa-book-open fs-1 mb-3 d-block opacity-50"></i>
                <h4 class="text-secondary">Tidak ada buku yang ditemukan</h4>
                @if(!empty($search))
                    <p class="mb-3">Tidak ada buku di kategori ini yang cocok dengan pencarian "<strong>{{ $search }}</strong>".</p>
                    <a href="{{ route('categories.books', $category) }}" class="btn btn-outline-primary">Lihat Semua Buku Kategori Ini</a>
                @else
                    <p class="mb-0">Belum ada buku yang terdaftar pada kategori {{ $category->name }}.</p>
                @endif
            </div>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($books->hasPages())
    <div class="d-flex justify-content-center mt-5">
        {{ $books->links() }}
    </div>
    @endif
</div>
@endsection
