<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['checkouts_id', 'book_id', 'subscription_package_id', 'price', 'total_book'])]
class CheckoutBook extends Model
{
    public function checkout(): BelongsTo
    {
        return $this->belongsTo(Checkout::class, 'checkouts_id');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
