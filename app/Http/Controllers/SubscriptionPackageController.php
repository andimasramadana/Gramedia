<?php

namespace App\Http\Controllers;

use App\Exports\SubscriptionPackageExport;
use App\Models\Book;
use App\Models\SubscriptionPackage;
use App\Models\SubscriptionPackageUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class SubscriptionPackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = SubscriptionPackage::query()
            ->select(['id', 'name_package', 'color', 'price']);

        if ($request->ajax()) {
            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('color_badge', function (SubscriptionPackage $subscriptionPackage): string {
                    if (! $subscriptionPackage->color) {
                        return '<span class="text-muted">-</span>';
                    }

                    return '<div class="d-flex align-items-center gap-2">'
                        .'<span class="d-inline-block rounded-circle border" style="width: 20px; height: 20px; background-color: '.e($subscriptionPackage->color).';"></span>'
                        .'<code>'.e($subscriptionPackage->color).'</code>'
                        .'</div>';
                })
                ->addColumn('action', function (SubscriptionPackage $subscriptionPackage): string {
                    return '<div class="d-flex gap-2">'
                        .'<a href="'.e(route('admin.subscription-packages.edit', $subscriptionPackage)).'" class="btn btn-sm btn-warning">Edit</a>'
                        .'<form action="'.e(route('admin.subscription-packages.destroy', $subscriptionPackage)).'" method="POST" onsubmit="return confirm(&quot;Menghapus paket ini juga menghapus relasi paket ke buku dan pengguna. Lanjutkan?&quot;)">'
                        .csrf_field()
                        .method_field('DELETE')
                        .'<button type="submit" class="btn btn-sm btn-danger">Hapus</button>'
                        .'</form>'
                        .'<a href="'.e(route('admin.subscription-packages.books', $subscriptionPackage)).'#books" class="btn btn-sm btn-success">Buku</a>'
                        .'</div>';
                })
                ->editColumn('price', fn (SubscriptionPackage $subscriptionPackage): string => $subscriptionPackage->price === null
                    ? '-'
                    : 'Rp '.number_format($subscriptionPackage->price, 0, ',', '.'))
                ->rawColumns(['color_badge', 'action'])
                ->toJson();
        }

        return view('admin.subscription-packages.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $books = Book::query()->orderBy('title')->get(['id', 'title', 'writer']);

        return view('admin.subscription-packages.create', compact('books'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'name_package' => ['required', 'string', 'max:255', 'unique:subscription_packages,name_package'],
            'color' => ['nullable', 'string', 'max:50'],
            'price' => ['required', 'integer', 'min:0'],
            'books_present' => ['sometimes', 'boolean'],
            'books' => ['sometimes', 'array'],
            'books.*' => ['integer', 'distinct', 'exists:books,id'],
        ]);

        $bookIds = $validatedData['books'] ?? [];
        unset($validatedData['books'], $validatedData['books_present']);

        DB::transaction(function () use ($validatedData, $bookIds): void {
            $subscriptionPackage = SubscriptionPackage::create($validatedData);
            $subscriptionPackage->books()->sync($bookIds);
        });

        return redirect()->route('admin.subscription-packages.index')
            ->with('success', 'Paket langganan berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubscriptionPackage $subscriptionPackage): View
    {
        $books = Book::query()->orderBy('title')->get(['id', 'title', 'writer']);
        $subscriptionPackage->load('books:id,title,writer');

        return view('admin.subscription-packages.edit', compact('subscriptionPackage', 'books'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubscriptionPackage $subscriptionPackage): RedirectResponse
    {
        $validatedData = $request->validate([
            'name_package' => [
                'required',
                'string',
                'max:255',
                Rule::unique('subscription_packages', 'name_package')->ignore($subscriptionPackage),
            ],
            'color' => ['nullable', 'string', 'max:50'],
            'price' => ['required', 'integer', 'min:0'],
            'books_present' => ['sometimes', 'boolean'],
            'books' => ['sometimes', 'array'],
            'books.*' => ['integer', 'distinct', 'exists:books,id'],
        ]);

        $bookIds = $validatedData['books'] ?? [];
        $hasBookSelection = $request->boolean('books_present');
        unset($validatedData['books'], $validatedData['books_present']);

        DB::transaction(function () use ($subscriptionPackage, $validatedData, $bookIds, $hasBookSelection): void {
            $subscriptionPackage->update($validatedData);

            if ($hasBookSelection) {
                $subscriptionPackage->books()->sync($bookIds);
            }
        });

        return redirect()->route('admin.subscription-packages.index')
            ->with('success', 'Paket langganan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubscriptionPackage $subscriptionPackage): RedirectResponse
    {
        $subscriptionPackage->delete();

        return redirect()->route('admin.subscription-packages.index')
            ->with('success', 'Paket langganan berhasil dihapus.');
    }

    /**
     * Halaman Pembayaran Paket Langganan
     */
    public function payment($id): View
    {
        $subscriptionPackage = SubscriptionPackage::findOrFail($id);

        // Mengarahkan ke file resources/views/user/subscription-package-payment.blade.php
        return view('user.subscription-package-payment', compact('subscriptionPackage'));
    }

    public function paymentSend(Request $request, int|string $id): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu untuk melakukan pembayaran.');
        }

        SubscriptionPackageUser::create([
            'subscription_package_id' => $id,
            'user_id' => Auth::id(),
            'expired_date' => now()->addDays(30)->format('Y-m-d'),
        ]);

        return redirect()->route('home')->with('success', 'Berhasil membeli paket langganan! silahkan cek halaman profile untuk detail transaksi');
    }

    public function export()
    {
        return Excel::download(new SubscriptionPackageExport, 'Subscription.xlsx');
    }
}
