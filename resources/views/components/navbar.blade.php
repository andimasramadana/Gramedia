<header class="navbar navbar-expand-md d-print-none">
    <div class="container-xl">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- BEGIN NAVBAR LOGO -->
        <a href="{{ route('home') }}" aria-label="Gramedia Digital" class="navbar-brand me-3 d-flex align-items-center text-decoration-none">
            <img src="{{ asset('images/gramedia-digital-logo.png') }}"
                 alt="Gramedia Digital"
                 style="height: 40px; width: auto; object-fit: contain;">
        </a>
        <!-- END NAVBAR LOGO -->
        <div class="navbar-nav mx-auto w-50 d-flex flex-row align-items-center {{ Auth::check() && Auth::user()->role == 'admin' ? 'admin-navbar-nav' : '' }}" id="navbar-menu">
            @if (Auth::check() && Auth::user()->role == 'admin')
                <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link">Dashboard</a>
                </li>
                <li class="nav-item {{ request()->routeIs('admin.book-categories.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.book-categories.index') }}" class="nav-link">Kategori Buku</a>
                </li>
                <li class="nav-item {{ request()->routeIs('admin.subscription-packages.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.subscription-packages.index') }}" class="nav-link">Paket Langganan</a>
                </li>
                <li class="nav-item {{ request()->routeIs('admin.books.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.books.index') }}" class="nav-link">Buku</a>
                </li>
            @else
                {{--- Kategori ---}}
                @php
                    $activeCategory = ($navbarCategories ?? collect())->firstWhere('id', request('category'));
                @endphp
                <div class="nav-item dropdown me-3 flex-shrink-0">
                    <button class="btn dropdown-toggle" type="button" id="dropdownCategoriesNavbar" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        {{ $activeCategory ? $activeCategory->name : 'Kategori' }}
                    </button>
                    <div class="dropdown-menu dropdown-menu-card shadow-sm" aria-labelledby="dropdownCategoriesNavbar" style="min-width: 600px; max-width: 90vw;">
                        <div class="p-3">
                            <div class="row g-2">
                                @forelse    ($navbarCategories ?? [] as $category)
                                <div class="col-3">
                                    <a href="{{ route('categories.books', $category) }}" class="text-decoration-none">
                                        <div class="card py-2">
                                            <div class="card-body p-2 text-center text-dark text-truncate" title="{{ $category->name }}">
                                                {{ $category->name }}
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                @empty
                                <div class="col-12">
                                    <p class="text-center text-secondary mb-0 py-2">Belum ada kategori</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                {{--- SEARCH ---}}
                <form action="{{ route('search') }}" method="GET" class="flex-grow-1 my-auto">
                    @if(request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    <div class="input-icon w-100 py-2">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-rounded pe-5" placeholder="Cari Produk, Judul Buku, Penulis" />
                        <button type="submit" class="input-icon-addon border-0 bg-transparent text-secondary p-0 cursor-pointer" title="Cari" style="z-index: 5;">
                            <!-- Download SVG icon from http://tabler.io/icons/icon/search -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false" class="icon">
                                <path d="M3 10a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                                <path d="M21 21l-6 -6" />
                            </svg>
                        </button>
                    </div>
                </form>
                {{--- Keranjang ---}}
                <a href="{{ route('carts') }}" class="ms-3 me-2 flex-shrink-0 text-decoration-none" aria-label="Keranjang">
                    <i class="fa-solid fa-cart-arrow-down fs-3 txt-dark"></i>
                </a>
            @endif
        </div>
        <div class="flex-row order-md-last ms-auto"></div>

        {{-- Auth::check() cek uda ada data login atau belum --}}
        @if(Auth::check())
            @php
                $nameParts = explode(' ', trim(Auth::user()->name));
                $initials = count($nameParts) >= 2
                    ? strtoupper(substr($nameParts[0], 0, 1).substr($nameParts[1], 0, 1))
                    : strtoupper(substr(Auth::user()->name, 0, 2));
            @endphp
            <a href="{{ route('profile') }}" class="text-decoration-none me-3" title="Profil">
                <span class="avatar avatar-sm rounded-circle fw-bold" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; background-color: #e2e8f0; color: #475569; font-size: 13px;">
                    {{ $initials }}
                </span>
            </a>
            <a href="{{ route('logout') }}" class="btn btn-danger">Logout</a>
        @else

            <a href="{{ route('login') }}" class="btn btn-primary">Masuk</a>
            <a href="{{ route('register') }}" class="btn btn-light">Daftar</a>
        @endif
    </div>
</header>