<?php

use App\Http\Controllers\BookCategoryController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\SubscriptionPackageBookController;
use App\Http\Controllers\SubscriptionPackageController;
use App\Http\Controllers\UserController;
use App\Models\Book;
use App\Models\SubscriptionPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Yajra\DataTables\Facades\DataTables;

// Public Routes
Route::get('/', [UserController::class, 'home'])->name('home');
Route::get('/search', [UserController::class, 'search'])->name('search');
Route::get('/categories/{category}', [UserController::class, 'categoryBooks'])->name('categories.books');
Route::get('/subscription-packages/{subscriptionPackage}', [UserController::class, 'subscriptionPackageBooks'])
    ->name('subscription-packages.show');

Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
// Route Pembayaran Paket Langganan (Sudah diperbaiki ke SubscriptionPackageController)
Route::get('/subscription-packages/{id}/payment', [SubscriptionPackageController::class, 'payment'])
    ->name('subscription-packages.payment');
Route::post('/subscription-packages/{id}/payment', [SubscriptionPackageController::class, 'paymentSend'])
    ->name('subscription-packages.payment.send');

// Route Guest (Hanya diakses sebelum login)
Route::middleware('IsGuest')->group(function () {
    Route::get('/register', function () {
        return view('register');
    })->name('register');

    Route::post('/register', [UserController::class, 'store'])->name('register.store');
    Route::post('/login', [UserController::class, 'authentication'])->name('login.auth');
    Route::get('login', [UserController::class, 'index'])->name('login');
});

// Route Logout
Route::get('logout', [UserController::class, 'logout'])
    ->middleware('IsLoggedIn')
    ->name('logout');

Route::middleware('IsLoggedIn')->group(function () {
    Route::redirect('/cart', '/carts')->name('cart');
    Route::get('/carts', [CartController::class, 'index'])->name('carts');
    Route::post('/carts', [CartController::class, 'store'])->name('carts.store');
    Route::delete('/carts/{cart}', [CartController::class, 'destroy'])->name('carts.destroy');

    Route::post('checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('checkout/{id}/snap', [CheckoutController::class, 'show'])->name('checkout.show');
});

Route::get('/profile', [UserController::class, 'profile'])
    ->middleware('IsLoggedIn')
    ->name('profile');

// Admin Routes
Route::middleware('isAdmin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::get('/book-categories/export', [BookCategoryController::class,
        'export'])->name('book-categories.export');

    Route::get('/books/export', [BookController::class,
        'export'])->name('books.export');

    Route::get('/subscription-packages/export', [SubscriptionPackageController::class,
        'export'])->name('subscription-packages.export');

    Route::resource('book-categories', BookCategoryController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('subscription-packages', SubscriptionPackageController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('books', BookController::class)->except(['show']);
    $booksIndexHandler = function (Request $request, $packageId) {
        $subscriptionPackage = $packageId instanceof SubscriptionPackage
            ? $packageId
            : SubscriptionPackage::findOrFail($packageId);

        if ($request->ajax()) {
            $selectedBookIds = $subscriptionPackage->books()->pluck('books.id')->toArray();

            return DataTables::of(Book::with('bookCategory')->select('books.*'))
                ->addIndexColumn()
                ->addColumn('id', function (Book $book) use ($selectedBookIds) {
                    return [
                        'id' => $book->id,
                        'checkBook' => in_array($book->id, $selectedBookIds),
                    ];
                })
                ->addColumn('cover', function (Book $book) {
                    $src = $book->cover ? asset('storage/'.$book->cover) : asset('images/no-cover.png');

                    return '<img src="'.e($src).'" alt="'.e($book->title).'" width="50" class="img-thumbnail">';
                })
                ->addColumn('category_name', function (Book $book) {
                    return $book->bookCategory?->name ?? '-';
                })
                ->editColumn('price', function (Book $book) {
                    return 'Rp '.number_format($book->price, 0, ',', '.');
                })
                ->rawColumns(['cover'])
                ->toJson();
        }

        return app(SubscriptionPackageBookController::class)->index($subscriptionPackage);
    };

    $booksStoreHandler = function (Request $request, $packageId) {
        $subscriptionPackage = $packageId instanceof SubscriptionPackage
            ? $packageId
            : SubscriptionPackage::findOrFail($packageId);

        $submittedBookIds = $request->input('bookIds', $request->input('book_ids', $request->input('books', [])));
        if (! is_array($submittedBookIds)) {
            $submittedBookIds = [];
        }

        if (empty($submittedBookIds)) {
            $request->merge(['book_ids' => []]);
        } else {
            $existingBookIds = $subscriptionPackage->books()->pluck('books.id')->toArray();
            $mergedBookIds = array_values(array_unique(array_merge($existingBookIds, $submittedBookIds)));
            $request->merge(['book_ids' => $mergedBookIds]);
        }

        return app(SubscriptionPackageBookController::class)->store($request, $subscriptionPackage);
    };

    Route::get('subscription-packages/{packageId}/books', $booksIndexHandler)
        ->name('subscription-package-books');
    Route::get('subscription-packages/{subscriptionPackage}/books', $booksIndexHandler)
        ->name('subscription-packages.books');
    Route::post('subscription-packages/{packageId}/books', $booksStoreHandler)
        ->name('subscription-package-books.store');
    Route::post('subscription-packages/{subscriptionPackage}/books', $booksStoreHandler)
        ->name('subscription-packages.books.store');
    Route::delete('subscription-packages/{subscriptionPackage}/books/{book}', [SubscriptionPackageBookController::class, 'destroy'])
        ->name('subscription-packages.books.destroy');
});
