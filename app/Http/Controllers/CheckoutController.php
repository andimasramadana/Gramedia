<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Checkout;
use App\Services\Midtrans\CreateSnapTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'selected_carts' => ['required', 'array', 'min:1'],
            'selected_carts.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('carts', 'id')->where('user_id', Auth::id()),
            ],
        ]);

        $selectedCarts = Cart::query()
            ->where('user_id', Auth::id())
            ->whereIn('id', $validated['selected_carts'])
            ->with(['book', 'subscriptionPackage'])
            ->get();

        if ($selectedCarts->isEmpty()) {
            return redirect()->back()->with('error', 'Buku tidak ada dikeranjang anda!');
        }

        $prices = $selectedCarts->mapWithKeys(fn (Cart $cart): array => [
            $cart->id => $cart->subscription_package_id === null
                ? $cart->book->price
                : $cart->subscriptionPackage->price,
        ]);
        $totalPrice = $prices->sum();

        $checkout = DB::transaction(function () use ($selectedCarts, $prices, $totalPrice): Checkout {
            $checkout = Checkout::create([
                'user_id' => Auth::id(),
                'total_book_price' => $totalPrice,
                'total_price' => $totalPrice,
                'total_book' => $selectedCarts->count(),
                'status' => 'pending',
                'order_code' => 'ORD-'.time(),
            ]);

            foreach ($selectedCarts as $item) {
                $checkout->checkoutBooks()->create([
                    'book_id' => $item->book_id,
                    'subscription_package_id' => $item->subscription_package_id,
                    'price' => $prices[$item->id],
                    'total_book' => 1,
                ]);
            }

            Cart::query()->whereIn('id', $selectedCarts->modelKeys())->delete();

            return $checkout;
        });

        return redirect()->route('checkout.show', $checkout->id);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $checkout = Checkout::findOrFail($id);
        $snapToken = $checkout->snap_token;
        if (is_null($snapToken)) {
            $midtrans = new CreateSnapTokenService($checkout);
            $snapToken = $midtrans->getSnapToken();

            $checkout->snap_token = $snapToken;
            $checkout->save();
        }

        return view('user.checkout.show', compact('checkout', 'snapToken'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Checkout $checkout)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Checkout $checkout)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Checkout $checkout)
    {
        //
    }
}
