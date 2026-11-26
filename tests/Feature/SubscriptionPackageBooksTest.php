<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookCategory;
use App\Models\SubscriptionPackage;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class SubscriptionPackageBooksTest extends TestCase
{
    use DatabaseTransactions;

    public function test_package_card_links_to_a_page_showing_only_books_in_that_package(): void
    {
        $category = BookCategory::create(['name' => 'Package Page Category '.Str::uuid()]);
        $subscriptionPackage = SubscriptionPackage::create([
            'name_package' => 'Package Page '.Str::uuid(),
            'description' => 'Package description',
            'color' => '#123456',
            'price' => 25000,
        ]);
        $includedBook = $this->createBook($category, 'Book included in package');
        $unrelatedBook = $this->createBook($category, 'Book outside package');
        $subscriptionPackage->books()->attach($includedBook);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('subscription-packages.show', $subscriptionPackage).'"', false);

        $this->get(route('subscription-packages.show', $subscriptionPackage))
            ->assertOk()
            ->assertSee($subscriptionPackage->name_package)
            ->assertSee($includedBook->title)
            ->assertDontSee($unrelatedBook->title)
            ->assertDontSee('Tambah Buku')
            ->assertDontSee('Tambahkan Buku ke Langganan');
    }

    public function test_admin_can_access_full_page_to_manage_books_for_subscription_package(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = BookCategory::create(['name' => 'Admin Package Category '.Str::uuid()]);
        $subscriptionPackage = SubscriptionPackage::create([
            'name_package' => 'Admin Package '.Str::uuid(),
            'price' => 30000,
        ]);
        $includedBook = $this->createBook($category, 'Existing Package Book');
        $availableBook = $this->createBook($category, 'Available Book For Package');
        $subscriptionPackage->books()->attach($includedBook);

        $this->actingAs($admin)
            ->get(route('admin.subscription-packages.books', $subscriptionPackage))
            ->assertOk()
            ->assertSee('Data Buku yang akan ditambahkan ke paket '.$subscriptionPackage->name_package)
            ->assertSee('Tambah Buku')
            ->assertSee('id="books-table"', false);

        $ajaxResponse = $this->actingAs($admin)->getJson(
            route('admin.subscription-package-books', ['packageId' => $subscriptionPackage->id]),
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $ajaxResponse->assertOk()
            ->assertJsonStructure(['data', 'recordsTotal', 'recordsFiltered'])
            ->assertJson(fn ($json) => $json->where('recordsTotal', fn ($total) => $total >= 2)->etc());
    }

    public function test_admin_can_add_checked_books_to_subscription_package(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = BookCategory::create(['name' => 'Add Books Category '.Str::uuid()]);
        $subscriptionPackage = SubscriptionPackage::create([
            'name_package' => 'Add Books Package '.Str::uuid(),
            'price' => 20000,
        ]);
        $book1 = $this->createBook($category, 'Selected Book 1');
        $book2 = $this->createBook($category, 'Selected Book 2');

        $this->actingAs($admin)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post(route('admin.subscription-packages.books.store', $subscriptionPackage), [
                'book_ids' => [$book1->id, $book2->id],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('subscription_package_books', [
            'subscription_package_id' => $subscriptionPackage->id,
            'book_id' => $book1->id,
        ]);
        $this->assertDatabaseHas('subscription_package_books', [
            'subscription_package_id' => $subscriptionPackage->id,
            'book_id' => $book2->id,
        ]);
    }

    public function test_adding_books_validates_required_selection(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $subscriptionPackage = SubscriptionPackage::create([
            'name_package' => 'Validate Books Package '.Str::uuid(),
            'price' => 20000,
        ]);

        $this->actingAs($admin)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post(route('admin.subscription-packages.books.store', $subscriptionPackage), [
                'book_ids' => [],
            ])
            ->assertSessionHasErrors('book_ids');
    }

    public function test_admin_can_remove_a_book_from_subscription_package(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = BookCategory::create(['name' => 'Remove Book Category '.Str::uuid()]);
        $subscriptionPackage = SubscriptionPackage::create([
            'name_package' => 'Remove Book Package '.Str::uuid(),
            'price' => 20000,
        ]);
        $book = $this->createBook($category, 'Book to Remove');
        $subscriptionPackage->books()->attach($book);

        $this->actingAs($admin)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->delete(route('admin.subscription-packages.books.destroy', [
                'subscriptionPackage' => $subscriptionPackage,
                'book' => $book,
            ]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('subscription_package_books', [
            'subscription_package_id' => $subscriptionPackage->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_guest_cannot_add_or_remove_books_from_package(): void
    {
        $category = BookCategory::create(['name' => 'Guest Check Category '.Str::uuid()]);
        $subscriptionPackage = SubscriptionPackage::create([
            'name_package' => 'Guest Check Package '.Str::uuid(),
            'price' => 20000,
        ]);
        $book = $this->createBook($category, 'Book For Guest Check');

        $this->withoutMiddleware(PreventRequestForgery::class)
            ->post(route('admin.subscription-packages.books.store', $subscriptionPackage), [
                'book_ids' => [$book->id],
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHas('error');

        $this->withoutMiddleware(PreventRequestForgery::class)
            ->delete(route('admin.subscription-packages.books.destroy', [
                'subscriptionPackage' => $subscriptionPackage,
                'book' => $book,
            ]))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error');
    }

    private function createBook(BookCategory $category, string $title): Book
    {
        return Book::create([
            'cover' => 'covers/test.jpg',
            'title' => $title,
            'price' => 15000,
            'language' => 'Indonesia',
            'publisher' => 'Penerbit Tes',
            'writer' => 'Penulis Tes',
            'release_date' => '2026-01-15',
            'page_of_book' => 120,
            'book_category_id' => $category->id,
        ]);
    }
}
