<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPackage extends Model
{
    protected $fillable = ['name_package', 'description', 'color', 'price'];

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'subscription_package_books')
            ->withTimestamps();
    }

    public function subscriptionPackageUsers(): HasMany
    {
        return $this->hasMany(SubscriptionPackageUser::class);
    }
}
