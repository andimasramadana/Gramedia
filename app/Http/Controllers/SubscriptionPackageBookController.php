<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\SubscriptionPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionPackageBookController extends Controller
{
    /**
     * Display a full-page listing of books to assign to the subscription package.
     */
    public function index(SubscriptionPackage $subscriptionPackage): View
    {
        $books = Book::with('bookCategory')->orderBy('title')->get();
        $selectedBookIds = $subscriptionPackage->books()->pluck('books.id')->toArray();

        return view('admin.subscription-package-books.index', compact('subscriptionPackage', 'books', 'selectedBookIds'));
    }

    /**
     * Store newly selected books into a subscription package.
     */
    public function store(Request $request, SubscriptionPackage $subscriptionPackage): RedirectResponse
    {
        $bookIds = $request->input('book_ids', $request->input('books', []));
        if (! is_array($bookIds)) {
            $bookIds = [];
        }

        $validated = validator(['book_ids' => $bookIds], [
            'book_ids' => ['required', 'array', 'min:1'],
            'book_ids.*' => ['integer', 'distinct', 'exists:books,id'],
        ], [
            'book_ids.required' => 'Pilih minimal satu buku untuk ditambahkan.',
            'book_ids.min' => 'Pilih minimal satu buku untuk ditambahkan.',
            'book_ids.*.exists' => 'Buku yang dipilih tidak ditemukan.',
        ])->validate();

        $subscriptionPackage->books()->sync($validated['book_ids']);

        $count = count($validated['book_ids']);

        return back()->with('success', 'berhasil menambahkan buku ke paket langganan.');
    }

    /**
     * Remove a book from a subscription package.
     */
    public function destroy(SubscriptionPackage $subscriptionPackage, Book $book): RedirectResponse
    {
        $subscriptionPackage->books()->detach($book->id);

        return back()->with('success', "Buku '{$book->title}' berhasil dihapus dari paket {$subscriptionPackage->name_package}.");
    }
}
