<?php

namespace Tests\Feature;

use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_page_renders_empty_cart(): void
    {
        $this->get(route('cart'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Cart')
                ->where('cart.items', [])
                ->where('cart.count', 0)
                ->where('cart.subtotal', 0)
            );
    }

    public function test_guest_can_add_active_in_stock_variant(): void
    {
        $this->seed(CatalogSeeder::class);
        $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();

        $this->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ])->assertRedirect();

        $this->get(route('cart'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Cart')
                ->where('cart.count', 2)
                ->has('cart.items', 1)
                ->where('cart.items.0.quantity', 2)
                ->where('cart.items.0.variant.id', $variant->id)
            );
    }

    public function test_same_variant_updates_quantity_instead_of_duplicate(): void
    {
        $this->seed(CatalogSeeder::class);
        $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();

        $this->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $this->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->get(route('cart'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('cart.count', 3)
                ->has('cart.items', 1)
                ->where('cart.items.0.quantity', 3)
            );
    }

    public function test_quantity_cannot_exceed_stock(): void
    {
        $this->seed(CatalogSeeder::class);
        $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();

        $this->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => $variant->stock + 1,
        ])->assertSessionHasErrors('quantity');
    }

    public function test_out_of_stock_variant_cannot_be_added(): void
    {
        $this->seed(CatalogSeeder::class);
        $variant = ProductVariant::query()->where('sku', 'LL-LBR-69-SGE')->firstOrFail();
        $this->assertEquals(0, $variant->stock);

        $this->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ])->assertSessionHasErrors('product_variant_id');
    }

    public function test_inactive_variant_cannot_be_added(): void
    {
        $this->seed(CatalogSeeder::class);
        $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();
        $variant->forceFill(['is_active' => false])->save();

        $this->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ])->assertSessionHasErrors('product_variant_id');
    }

    public function test_inactive_product_variant_cannot_be_added(): void
    {
        $this->seed(CatalogSeeder::class);
        $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();
        $variant->product->forceFill(['is_active' => false])->save();

        $this->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ])->assertSessionHasErrors('product_variant_id');
    }

    public function test_inactive_category_variant_cannot_be_added(): void
    {
        $this->seed(CatalogSeeder::class);
        $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();
        $variant->product->category->forceFill(['is_active' => false])->save();

        $this->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ])->assertSessionHasErrors('product_variant_id');
    }

    public function test_cart_page_presents_current_backend_price(): void
    {
        $this->seed(CatalogSeeder::class);
        $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();

        $this->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $variant->forceFill(['price' => 300000])->save();

        $this->get(route('cart'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('cart.items.0.variant.price', 300000)
                ->where('cart.items.0.line_total', 600000)
                ->where('cart.subtotal', 600000)
            );
    }

    public function test_update_quantity_revalidates_stock(): void
    {
        $this->seed(CatalogSeeder::class);
        $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();

        $this->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $cart = $this->get(route('cart'))->inertiaProps('cart');
        $cartItemId = $cart['items'][0]['id'];

        $variant->forceFill(['stock' => 3])->save();

        $this->patch(route('cart.items.update', $cartItemId), [
            'quantity' => 4,
        ])->assertSessionHasErrors('quantity');
    }

    public function test_cart_sync_clamps_quantity_when_stock_drops(): void
    {
        $this->seed(CatalogSeeder::class);
        $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();

        $this->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 5,
        ]);

        $variant->forceFill(['stock' => 2])->save();

        $this->get(route('cart'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('cart.items.0.quantity', 2)
                ->has('cartAdjustments', 1)
            );
    }

    public function test_cart_sync_removes_unavailable_items(): void
    {
        $this->seed(CatalogSeeder::class);
        $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();

        $this->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $variant->forceFill(['stock' => 0])->save();

        $this->get(route('cart'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('cart.items', [])
                ->where('cart.count', 0)
                ->has('cartAdjustments', 1)
            );
    }

    public function test_remove_item_works_only_for_current_cart(): void
    {
        $this->seed(CatalogSeeder::class);
        $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();

        $this->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $cart = $this->get(route('cart'))->inertiaProps('cart');
        $cartItemId = $cart['items'][0]['id'];

        $this->withoutMiddleware()->delete(route('cart.items.destroy', $cartItemId + 9999))
            ->assertNotFound();
    }

    public function test_authenticated_user_cart_persists(): void
    {
        $this->seed(CatalogSeeder::class);
        $user = User::factory()->create();
        $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();

        $this->actingAs($user)->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $this->actingAs($user)->get(route('cart'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('cart.count', 2)
                ->has('cart.items', 1)
            );
    }

    public function test_import_validates_variant_and_ignores_client_data(): void
    {
        $this->seed(CatalogSeeder::class);
        $validVariant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();
        $invalidVariant = ProductVariant::query()->where('sku', 'LL-LBR-69-SGE')->firstOrFail();

        $response = $this->postJson(route('cart.import'), [
            'items' => [
                [
                    'product_variant_id' => $validVariant->id,
                    'quantity' => 2,
                ],
                [
                    'product_variant_id' => $invalidVariant->id,
                    'quantity' => 1,
                ],
            ],
        ])->assertOk();

        $this->assertEquals(2, $response->json('cart.count'));
        $this->assertCount(1, $response->json('cart.items'));
        $this->assertNotEmpty($response->json('adjustments'));
    }
}
