<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCategory;
use App\Models\Checkout;
use App\Models\SubscriptionPackage;
use App\Models\SubscriptionPackageUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index()
    {
        return view('login');
    }

    public function authentication(Request $request)
    {
        $validatedDate = $request->validate([
            'email' => ['required'],
            'password' => ['required'],
        ]);

        // Auth (authentication) : verifikasi data pengguna
        // Auth::attempt() melakukan 3 hal :
        // 1. cek apakah pw didatabase udah di hashing, kemudian cocokan text pw dari input dan pw hashing
        // 2. cek pasangan email-pw cocok atau engga
        // 3. jika cocok, simpan data pengguna di cookies

        if (Auth::attempt($validatedDate)) {
            $request->session()->regenerate();

            if (Auth::user()->role == 'admin') {
                return redirect()->route('admin.dashboard')->with('success', 'Login successful!');
            } else {
                return redirect()->route('home')->with('success', 'Login successful!');
            }

        } else {
            // jika proses 1-3 gagal, maka dikembalikan ke login
            return redirect()->route('login')->with('error', 'Email dan password tidak sesuai. Silahkan coba lagi!');
        }
    }

    public function store(Request $request)
    {
        // validasi data
        $validatedDate = $request->validate([
            // name_input => [jenis_validasi]
            'name' => ['required', 'min:3'],
            // unique : data gaboleh dupliakt, dari table users field email
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'max:12'],
        ], [
            'name.required' => 'Nama Lengkap harus diisi',
            'name.min' => 'Nama Lengkap harus diisi dengan minimal 3 huruf',
            'email.require' => 'Email harus diisi',
            'email.unique' => 'Email sudah terdaftar, gunakan email lainnya',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password harus diisi minimal 8 karakter',
            'password.max' => 'Password harus diisi maksimal 12 karakter',
        ]);
        // simpan data
        User::create([
            'name' => $validatedDate['name'],
            'email' => $validatedDate['email'],
            // password di hashing dulu, diubah dari text biasa ke karakter acak
            'password' => Hash::make($validatedDate['password']),
        ]);

        // setelah data disimpan, halaman akna diarahkan kemana
        // with('nama', 'pesan'): mengirim pesan nontifikasi satu kali
        return redirect()->route('login')->with('success', 'Berasil membuat akun! Silahkan login');
    }

    public function logout()
    {
        Auth::logout();

        return redirect()->route('home')->with('success', 'Anda sudah logout!');
    }

    public function profile(Request $request): View
    {
        $user = $request->user();
        $subscriptions = SubscriptionPackageUser::with('subscriptionPackage')
            ->where('user_id', $user->id)->latest()->get();
        $checkouts = Checkout::where('user_id', $user->id)->latest()->get();

        return view('user.profile', [
            'filter' => $request->query('filter') === 'buku' ? 'buku' : 'paket',
            'subscriptions' => $subscriptions,
            'checkouts' => $checkouts,
            'subscriptionCount' => $subscriptions->count(),
            'checkoutCount' => $checkouts->sum('total_book'),
        ]);
    }

    public function home(Request $request)
    {
        $categoryId = $request->query('category');
        $selectedCategory = $categoryId ? BookCategory::find($categoryId) : null;

        $latestBooksQuery = Book::orderBy('created_at', 'desc');
        $freeBooksQuery = Book::where('price', 0)->orderBy('created_at', 'desc');

        if ($categoryId) {
            $latestBooksQuery->where('book_category_id', $categoryId);
            $freeBooksQuery->where('book_category_id', $categoryId);
        }

        $latestBooks = $latestBooksQuery->limit(10)->get();
        $freeBooks = $freeBooksQuery->limit(6)->get();
        $subscriptionPackages = SubscriptionPackage::orderBy('created_at', 'desc')->limit(3)->get();

        return view('home', compact('latestBooks', 'freeBooks', 'selectedCategory', 'subscriptionPackages'));
    }

    public function search(Request $request)
    {
        $search = $request->query('search');
        $categoryId = $request->query('category');
        $selectedCategory = $categoryId ? BookCategory::find($categoryId) : null;

        $latestBooksQuery = Book::where('title', 'like', "%{$search}%")
            ->orWhere('writer', 'like', "%{$search}%")
            ->orWhere('publisher', 'like', "%{$search}%")
            ->orderBy('created_at', 'desc');

        $freeBooksQuery = Book::where('price', 0)
            ->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('writer', 'like', "%{$search}%")
                    ->orWhere('publisher', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc');

        if ($categoryId) {
            $latestBooksQuery->where('book_category_id', $categoryId);
            $freeBooksQuery->where('book_category_id', $categoryId);
        }

        $latestBooks = $latestBooksQuery->limit(10)->get();
        $freeBooks = $freeBooksQuery->limit(6)->get();
        $subscriptionPackages = SubscriptionPackage::all();

        return view('home', compact('latestBooks', 'freeBooks', 'selectedCategory', 'subscriptionPackages', 'search'));
    }

    public function categoryBooks(Request $request, BookCategory $category)
    {
        $search = trim((string) $request->query('search'));
        $sort = $request->query('sort', 'newest');

        $booksQuery = Book::where('book_category_id', $category->id)->with('bookCategory');

        if ($search !== '') {
            $booksQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('writer', 'like', "%{$search}%");
            });
        }

        switch ($sort) {
            case 'price_asc':
                $booksQuery->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $booksQuery->orderBy('price', 'desc');
                break;
            case 'newest':
            default:
                $booksQuery->orderBy('created_at', 'desc');
                break;
        }

        $books = $booksQuery->paginate(12)->withQueryString();

        return view('category-books', compact('category', 'books', 'search', 'sort'));
    }

    public function subscriptionPackageBooks(Request $request, SubscriptionPackage $subscriptionPackage): View
    {
        $search = trim((string) $request->query('search'));
        $sort = $request->query('sort', 'newest');

        $booksQuery = $subscriptionPackage->books()->with('bookCategory');

        if ($search !== '') {
            $booksQuery->where(function (Builder $query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('writer', 'like', "%{$search}%");
            });
        }

        switch ($sort) {
            case 'price_asc':
                $booksQuery->orderBy('price');
                break;
            case 'price_desc':
                $booksQuery->orderByDesc('price');
                break;
            case 'newest':
            default:
                $booksQuery->orderByDesc('created_at');
                break;
        }

        $books = $booksQuery->paginate(12)->withQueryString();

        return view('subscription-package-books', compact('subscriptionPackage', 'books', 'search', 'sort'));
    }
}
