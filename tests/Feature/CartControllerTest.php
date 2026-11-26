<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookCategory;
use App\Models\Cart;
use App\Models\SubscriptionPackage;
use App\Models\SubscriptionPackageUser;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_unit_purchase_displays_success_message_book_cover_and_price(): void
    {
        $user = User::factory()->create();
        $category = BookCategory::create(['name' => 'Cart Category '.Str::uuid()]);
        $book = Book::create([
            'cover' => 'covers/cart-test.jpg',
            'title' => 'Cart Test Book',
            'price' => 15000,
            'language' => 'Indonesia',
            'publisher' => 'Penerbit Tes',
            'writer' => 'Penulis Tes',
            'release_date' => '2026-01-15',
            'page_of_book' => 120,
            'book_category_id' => $category->id,
        ]);
        $subscriptionPackage = SubscriptionPackage::create([
            'name_package' => 'Cart Package '.Str::uuid(),
            'price' => 25000,
        ]);
        Cart::create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'subscription_package_id' => $subscriptionPackage->id,
        ]);

        $response = $this->actingAs($user)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post(route('carts.store'), [
                'action' => 'add_to_cart',
                'book_id' => $book->id,
                'purchase_option' => 'none',
            ]);

        $response->assertRedirect(route('carts'));
        $this->assertDatabaseHas('carts', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'subscription_package_id' => null,
        ]);
        $cart = Cart::query()
            ->where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->firstOrFail();

        $this->actingAs($user)
            ->get(route('carts'))
            ->assertOk()
            ->assertSee('Buku berhasil ditambahkan ke keranjang.')
            ->assertSee('src="'.asset('storage/covers/cart-test.jpg').'"', false)
            ->assertSee('Rp15.000')
            ->assertSee('value="'.$cart->id.'" data-price="15000" checked', false)
            ->assertSee('class="summary-row', false)
            ->assertSee('data-cart-id="'.$cart->id.'"', false)
            ->assertSee("input.name = 'selected_carts[]';", false)
            ->assertSee('Subtotal')
            ->assertSee('Checkout');
    }

    public function test_user_can_add_a_book_with_a_linked_subscription_package(): void
    {
        $user = User::factory()->create();
        $category = BookCategory::create(['name' => 'Package Cart Category '.Str::uuid()]);
        $book = Book::create([
            'cover' => 'covers/package-cart-test.jpg',
            'title' => 'Package Cart Test Book',
            'price' => 15000,
            'language' => 'Indonesia',
            'publisher' => 'Penerbit Tes',
            'writer' => 'Penulis Tes',
            'release_date' => '2026-01-15',
            'page_of_book' => 120,
            'book_category_id' => $category->id,
        ]);
        $subscriptionPackage = SubscriptionPackage::create([
            'name_package' => 'Package Cart '.Str::uuid(),
            'price' => 25000,
        ]);
        $subscriptionPackage->books()->attach($book);

        $this->actingAs($user)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post(route('carts.store'), [
                'action' => 'add_to_cart',
                'book_id' => $book->id,
                'purchase_option' => (string) $subscriptionPackage->id,
            ])
            ->assertRedirect(route('carts'));

        $this->assertDatabaseHas('carts', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'subscription_package_id' => $subscriptionPackage->id,
        ]);

        $this->actingAs($user)
            ->get(route('carts'))
            ->assertSee($subscriptionPackage->name_package)
            ->assertSee('Rp25.000');
    }

    public function test_cart_summary_displays_prices_and_marks_packages_the_user_does_not_have(): void
    {
        $user = User::factory()->create();
        $category = BookCategory::create(['name' => 'Dynamic Cart Category '.Str::uuid()]);
        $activePackage = SubscriptionPackage::create([
            'name_package' => 'Active Cart Package '.Str::uuid(),
            'price' => 50000,
        ]);
        $unownedPackage = SubscriptionPackage::create([
            'name_package' => 'Unowned Cart Package '.Str::uuid(),
            'price' => 80000,
        ]);
        $activeBook = $this->createBook($category, 'Active package book', 10000);
        $unownedBook = $this->createBook($category, 'Unowned package book', 12000);
        $unitBook = $this->createBook($category, 'Unit price book', 30000);
        $activeCart = Cart::create([
            'book_id' => $activeBook->id,
            'user_id' => $user->id,
            'subscription_package_id' => $activePackage->id,
        ]);
        $unownedCart = Cart::create([
            'book_id' => $unownedBook->id,
            'user_id' => $user->id,
            'subscription_package_id' => $unownedPackage->id,
        ]);
        $unitCart = Cart::create([
            'book_id' => $unitBook->id,
            'user_id' => $user->id,
            'subscription_package_id' => null,
        ]);
        SubscriptionPackageUser::create([
            'user_id' => $user->id,
            'subscription_package_id' => $activePackage->id,
            'expired_date' => today()->addDay()->toDateString(),
        ]);

        $this->actingAs($user)
            ->get(route('carts'))
            ->assertOk()
            ->assertSee('data-price="50000"', false)
            ->assertSee('data-price="80000"', false)
            ->assertSee('data-price="30000"', false)
            ->assertSee('data-cart-id="'.$activeCart->id.'"', false)
            ->assertSee('data-cart-id="'.$unownedCart->id.'"', false)
            ->assertSee('data-cart-id="'.$unitCart->id.'"', false)
            ->assertSee('Perlu membeli paket '.$unownedPackage->name_package)
            ->assertDontSee('Perlu membeli paket '.$activePackage->name_package)
            ->assertSee('Rp 160.000');
    }

    public function test_user_cannot_choose_a_package_not_linked_to_the_book(): void
    {
        $user = User::factory()->create();
        $category = BookCategory::create(['name' => 'Invalid Package Category '.Str::uuid()]);
        $book = Book::create([
            'cover' => 'covers/invalid-package.jpg',
            'title' => 'Invalid Package Book',
            'price' => 15000,
            'language' => 'Indonesia',
            'publisher' => 'Penerbit Tes',
            'writer' => 'Penulis Tes',
            'release_date' => '2026-01-15',
            'page_of_book' => 120,
            'book_category_id' => $category->id,
        ]);
        $subscriptionPackage = SubscriptionPackage::create([
            'name_package' => 'Unlinked Cart Package '.Str::uuid(),
            'price' => 25000,
        ]);

        $this->actingAs($user)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->from(route('books.show', $book))
            ->followingRedirects()
            ->post(route('carts.store'), [
                'action' => 'add_to_cart',
                'book_id' => $book->id,
                'purchase_option' => (string) $subscriptionPackage->id,
            ])
            ->assertOk()
            ->assertSee('Terjadi kesalahan, silakan coba lagi.');

        $this->assertDatabaseMissing('carts', [
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_add_to_cart_requires_book_purchase_option_and_action(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post(route('carts.store'), [])
            ->assertRedirect()
            ->assertSessionHasErrors(['book_id', 'purchase_option', 'action']);

        $this->assertDatabaseMissing('carts', ['user_id' => $user->id]);
    }

    public function test_user_only_sees_and_can_delete_their_own_cart_items(): void
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $category = BookCategory::create(['name' => 'Isolated Cart Category '.Str::uuid()]);
        $ownedBook = Book::create([
            'cover' => 'covers/owned-cart-book.jpg',
            'title' => 'Owned Cart Book',
            'price' => 15000,
            'language' => 'Indonesia',
            'publisher' => 'Penerbit Tes',
            'writer' => 'Penulis Tes',
            'release_date' => '2026-01-15',
            'page_of_book' => 120,
            'book_category_id' => $category->id,
        ]);
        $anotherBook = Book::create([
            'cover' => 'covers/another-cart-book.jpg',
            'title' => 'Another User Cart Book',
            'price' => 25000,
            'language' => 'Indonesia',
            'publisher' => 'Penerbit Tes',
            'writer' => 'Penulis Tes',
            'release_date' => '2026-01-15',
            'page_of_book' => 180,
            'book_category_id' => $category->id,
        ]);
        $ownedCart = Cart::create([
            'book_id' => $ownedBook->id,
            'user_id' => $user->id,
            'subscription_package_id' => null,
        ]);
        $anotherCart = Cart::create([
            'book_id' => $anotherBook->id,
            'user_id' => $anotherUser->id,
            'subscription_package_id' => null,
        ]);

        $this->withoutMiddleware(PreventRequestForgery::class);

        $this->actingAs($user)
            ->get(route('carts'))
            ->assertSee('Owned Cart Book')
            ->assertDontSee('Another User Cart Book');

        $this->actingAs($user)
            ->delete(route('carts.destroy', $anotherCart))
            ->assertNotFound();

        $this->delete(route('carts.destroy', $ownedCart))
            ->assertRedirect(route('carts'))
            ->assertSessionHas('success', 'Buku berhasil dihapus dari keranjang.');

        $this->assertDatabaseMissing('carts', ['id' => $ownedCart->id]);
        $this->assertDatabaseHas('carts', ['id' => $anotherCart->id]);
    }

    public function test_guest_is_redirected_to_login_when_opening_the_cart(): void
    {
        $this->get(route('carts'))
            ->assertRedirect(route('login'));
    }

    public function test_legacy_cart_url_redirects_to_the_plural_cart_url(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/cart')
            ->assertRedirect(route('carts'));
    }

    public function test_buy_now_does_not_add_the_book_to_the_cart_until_checkout_is_available(): void
    {
        $user = User::factory()->create();
        $category = BookCategory::create(['name' => 'Buy Now Category '.Str::uuid()]);
        $book = Book::create([
            'cover' => 'covers/buy-now-book.jpg',
            'title' => 'Buy Now Book',
            'price' => 15000,
            'language' => 'Indonesia',
            'publisher' => 'Penerbit Tes',
            'writer' => 'Penulis Tes',
            'release_date' => '2026-01-15',
            'page_of_book' => 120,
            'book_category_id' => $category->id,
        ]);

        $this->actingAs($user)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->from(route('books.show', $book))
            ->post(route('carts.store'), [
                'action' => 'buy_now',
                'book_id' => $book->id,
                'purchase_option' => 'none',
            ])
            ->assertRedirect(route('books.show', $book))
            ->assertSessionHas('error', 'Fitur Beli Sekarang belum tersedia.');

        $this->assertDatabaseMissing('carts', [
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);
    }

    private function createBook(BookCategory $category, string $title, int $price): Book
    {
        return Book::create([
            'cover' => 'covers/'.Str::slug($title).'.jpg',
            'title' => $title,
            'price' => $price,
            'language' => 'Indonesia',
            'publisher' => 'Penerbit Tes',
            'writer' => 'Penulis Tes',
            'release_date' => '2026-01-15',
            'page_of_book' => 120,
            'book_category_id' => $category->id,
        ]);
    }
}
