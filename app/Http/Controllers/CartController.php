<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\SubscriptionPackageUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $carts = Cart::query()
            ->where('user_id', Auth::id())
            ->with(['book', 'subscriptionPackage'])
            ->get();

        $activePackageIds = SubscriptionPackageUser::query()
            ->where('user_id', Auth::id())
            ->whereDate('expired_date', '>', today())
            ->pluck('subscription_package_id');

        $packagesToPurchase = $carts
            ->whereNotNull('subscription_package_id')
            ->pluck('subscription_package_id')
            ->unique()
            ->diff($activePackageIds)
            ->values();

        $carts->each(function (Cart $cart) use ($packagesToPurchase): void {
            $cart->display_price = $cart->subscriptionPackage?->price ?? $cart->book?->price ?? 0;
            $cart->needs_package = $packagesToPurchase->contains($cart->subscription_package_id);
        });

        $totalPrice = $carts->sum('display_price');

        return view('user.carts.index', compact('carts', 'totalPrice'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $purchaseOptionRules = ['bail', 'required', 'string'];

        if ($request->input('purchase_option') === 'none') {
            $purchaseOptionRules[] = Rule::in(['none']);
        } elseif (is_numeric($request->input('book_id'))) {
            $purchaseOptionRules[] = Rule::exists('subscription_package_books', 'subscription_package_id')
                ->where('book_id', $request->input('book_id'));
        } else {
            $purchaseOptionRules[] = Rule::in([]);
        }

        $validated = $request->validate([
            'book_id' => ['bail', 'required', 'integer', 'exists:books,id'],
            'purchase_option' => $purchaseOptionRules,
            'action' => ['required', Rule::in(['add_to_cart', 'buy_now'])],
        ]);

        if ($validated['action'] !== 'add_to_cart') {
            return back()->with('error', 'Fitur Beli Sekarang belum tersedia.');
        }

        Cart::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'book_id' => $validated['book_id'],
            ],
            [
                'subscription_package_id' => $validated['purchase_option'] === 'none'
                    ? null
                    : $validated['purchase_option'],
            ],
        );

        return redirect()->route('carts')->with('success', 'Buku berhasil ditambahkan ke keranjang.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cart $cart): RedirectResponse
    {
        abort_unless((int) $cart->user_id === (int) Auth::id(), 404);

        $cart->delete();

        return redirect()->route('carts')->with('success', 'Buku berhasil dihapus dari keranjang.');
    }
}
