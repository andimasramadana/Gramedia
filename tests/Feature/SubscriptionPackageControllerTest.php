<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookCategory;
use App\Models\SubscriptionPackage;
use App\Models\SubscriptionPackageUser;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class SubscriptionPackageControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_list_packages_and_open_create_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.subscription-packages.index'))
            ->assertOk()
            ->assertSee('id="subscription-packages-table"', false)
            ->assertSee('serverSide: true')
            ->assertSee('class="nav-item active"', false)
            ->assertSee(route('admin.subscription-packages.create'));

        $this->get(route('admin.subscription-packages.create'))
            ->assertOk()
            ->assertSee('Tambah Paket Langganan')
            ->assertSee('name="price"', false)
            ->assertSee('name="color"', false)
            ->assertDontSee('name="description"', false);

        $this->get('/admin/subscription-packages/1')->assertStatus(405);
    }

    public function test_admin_can_download_subscription_package_export_with_book_and_active_customer_counts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'user']);
        $expiredCustomer = User::factory()->create(['role' => 'user']);
        $category = BookCategory::create(['name' => 'Export Category '.Str::uuid()]);
        $packageName = 'Export Package '.Str::uuid();
        $subscriptionPackage = SubscriptionPackage::create([
            'name_package' => $packageName,
            'description' => 'Package for export test',
            'color' => '#123456',
            'price' => 25000,
        ]);
        $book = Book::create([
            'cover' => 'covers/export-test.jpg',
            'title' => 'Export Test Book',
            'price' => 15000,
            'language' => 'Indonesia',
            'publisher' => 'Penerbit Tes',
            'writer' => 'Penulis Tes',
            'release_date' => '2026-01-15',
            'page_of_book' => 120,
            'book_category_id' => $category->id,
        ]);
        $subscriptionPackage->books()->attach($book);
        SubscriptionPackageUser::create([
            'subscription_package_id' => $subscriptionPackage->id,
            'user_id' => $customer->id,
            'expired_date' => now()->toDateString(),
        ]);
        SubscriptionPackageUser::create([
            'subscription_package_id' => $subscriptionPackage->id,
            'user_id' => $expiredCustomer->id,
            'expired_date' => now()->subDay()->toDateString(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.subscription-packages.export'));

        $response->assertDownload('Subscription.xlsx');
        $spreadsheet = IOFactory::load($response->baseResponse->getFile()->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $dataRow = null;

        for ($row = 3; $row <= $sheet->getHighestDataRow(); $row++) {
            if ($sheet->getCell("B{$row}")->getValue() === $packageName) {
                $dataRow = $row;
                break;
            }
        }

        $this->assertNotNull($dataRow);
        $this->assertSame(1, $sheet->getCell("D{$dataRow}")->getValue());
        $this->assertSame(1, $sheet->getCell("E{$dataRow}")->getValue());
    }

    public function test_admin_receives_server_side_package_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $existingPackagesCount = SubscriptionPackage::count();
        $packagePrefix = 'CRUD Plan '.Str::uuid();

        foreach (['Alpha', 'Beta', 'Gamma'] as $suffix) {
            SubscriptionPackage::create([
                'name_package' => "{$packagePrefix} {$suffix}",
                'description' => 'Test package description',
                'color' => '#336699',
                'price' => 19000,
            ]);
        }

        $response = $this->actingAs($admin)->getJson(
            route('admin.subscription-packages.index', [
                'draw' => 4,
                'start' => 0,
                'length' => 10,
                'columns' => [
                    ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'searchable' => 'false', 'orderable' => 'false'],
                    ['data' => 'name_package', 'name' => 'name_package', 'searchable' => 'true', 'orderable' => 'true'],
                    ['data' => 'price', 'name' => 'price', 'searchable' => 'true', 'orderable' => 'true'],
                    ['data' => 'action', 'name' => 'action', 'searchable' => 'false', 'orderable' => 'false'],
                ],
                'order' => [['column' => 1, 'dir' => 'asc']],
                'search' => ['value' => $packagePrefix, 'regex' => 'false'],
            ]),
            ['X-Requested-With' => 'XMLHttpRequest'],
        );

        $response->assertOk()
            ->assertJsonPath('draw', 4)
            ->assertJsonPath('recordsTotal', $existingPackagesCount + 3)
            ->assertJsonPath('recordsFiltered', 3)
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.name_package', "{$packagePrefix} Alpha")
            ->assertJsonPath('data.0.price', 'Rp 19.000')
            ->assertJsonMissingPath('data.0.description')
            ->assertJsonMissingPath('data.0.color_preview')
            ->assertJsonPath('data.0.action', fn (string $action): bool => str_contains($action, 'btn-success">Buku</a>')
                && str_contains($action, 'btn-warning">Edit</a>')
                && str_contains($action, 'btn-danger">Hapus</button>')
                && str_contains($action, '#books'));
    }

    public function test_admin_can_create_update_and_delete_a_package(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $name = 'CRUD Package '.Str::uuid();
        $this->actingAs($admin)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post(route('admin.subscription-packages.store'), ['name_package' => $name, 'price' => 25000, 'color' => '#123456'])
            ->assertRedirect(route('admin.subscription-packages.index'))
            ->assertSessionHas('success', 'Paket langganan berhasil ditambahkan.');

        $subscriptionPackage = SubscriptionPackage::where('name_package', $name)->firstOrFail();
        $this->assertDatabaseHas('subscription_packages', [
            'id' => $subscriptionPackage->id,
            'name_package' => $name,
            'price' => 25000,
            'description' => null,
            'color' => '#123456',
        ]);

        $this->get(route('admin.subscription-packages.edit', $subscriptionPackage))
            ->assertOk()
            ->assertSee($name)
            ->assertSee('value="25000"', false)
            ->assertSee('name="color"', false)
            ->assertDontSee('name="description"', false)
            ->assertSee('name="price"', false);

        $this->withoutMiddleware(PreventRequestForgery::class)
            ->put(route('admin.subscription-packages.update', $subscriptionPackage), [
                'name_package' => "{$name} Updated",
                'price' => 35000,
                'color' => '#654321',
            ])
            ->assertRedirect(route('admin.subscription-packages.index'))
            ->assertSessionHas('success', 'Paket langganan berhasil diperbarui.');

        $this->assertDatabaseHas('subscription_packages', [
            'id' => $subscriptionPackage->id,
            'name_package' => "{$name} Updated",
            'price' => 35000,
            'description' => null,
            'color' => '#654321',
        ]);

        $this->withoutMiddleware(PreventRequestForgery::class)
            ->delete(route('admin.subscription-packages.destroy', $subscriptionPackage))
            ->assertRedirect(route('admin.subscription-packages.index'))
            ->assertSessionHas('success', 'Paket langganan berhasil dihapus.');

        $this->assertDatabaseMissing('subscription_packages', ['id' => $subscriptionPackage->id]);
    }

    public function test_admin_can_assign_books_to_a_package_and_update_the_assignment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = BookCategory::create(['name' => 'Package Book Category '.Str::uuid()]);
        $firstBook = $this->createBook($category, 'First package book');
        $secondBook = $this->createBook($category, 'Second package book');
        $packageName = 'Package with books '.Str::uuid();

        $this->actingAs($admin)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post(route('admin.subscription-packages.store'), [
                'name_package' => $packageName,
                'price' => 25000,
                'color' => '#123456',
                'books_present' => '1',
                'books' => [$firstBook->id],
            ])
            ->assertRedirect(route('admin.subscription-packages.index'));

        $subscriptionPackage = SubscriptionPackage::where('name_package', $packageName)->firstOrFail();
        $this->assertDatabaseHas('subscription_package_books', [
            'subscription_package_id' => $subscriptionPackage->id,
            'book_id' => $firstBook->id,
        ]);

        $this->get(route('admin.subscription-packages.edit', $subscriptionPackage))
            ->assertOk()
            ->assertSee('Buku dalam Paket')
            ->assertSee('value="'.$firstBook->id.'" selected', false)
            ->assertSee($secondBook->title);

        $this->withoutMiddleware(PreventRequestForgery::class)
            ->put(route('admin.subscription-packages.update', $subscriptionPackage), [
                'name_package' => $packageName,
                'price' => 25000,
                'color' => '#123456',
                'books_present' => '1',
                'books' => [$secondBook->id],
            ])->assertRedirect(route('admin.subscription-packages.index'));

        $this->assertDatabaseMissing('subscription_package_books', [
            'subscription_package_id' => $subscriptionPackage->id,
            'book_id' => $firstBook->id,
        ]);
        $this->assertDatabaseHas('subscription_package_books', [
            'subscription_package_id' => $subscriptionPackage->id,
            'book_id' => $secondBook->id,
        ]);

        $this->withoutMiddleware(PreventRequestForgery::class)
            ->put(route('admin.subscription-packages.update', $subscriptionPackage), [
                'name_package' => $packageName,
                'price' => 25000,
                'color' => '#123456',
                'books_present' => '1',
                'books' => [PHP_INT_MAX],
            ])
            ->assertSessionHasErrors('books.0');

        $this->assertDatabaseHas('subscription_package_books', [
            'subscription_package_id' => $subscriptionPackage->id,
            'book_id' => $secondBook->id,
        ]);

        $this->withoutMiddleware(PreventRequestForgery::class)
            ->put(route('admin.subscription-packages.update', $subscriptionPackage), [
                'name_package' => $packageName,
                'price' => 25000,
                'color' => '#123456',
                'books_present' => '1',
            ])
            ->assertRedirect(route('admin.subscription-packages.index'));

        $this->assertDatabaseMissing('subscription_package_books', [
            'subscription_package_id' => $subscriptionPackage->id,
            'book_id' => $secondBook->id,
        ]);
    }

    public function test_package_name_and_price_are_required_and_name_is_unique(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $package = SubscriptionPackage::create(['name_package' => 'Existing package', 'price' => 10000]);

        $this->actingAs($admin)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post(route('admin.subscription-packages.store'), [])
            ->assertSessionHasErrors(['name_package', 'price']);

        $this->withoutMiddleware(PreventRequestForgery::class)
            ->post(route('admin.subscription-packages.store'), ['name_package' => 'Existing package', 'price' => 10000])
            ->assertSessionHasErrors('name_package');

        $this->withoutMiddleware(PreventRequestForgery::class)
            ->put(route('admin.subscription-packages.update', $package), ['name_package' => 'Existing package', 'price' => -1])
            ->assertSessionHasErrors('price');
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
