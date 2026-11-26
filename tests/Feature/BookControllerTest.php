<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookCategory;
use App\Models\SubscriptionPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_list_books_and_open_create_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $bookCategory = BookCategory::create(['name' => 'Form Category '.Str::uuid()]);

        $this->actingAs($admin)
            ->get(route('admin.books.index'))
            ->assertOk()
            ->assertSee('id="books-table"', false)
            ->assertSee('serverSide: true')
            ->assertSee('class="nav-item active"', false)
            ->assertSee(route('admin.books.create'));

        $this->get(route('admin.books.create'))
            ->assertOk()
            ->assertSee('Tambah Buku')
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('action="'.route('admin.books.store').'"', false)
            ->assertSee('name="cover"', false)
            ->assertSee('name="book_category_id"', false)
            ->assertSee('value="'.$bookCategory->id.'"', false)
            ->assertSee($bookCategory->name)
            ->assertSee('id="description-editor"', false)
            ->assertSee('name="description"', false)
            ->assertSee("new Quill('#description-editor'", false)
            ->assertSee('value = quill.root.innerHTML', false);

        $this->get('/admin/books/1')->assertMethodNotAllowed();
    }

    public function test_admin_receives_server_side_book_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $bookCategory = BookCategory::create(['name' => 'CRUD Book Category '.Str::uuid()]);
        $title = 'CRUD Book '.Str::uuid();
        $book = Book::create([
            'cover' => 'books/test-cover.jpg',
            'title' => $title,
            'price' => 25000,
            'language' => 'Indonesia',
            'publisher' => 'Penerbit Tes',
            'writer' => 'Penulis Tes',
            'release_date' => '2026-01-15',
            'page_of_book' => 200,
            'book_category_id' => $bookCategory->id,
        ]);

        $response = $this->actingAs($admin)->getJson(
            route('admin.books.index', [
                'draw' => 2,
                'start' => 0,
                'length' => 10,
                'columns' => [
                    ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'searchable' => 'false', 'orderable' => 'false'],
                    ['data' => 'cover_preview', 'name' => 'cover', 'searchable' => 'false', 'orderable' => 'false'],
                    ['data' => 'title', 'name' => 'title', 'searchable' => 'true', 'orderable' => 'true'],
                    ['data' => 'writer', 'name' => 'writer', 'searchable' => 'true', 'orderable' => 'true'],
                    ['data' => 'category_name', 'name' => 'bookCategory.name', 'searchable' => 'true', 'orderable' => 'true'],
                    ['data' => 'price', 'name' => 'price', 'searchable' => 'true', 'orderable' => 'true'],
                    ['data' => 'action', 'name' => 'action', 'searchable' => 'false', 'orderable' => 'false'],
                ],
                'search' => ['value' => $bookCategory->name, 'regex' => 'false'],
            ]),
            ['X-Requested-With' => 'XMLHttpRequest'],
        );

        $response->assertOk()
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.title', $title)
            ->assertJsonPath('data.0.category_name', $bookCategory->name)
            ->assertJsonPath('data.0.price', 'Rp 25.000')
            ->assertJsonPath('data.0.cover', fn (?string $cover): bool => str_contains((string) $cover, 'books/test-cover.jpg'))
            ->assertJsonPath('data.0.action', fn (string $action): bool => str_contains($action, route('admin.books.edit', $book))
                && str_contains($action, 'btn-warning')
                && str_contains($action, 'btn-danger'));

        $this->assertSame($book->id, Book::where('title', $title)->value('id'));
    }

    public function test_home_book_title_opens_the_book_detail_page(): void
    {
        $bookCategory = BookCategory::create(['name' => 'Home Detail Category '.Str::uuid()]);
        $book = Book::create([
            'cover' => 'covers/home-detail.jpg',
            'title' => 'Home Detail Book',
            'price' => 25000,
            'language' => 'Indonesia',
            'publisher' => 'Penerbit Tes',
            'writer' => 'Penulis Tes',
            'release_date' => '2026-01-15',
            'page_of_book' => 200,
            'book_category_id' => $bookCategory->id,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('books.show', $book).'"', false);
    }

    public function test_book_detail_displays_linked_packages_and_unit_price(): void
    {
        $bookCategory = BookCategory::create(['name' => 'Detail Category '.Str::uuid()]);
        $book = Book::create([
            'cover' => 'covers/detail.jpg',
            'title' => 'Detail Book',
            'description' => '<p>Deskripsi buku <strong>lengkap</strong>.</p>',
            'price' => 120000,
            'language' => 'Indonesia',
            'publisher' => 'Gramedia',
            'writer' => 'Penulis Tes',
            'release_date' => '2026-01-15',
            'page_of_book' => 240,
            'book_category_id' => $bookCategory->id,
        ]);
        $linkedPackage = SubscriptionPackage::create([
            'name_package' => 'Paket Fiksi',
            'description' => 'Paket untuk buku fiksi',
            'color' => '#123456',
            'price' => 50000,
        ]);
        $unlinkedPackage = SubscriptionPackage::create([
            'name_package' => 'Paket Tidak Tertaut',
            'description' => 'Paket lain',
            'color' => '#654321',
            'price' => 75000,
        ]);
        $linkedPackage->books()->attach($book);

        $this->get(route('books.show', $book))
            ->assertOk()
            ->assertSee('Detail Book')
            ->assertSee('Paket Fiksi')
            ->assertDontSee($unlinkedPackage->name_package)
            ->assertSee('Rp50.000')
            ->assertSee('Rp120.000')
            ->assertSee('Baca Selengkapnya')
            ->assertSee('<p>Deskripsi buku <strong>lengkap</strong>.</p>', false)
            ->assertSee('Indonesia')
            ->assertSee('Gramedia')
            ->assertSee('15 January 2026')
            ->assertSee('240 Halaman')
            ->assertSee('value="none" class="form-selectgroup-input" checked', false);
    }

    public function test_book_detail_returns_not_found_for_unknown_book(): void
    {
        $this->get(route('books.show', PHP_INT_MAX))
            ->assertNotFound();
    }

    public function test_admin_can_save_book_description_and_category(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $bookCategory = BookCategory::create(['name' => 'Save Book Category '.Str::uuid()]);
        $title = 'Book with description '.Str::uuid();

        $this->actingAs($admin)
            ->post(route('admin.books.store'), [
                ...$this->validBookData($bookCategory, $title),
                'cover' => UploadedFile::fake()->image('cover.jpg'),
            ])
            ->assertRedirect(route('admin.books.index'))
            ->assertSessionHas('success', 'Buku berhasil ditambahkan.');

        $book = Book::where('title', $title)->firstOrFail();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'description' => '<p>Deskripsi tes buku</p>',
            'book_category_id' => $bookCategory->id,
        ]);
        Storage::disk('public')->assertExists($book->cover);
    }

    public function test_admin_can_create_update_and_delete_a_book_and_its_cover(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $bookCategory = BookCategory::create(['name' => 'CRUD Book Category '.Str::uuid()]);
        $title = 'CRUD Book '.Str::uuid();

        $this->actingAs($admin)
            ->post(route('admin.books.store'), [
                ...$this->validBookData($bookCategory, $title),
                'cover' => UploadedFile::fake()->image('cover.jpg'),
            ])
            ->assertRedirect(route('admin.books.index'))
            ->assertSessionHas('success', 'Buku berhasil ditambahkan.');

        $book = Book::where('title', $title)->firstOrFail();
        $originalCoverPath = $book->cover;
        Storage::disk('public')->assertExists($originalCoverPath);
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => $title,
            'description' => '<p>Deskripsi tes buku</p>',
            'book_category_id' => $bookCategory->id,
        ]);

        $this->get(route('admin.books.edit', $book))
            ->assertOk()
            ->assertSee($title)
            ->assertSee('name="cover"', false);

        $updatedTitle = "{$title} Updated";
        $this->put(route('admin.books.update', $book), $this->validBookData($bookCategory, $updatedTitle))
            ->assertRedirect(route('admin.books.index'))
            ->assertSessionHas('success', 'Buku berhasil diperbarui.');

        $book->refresh();
        $this->assertSame($originalCoverPath, $book->cover);
        $this->assertSame('<p>Deskripsi tes buku</p>', $book->description);
        Storage::disk('public')->assertExists($originalCoverPath);

        $this->put(route('admin.books.update', $book), [
            ...$this->validBookData($bookCategory, $updatedTitle),
            'cover' => UploadedFile::fake()->image('replacement.png'),
        ])
            ->assertRedirect(route('admin.books.index'));

        $book->refresh();
        $replacementCoverPath = $book->cover;
        $this->assertNotSame($originalCoverPath, $replacementCoverPath);
        Storage::disk('public')->assertMissing($originalCoverPath);
        Storage::disk('public')->assertExists($replacementCoverPath);

        $this->delete(route('admin.books.destroy', $book))
            ->assertRedirect(route('admin.books.index'))
            ->assertSessionHas('success', 'Buku berhasil dihapus.');

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
        Storage::disk('public')->assertMissing($replacementCoverPath);
    }

    public function test_book_cover_must_be_an_allowed_image_and_category_must_exist(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $bookCategory = BookCategory::create(['name' => 'CRUD Book Category '.Str::uuid()]);

        $this->actingAs($admin)
            ->post(route('admin.books.store'), [
                ...$this->validBookData($bookCategory, 'Invalid Book'),
                'book_category_id' => 999999,
                'cover' => UploadedFile::fake()->create('cover.pdf', 10, 'application/pdf'),
            ])
            ->assertSessionHasErrors(['cover', 'book_category_id']);

        $this->assertDatabaseMissing('books', ['title' => 'Invalid Book']);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_book_fields_and_cover_are_required_when_creating_a_book(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.books.store'), [])
            ->assertSessionHasErrors([
                'cover',
                'title',
                'price',
                'language',
                'publisher',
                'writer',
                'release_date',
                'page_of_book',
                'book_category_id',
            ]);
    }

    public function test_deleting_a_category_also_deletes_cascaded_book_covers(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $bookCategory = BookCategory::create(['name' => 'CRUD Book Category '.Str::uuid()]);
        $coverPath = 'books/category-book-cover.jpg';
        Storage::disk('public')->put($coverPath, 'test image');
        $book = Book::create([
            ...$this->validBookData($bookCategory, 'Category cleanup book'),
            'cover' => $coverPath,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.book-categories.destroy', $bookCategory))
            ->assertRedirect(route('admin.book-categories.index'));

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
        Storage::disk('public')->assertMissing($coverPath);
    }

    /**
     * @return array<string, mixed>
     */
    private function validBookData(BookCategory $bookCategory, string $title): array
    {
        return [
            'title' => $title,
            'description' => '<p>Deskripsi tes buku</p>',
            'price' => 25000,
            'language' => 'Indonesia',
            'publisher' => 'Penerbit Tes',
            'writer' => 'Penulis Tes',
            'release_date' => '2026-01-15',
            'page_of_book' => 200,
            'book_category_id' => $bookCategory->id,
        ];
    }
}
