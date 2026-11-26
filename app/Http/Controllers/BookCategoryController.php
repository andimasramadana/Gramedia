<?php

namespace App\Http\Controllers;

use App\Exports\BookCategoriesExport;
use App\Models\BookCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Yajra\DataTables\Facades\DataTables;

class BookCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = BookCategory::query()->select(['id', 'name']);

        if ($request->ajax()) {
            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('action', function (BookCategory $bookCategory): string {
                    return '<div class="d-flex gap-2">'
                        .'<a href="'.e(route('admin.book-categories.edit', $bookCategory)).'" class="btn btn-sm btn-warning">Edit</a>'
                        .'<form action="'.e(route('admin.book-categories.destroy', $bookCategory)).'" method="POST" onsubmit="return confirm(&quot;Menghapus kategori ini juga akan menghapus buku terkait. Lanjutkan?&quot;)">'
                        .csrf_field()
                        .method_field('DELETE')
                        .'<button type="submit" class="btn btn-sm btn-danger">Hapus</button>'
                        .'</form>'
                        .'</div>';
                })
                ->rawColumns(['action'])
                ->toJson();
        }

        return view('admin.book-categories.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.book-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:book_categories,name'],
        ]);

        BookCategory::create($validatedData);

        return redirect()->route('admin.book-categories.index')
            ->with('success', 'Kategori buku berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BookCategory $bookCategory): View
    {
        return view('admin.book-categories.edit', compact('bookCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BookCategory $bookCategory): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('book_categories', 'name')->ignore($bookCategory),
            ],
        ]);

        $bookCategory->update($validatedData);

        return redirect()->route('admin.book-categories.index')
            ->with('success', 'Kategori buku berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BookCategory $bookCategory): RedirectResponse
    {
        $coverPaths = $bookCategory->books()->pluck('cover');
        $bookCategory->delete();
        $publicStorage = Storage::disk('public');

        foreach ($coverPaths as $coverPath) {
            if ($publicStorage->exists($coverPath) && ! $publicStorage->delete($coverPath)) {
                throw new RuntimeException('Sampul buku pada kategori ini gagal dihapus.');
            }
        }

        return redirect()->route('admin.book-categories.index')
            ->with('success', 'Kategori buku berhasil dihapus.');
    }

    public function export()
    {
        return Excel::download(new BookCategoriesExport, 'book_categories.xlsx');
    }
}
