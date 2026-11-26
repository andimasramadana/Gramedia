<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_profile_counts_books_from_checkout_totals(): void
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $timestamp = now();

        DB::table('checkouts')->insert([
            [
                'user_id' => $user->id,
                'payment_method' => 'QRIS',
                'total_book' => 3,
                'total_book_price' => 45000,
                'total_price' => 45000,
                'status' => 'Done',
                'order_code' => 'PROFILE-TEST-'.$user->id,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'user_id' => $anotherUser->id,
                'payment_method' => 'QRIS',
                'total_book' => 8,
                'total_book_price' => 120000,
                'total_price' => 120000,
                'status' => 'Done',
                'order_code' => 'OTHER-PROFILE-TEST-'.$anotherUser->id,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ]);

        $this->actingAs($user)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('Koleksi Buku')
            ->assertSee('>3</span>', false);
    }
}
