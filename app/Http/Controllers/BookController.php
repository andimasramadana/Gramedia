<?php

namespace App\Http\Controllers;

use App\Exports\BookExport;
use App\Models\Book;
use App\Models\BookCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $books = Book::with('bookCategory');

            return DataTables::of($books)
                ->addIndexColumn()

                ->editColumn('cover', function ($row) {
                    if ($row->cover) {
                        return '<img src="'.
                            asset('storage/'.$row->cover).
                            '" alt="'.e($row->title).
                            '" class="img-thumbnail" style="max-height: 60px;">';
                    }

                    return '<span class="badge bg-secondary">No Cover</span>';
                })

                ->editColumn('price', function ($row) {
                    return 'Rp '.number_format(
                        $row->price,
                        0,
                        ',',
                        '.'
                    );
                })

                ->addColumn('category_name', function ($row) {
                    return $row->bookCategory?->name ?? '-';
                })

                ->addColumn('action', function ($row) {
                    $btnDetail = '
                        <button type="button"
                            class="btn btn-info btn-sm btn-detail"
                            data-cover="'.
                            e($row->cover
                                ? asset('storage/'.$row->cover)
                                : '').'"
                            data-title="'.e($row->title).'"
                            data-category="'.
                            e($row->bookCategory?->name ?? '-').'"
                            data-price="Rp '.
                            number_format($row->price, 0, ',', '.').'"
                            data-writer="'.e($row->writer ?? '-').'"
                            data-publisher="'.e($row->publisher ?? '-').'"
                            data-language="'.e($row->language ?? '-').'"
                            data-release="'.
                            e($row->release_date
                                ? date('d M Y', strtotime($row->release_date))
                                : '-').'"
                            data-pages="'.e($row->page_of_book ?? '-').'"
                            data-description="'.e($row->description ?? '').'">
                            <i class="bi bi-eye"></i> Detail
                        </button>';

                    $btnEdit = '
                        <a href="'.
                        route('admin.books.edit', $row->id).'"
                            class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil-square"></i> Edit
                        </a>';

                    $btnDelete = '
                        <form action="'.
                        route('admin.books.destroy', $row->id).'"
                            method="POST"
                            style="display:inline-block"
                            onsubmit="return confirm(\'Apakah Anda yakin ingin menghapus buku ini?\')">
                            '.csrf_field().'
                            '.method_field('DELETE').'
                            <button type="submit"
                                class="btn btn-danger btn-sm">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                        </form>';

                    return $btnDetail.' '.$btnEdit.' '.$btnDelete;
                })

                ->rawColumns(['cover', 'action'])
                ->make(true);
        }

        return view('admin.books.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $bookCategories = BookCategory::all();

        return view(
            'admin.books.create',
            compact('bookCategories')
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_category_id' => [
                'required',
                'exists:book_categories,id',
            ],
            'cover' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'title' => ['required', 'string', 'max:255'],
            'writer' => ['required', 'string', 'max:255'],
            'publisher' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'language' => ['required', 'string', 'max:100'],
            'release_date' => ['required', 'date'],
            'page_of_book' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['cover'] = $request
            ->file('cover')
            ->store('covers', 'public');

        Book::create($validated);

        return redirect()
            ->route('admin.books.index')
            ->with('success', 'Buku berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book): View
    {
        $book->load('subscriptionPackageBooks.subscriptionPackage');

        return view('user.books.show', compact('book'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book)
    {
        $bookCategories = BookCategory::all();

        return view(
            'admin.books.edit',
            compact('book', 'bookCategories')
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'book_category_id' => [
                'required',
                'exists:book_categories,id',
            ],
            'cover' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'title' => ['required', 'string', 'max:255'],
            'writer' => ['required', 'string', 'max:255'],
            'publisher' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'language' => ['required', 'string', 'max:100'],
            'release_date' => ['required', 'date'],
            'page_of_book' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('cover')) {
            if ($book->cover) {
                Storage::disk('public')->delete($book->cover);
            }

            $validated['cover'] = $request
                ->file('cover')
                ->store('covers', 'public');
        } else {
            unset($validated['cover']);
        }

        $book->update($validated);

        return redirect()
            ->route('admin.books.index')
            ->with('success', 'Buku berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        if ($book->cover) {
            Storage::disk('public')->delete($book->cover);
        }

        $book->delete();

        return redirect()
            ->route('admin.books.index')
            ->with('success', 'Buku berhasil dihapus.');
    }

    public function export()
    {
        return Excel::download(new BookExport, 'books.xlsx');
    }
}
