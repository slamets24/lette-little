<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    /**
     * Seed the storefront catalog.
     */
    public function run(): void
    {
        $categories = collect([
            [
                'name' => 'Dresses',
                'slug' => 'dresses',
                'description' => 'Airy silhouettes for birthdays, family photos, and soft everyday rituals.',
                'image_path' => '/images/lette/stitch-dresses.jpg',
                'sort_order' => 10,
            ],
            [
                'name' => 'Rompers',
                'slug' => 'rompers',
                'description' => 'Easy one-piece comfort with breathable fabric and gentle movement.',
                'image_path' => '/images/lette/stitch-rompers.jpg',
                'sort_order' => 20,
            ],
            [
                'name' => 'Sets',
                'slug' => 'sets',
                'description' => 'Coordinated essentials that make dressing simple and gift-ready.',
                'image_path' => '/images/lette/stitch-sets.jpg',
                'sort_order' => 30,
            ],
            [
                'name' => 'Sleepwear',
                'slug' => 'sleepwear',
                'description' => 'Quiet bedtime layers made for warm cuddles and slow mornings.',
                'image_path' => '/images/lette/stitch-sleepwear.jpg',
                'sort_order' => 40,
            ],
        ])->mapWithKeys(function (array $category) {
            $model = Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [...$category, 'is_active' => true]
            );

            return [$category['slug'] => $model];
        });

        $products = [
            [
                'category' => 'dresses',
                'name' => 'Cloud Dress',
                'slug' => 'cloud-dress',
                'description' => 'A feather-light everyday dress with soft gathers and room for tiny movement.',
                'materials' => 'Organic cotton poplin with breathable cotton lining.',
                'care_instructions' => 'Machine wash cold, gentle cycle. Line dry in shade.',
                'base_price' => 249000,
                'is_featured' => true,
                'image' => '/images/lette/stitch-product-dress.jpg',
                'variants' => [
                    ['sku' => 'LL-CDR-03-IVY', 'size' => '0-3M', 'color' => 'Ivory', 'color_hex' => '#f5eadc', 'price' => 249000, 'stock' => 6],
                    ['sku' => 'LL-CDR-36-IVY', 'size' => '3-6M', 'color' => 'Ivory', 'color_hex' => '#f5eadc', 'price' => 249000, 'stock' => 9],
                    ['sku' => 'LL-CDR-69-SND', 'size' => '6-9M', 'color' => 'Sand', 'color_hex' => '#d8c2a9', 'price' => 259000, 'stock' => 3],
                ],
            ],
            [
                'category' => 'rompers',
                'name' => 'Little Bloom Romper',
                'slug' => 'little-bloom-romper',
                'description' => 'A one-piece romper with soft leg openings, easy snaps, and a relaxed baby fit.',
                'materials' => 'Cotton modal jersey, nickel-free snaps.',
                'care_instructions' => 'Wash inside out with similar colors. Tumble dry low.',
                'base_price' => 229000,
                'is_featured' => true,
                'image' => '/images/lette/stitch-product-romper.jpg',
                'variants' => [
                    ['sku' => 'LL-LBR-03-OAT', 'size' => '0-3M', 'color' => 'Oat', 'color_hex' => '#efe2d2', 'price' => 229000, 'stock' => 8],
                    ['sku' => 'LL-LBR-36-SGE', 'size' => '3-6M', 'color' => 'Sage', 'color_hex' => '#b8b098', 'price' => 229000, 'stock' => 7],
                    ['sku' => 'LL-LBR-69-SGE', 'size' => '6-9M', 'color' => 'Sage', 'color_hex' => '#b8b098', 'price' => 239000, 'stock' => 0],
                ],
            ],
            [
                'category' => 'sets',
                'name' => 'Morning Set',
                'slug' => 'morning-set',
                'description' => 'A soft top and bloomer set made for simple dressing and comfortable gifting.',
                'materials' => 'Textured cotton gauze, elastic waist with soft finishing.',
                'care_instructions' => 'Hand wash or gentle machine wash. Do not bleach.',
                'base_price' => 279000,
                'is_featured' => true,
                'image' => '/images/lette/stitch-product-set.jpg',
                'variants' => [
                    ['sku' => 'LL-MST-36-CRM', 'size' => '3-6M', 'color' => 'Cream', 'color_hex' => '#f0dfca', 'price' => 279000, 'stock' => 5],
                    ['sku' => 'LL-MST-69-CLY', 'size' => '6-9M', 'color' => 'Clay', 'color_hex' => '#c8ad8d', 'price' => 289000, 'stock' => 4],
                    ['sku' => 'LL-MST-912-CLY', 'size' => '9-12M', 'color' => 'Clay', 'color_hex' => '#c8ad8d', 'price' => 289000, 'stock' => 2],
                ],
            ],
            [
                'category' => 'dresses',
                'name' => 'Luna Dress',
                'slug' => 'luna-dress',
                'description' => 'A softly structured dress for family photos, first birthdays, and warm afternoons.',
                'materials' => 'Cotton linen blend with cotton lining.',
                'care_instructions' => 'Machine wash cold. Steam lightly if needed.',
                'base_price' => 259000,
                'is_featured' => false,
                'image' => '/images/lette/stitch-product-luna.jpg',
                'variants' => [
                    ['sku' => 'LL-LDR-36-MLK', 'size' => '3-6M', 'color' => 'Milk', 'color_hex' => '#f7efe4', 'price' => 259000, 'stock' => 6],
                    ['sku' => 'LL-LDR-69-STN', 'size' => '6-9M', 'color' => 'Stone', 'color_hex' => '#d6c9b7', 'price' => 269000, 'stock' => 5],
                ],
            ],
            [
                'category' => 'sleepwear',
                'name' => 'Cloud Sleep Set',
                'slug' => 'cloud-sleep-set',
                'description' => 'A bedtime set with a gentle stretch and a calm, cozy handfeel.',
                'materials' => 'Ribbed organic cotton with soft stretch.',
                'care_instructions' => 'Machine wash cold. Tumble dry low.',
                'base_price' => 279000,
                'is_featured' => false,
                'image' => '/images/lette/stitch-product-sleep.jpg',
                'variants' => [
                    ['sku' => 'LL-CSS-03-OAT', 'size' => '0-3M', 'color' => 'Oat', 'color_hex' => '#f8ecdd', 'price' => 279000, 'stock' => 4],
                    ['sku' => 'LL-CSS-36-MOC', 'size' => '3-6M', 'color' => 'Mocha', 'color_hex' => '#a89485', 'price' => 279000, 'stock' => 6],
                    ['sku' => 'LL-CSS-69-MOC', 'size' => '6-9M', 'color' => 'Mocha', 'color_hex' => '#a89485', 'price' => 289000, 'stock' => 3],
                ],
            ],
        ];

        foreach ($products as $productData) {
            /** @var Product $product */
            $product = Product::query()->updateOrCreate(
                ['slug' => $productData['slug']],
                [
                    'category_id' => $categories[$productData['category']]->id,
                    'name' => $productData['name'],
                    'description' => $productData['description'],
                    'materials' => $productData['materials'],
                    'care_instructions' => $productData['care_instructions'],
                    'base_price' => $productData['base_price'],
                    'is_active' => true,
                    'is_featured' => $productData['is_featured'],
                ]
            );

            $product->images()->updateOrCreate(
                ['path' => $productData['image']],
                [
                    'disk' => 'public',
                    'alt_text' => $productData['name'],
                    'sort_order' => 10,
                    'is_primary' => true,
                ]
            );

            foreach ($productData['variants'] as $variant) {
                $product->variants()->updateOrCreate(['sku' => $variant['sku']], [...$variant, 'is_active' => true]);
            }
        }
    }
}
