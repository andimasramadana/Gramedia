<?php

namespace Tests\Feature;

use App\Models\BookCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookCategoryControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_list_categories_and_open_create_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.book-categories.index'))
            ->assertOk()
            ->assertSee('id="book-categories-table"', false)
            ->assertSee('serverSide: true')
            ->assertSee('class="nav-item active"', false)
            ->assertSee(route('admin.book-categories.create'));

        $this->get(route('admin.book-categories.create'))
            ->assertOk()
            ->assertSee('Tambah Kategori Buku');

        $this->get('/admin/book-categories/1')->assertStatus(405);
    }

    public function test_admin_receives_paginated_category_data_from_the_server(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $existingCategoriesCount = BookCategory::count();
        $categoryPrefix = 'Server Side '.Str::uuid();

        foreach (["{$categoryPrefix} Alpha", "{$categoryPrefix} Beta", "{$categoryPrefix} Gamma"] as $name) {
            BookCategory::create(['name' => $name]);
        }

        $response = $this->actingAs($admin)->getJson(
            route('admin.book-categories.index', [
                'draw' => 3,
                'start' => 1,
                'length' => 1,
                'search' => ['value' => $categoryPrefix, 'regex' => 'false'],
                'columns' => [
                    ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'searchable' => 'false', 'orderable' => 'false'],
                    ['data' => 'name', 'name' => 'name', 'searchable' => 'true', 'orderable' => 'true'],
                    ['data' => 'action', 'name' => 'action', 'searchable' => 'false', 'orderable' => 'false'],
                ],
                'order' => [['column' => 1, 'dir' => 'asc']],
            ]),
            ['X-Requested-With' => 'XMLHttpRequest'],
        );

        $response->assertOk()
            ->assertJsonPath('draw', 3)
            ->assertJsonPath('recordsTotal', $existingCategoriesCount + 3)
            ->assertJsonPath('recordsFiltered', 3)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', "{$categoryPrefix} Beta")
            ->assertJsonPath('data.0.action', fn (string $action): bool => str_contains($action, 'btn-warning') && str_contains($action, 'btn-danger'));
    }

    public function test_admin_can_search_and_sort_categories_server_side(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $existingCategoriesCount = BookCategory::count();
        $searchToken = Str::uuid();

        foreach (["Book Fiction {$searchToken}", "Book Science {$searchToken}", "Magazine Fiction {$searchToken}"] as $name) {
            BookCategory::create(['name' => $name]);
        }

        $response = $this->actingAs($admin)->getJson(
            route('admin.book-categories.index', [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
                'columns' => [
                    ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'searchable' => 'false', 'orderable' => 'false'],
                    ['data' => 'name', 'name' => 'name', 'searchable' => 'true', 'orderable' => 'true'],
                    ['data' => 'action', 'name' => 'action', 'searchable' => 'false', 'orderable' => 'false'],
                ],
                'order' => [['column' => 1, 'dir' => 'desc']],
                'search' => ['value' => "Fiction {$searchToken}", 'regex' => 'false'],
            ]),
            ['X-Requested-With' => 'XMLHttpRequest'],
        );

        $response->assertOk()
            ->assertJsonPath('recordsTotal', $existingCategoriesCount + 3)
            ->assertJsonPath('recordsFiltered', 2)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', "Magazine Fiction {$searchToken}")
            ->assertJsonPath('data.1.name', "Book Fiction {$searchToken}");
    }

    public function test_admin_can_create_update_and_delete_a_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.book-categories.store'), ['name' => 'CRUD Created Category'])
            ->assertRedirect(route('admin.book-categories.index'))
            ->assertSessionHas('success', 'Kategori buku berhasil ditambahkan.');

        $category = BookCategory::where('name', 'CRUD Created Category')->firstOrFail();
        $this->assertDatabaseHas('book_categories', ['id' => $category->id, 'name' => 'CRUD Created Category']);

        $this->get(route('admin.book-categories.edit', $category))
            ->assertOk()
            ->assertSee('CRUD Created Category');

        $this->put(route('admin.book-categories.update', $category), ['name' => 'CRUD Updated Category'])
            ->assertRedirect(route('admin.book-categories.index'))
            ->assertSessionHas('success', 'Kategori buku berhasil diperbarui.');

        $this->assertDatabaseHas('book_categories', ['id' => $category->id, 'name' => 'CRUD Updated Category']);

        $this->delete(route('admin.book-categories.destroy', $category))
            ->assertRedirect(route('admin.book-categories.index'))
            ->assertSessionHas('success', 'Kategori buku berhasil dihapus.');

        $this->assertDatabaseMissing('book_categories', ['id' => $category->id]);
    }

    public function test_category_name_is_required_and_unique(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = BookCategory::create(['name' => 'CRUD Existing Category']);

        $this->actingAs($admin)
            ->post(route('admin.book-categories.store'), ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->post(route('admin.book-categories.store'), ['name' => 'CRUD Existing Category'])
            ->assertSessionHasErrors('name');

        $this->put(route('admin.book-categories.update', $category), ['name' => 'CRUD Existing Category'])
            ->assertRedirect(route('admin.book-categories.index'));
    }
}
