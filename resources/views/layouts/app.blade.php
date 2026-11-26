<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Books App</title>

    {{-- CDN Tabler --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.5.1/dist/css/tabler.min.css" />

    {{-- CDN Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css" integrity="sha512-QeR2VH+lsBE5LSAe1Q5EnTBbe7XTBubt8dG93Y7gidSgdMCr8nVqKcfKAMyN96SV8KDbZVTDXChatu5G2KQGzg==" crossorigin="anonymous" referrerpolicy="no-referrer">

    {{-- CDN Jquery --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    {{-- CDN SlickJS --}}
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <!-- <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/> -->
    <link rel="stylesheet" href="https://cdn.datatables.net/3.1.3/css/dataTables.dataTables.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    <style>
        .admin-navbar-nav .nav-item > .nav-link {
            color: #212529;
        }

        .admin-navbar-nav .nav-item.active > .nav-link {
            color: #212529;
            background-color: #f2f2f2;
            border-radius: 0.5rem;
        }

        /* html,
        body {
            overflow: hidden 
        }*/
    </style>
    @stack('styles')
</head>
<body>
    <x-navbar/>
    @yield('content')
    <x-footer/>

    {{-- CDN Tabler (sudah include bundle Bootstrap 5 & Popper) --}}
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.5.1/dist/js/tabler.min.js"></script>
    <script>
        // Bridge bootstrap namespace jika ada library/script yang memanggil `bootstrap.*`
        if (typeof window.bootstrap === 'undefined' && typeof window.tabler !== 'undefined') {
            window.bootstrap = window.tabler.bootstrap || window.tabler;
        }
    </script>

    {{-- CDN SlickJS --}}
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

    {{-- CDN DataTables --}}
    <script src="https://cdn.datatables.net/3.1.3/js/dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    @stack('scripts')
</body>
</html>
