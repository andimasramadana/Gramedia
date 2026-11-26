<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $fillable = [
        'cover',
        'title',
        'description',
        'price',
        'language',
        'publisher',
        'writer',
        'release_date',
        'page_of_book',
        'book_category_id',
    ];

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BookCategory::class, 'book_category_id');
    }

    public function bookCategory(): BelongsTo
    {
        return $this->category();
    }

    public function subscriptionPackages(): BelongsToMany
    {
        return $this->belongsToMany(SubscriptionPackage::class, 'subscription_package_books')
            ->withTimestamps();
    }

    public function subscriptionPackageBooks(): HasMany
    {
        return $this->hasMany(SubscriptionPackageBook::class);
    }
}
