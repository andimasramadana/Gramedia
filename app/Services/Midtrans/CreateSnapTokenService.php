<?php

namespace App\Services\Midtrans;

use App\Models\Checkout;
use Midtrans\Snap;

class CreateSnapTokenService extends Midtrans
{
    public function __construct(protected Checkout $checkout)
    {
        parent::__construct();
    }

    public function getSnapToken(): string
    {
        $this->checkout->loadMissing(['checkoutBooks.book', 'user']);
        $params = [
            'transaction_details' => [
                'order_id' => $this->checkout->order_code,
                'gross_amount' => $this->checkout->total_price,
            ],
            // looping data checkoutbooks, untuk data barang yg dibeli
            'item_details' => $this->checkout->checkoutBooks->map(fn ($row) => [
                'id' => $row->id,
                'price' => $row->price,
                'quantity' => 1,
                'name' => $row->book->title, // nama barang diambil dari tble books
            ]
            ),
            'customer_details' => [
                'first_name' => $this->checkout->user->name,
                'email' => $this->checkout->user->email,
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        return $snapToken;
    }
}
