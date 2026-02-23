<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Tax;
use App\Support\StorefrontCache;
use Illuminate\Database\Seeder;

class ProductTypeDemoSeeder extends Seeder
{
    public function run(): void
    {
        $snacksCategoryId = Category::query()->where('name', 'Snacks')->value('id');
        $beveragesCategoryId = Category::query()->where('name', 'Bebidas')->value('id');
        $ivaTaxId = Tax::query()->where('name', 'IVA 16%')->value('id');

        // Parent/base products for variants (hidden from storefront)
        $baseTshirt = Product::query()->updateOrCreate(
            ['sku' => 'BAS-TSHIRT'],
            [
                'category_id' => $snacksCategoryId,
                'tax_id' => $ivaTaxId,
                'name' => 'Camiseta deportiva base',
                'barcode' => '7501000001001',
                'image_url' => '/images/product-placeholder.svg',
                'description' => 'Producto base para variantes de talla.',
                'unit' => 'unit',
                'product_type' => Product::TYPE_SIMPLE,
                'parent_product_id' => null,
                'cost_price' => 9.50,
                'sale_price' => 16.00,
                'is_active' => true,
                'is_visible_ecommerce' => false,
            ]
        );

        $baseCoffee = Product::query()->updateOrCreate(
            ['sku' => 'BAS-COFFEE'],
            [
                'category_id' => $beveragesCategoryId,
                'tax_id' => $ivaTaxId,
                'name' => 'Cafe base origen',
                'barcode' => '7501000001002',
                'image_url' => '/images/products/cafe.svg',
                'description' => 'Producto base para variantes de molienda.',
                'unit' => 'unit',
                'product_type' => Product::TYPE_SIMPLE,
                'parent_product_id' => null,
                'cost_price' => 4.20,
                'sale_price' => 7.50,
                'is_active' => true,
                'is_visible_ecommerce' => false,
            ]
        );

        // Variant examples
        $variantProducts = collect([
            Product::query()->updateOrCreate(
                ['sku' => 'VAR-TSHIRT-S'],
                [
                    'category_id' => $snacksCategoryId,
                    'tax_id' => $ivaTaxId,
                    'name' => 'Camiseta deportiva talla S',
                    'barcode' => '7501000001010',
                    'image_url' => '/images/product-placeholder.svg',
                    'description' => 'Variante talla S.',
                    'unit' => 'unit',
                    'product_type' => Product::TYPE_VARIANT,
                    'parent_product_id' => $baseTshirt->id,
                    'cost_price' => 9.60,
                    'sale_price' => 17.50,
                    'is_active' => true,
                    'is_visible_ecommerce' => true,
                ]
            ),
            Product::query()->updateOrCreate(
                ['sku' => 'VAR-TSHIRT-M'],
                [
                    'category_id' => $snacksCategoryId,
                    'tax_id' => $ivaTaxId,
                    'name' => 'Camiseta deportiva talla M',
                    'barcode' => '7501000001011',
                    'image_url' => '/images/product-placeholder.svg',
                    'description' => 'Variante talla M.',
                    'unit' => 'unit',
                    'product_type' => Product::TYPE_VARIANT,
                    'parent_product_id' => $baseTshirt->id,
                    'cost_price' => 9.80,
                    'sale_price' => 17.90,
                    'is_active' => true,
                    'is_visible_ecommerce' => true,
                ]
            ),
            Product::query()->updateOrCreate(
                ['sku' => 'VAR-TSHIRT-L'],
                [
                    'category_id' => $snacksCategoryId,
                    'tax_id' => $ivaTaxId,
                    'name' => 'Camiseta deportiva talla L',
                    'barcode' => '7501000001013',
                    'image_url' => '/images/product-placeholder.svg',
                    'description' => 'Variante talla L.',
                    'unit' => 'unit',
                    'product_type' => Product::TYPE_VARIANT,
                    'parent_product_id' => $baseTshirt->id,
                    'cost_price' => 10.00,
                    'sale_price' => 18.20,
                    'is_active' => true,
                    'is_visible_ecommerce' => true,
                ]
            ),
            Product::query()->updateOrCreate(
                ['sku' => 'VAR-TSHIRT-XL'],
                [
                    'category_id' => $snacksCategoryId,
                    'tax_id' => $ivaTaxId,
                    'name' => 'Camiseta deportiva talla XL',
                    'barcode' => '7501000001014',
                    'image_url' => '/images/product-placeholder.svg',
                    'description' => 'Variante talla XL.',
                    'unit' => 'unit',
                    'product_type' => Product::TYPE_VARIANT,
                    'parent_product_id' => $baseTshirt->id,
                    'cost_price' => 10.20,
                    'sale_price' => 18.60,
                    'is_active' => true,
                    'is_visible_ecommerce' => true,
                ]
            ),
            Product::query()->updateOrCreate(
                ['sku' => 'VAR-COFFEE-GRANO'],
                [
                    'category_id' => $beveragesCategoryId,
                    'tax_id' => $ivaTaxId,
                    'name' => 'Cafe origen en grano 500g',
                    'barcode' => '7501000001012',
                    'image_url' => '/images/products/cafe.svg',
                    'description' => 'Variante en grano.',
                    'unit' => 'unit',
                    'product_type' => Product::TYPE_VARIANT,
                    'parent_product_id' => $baseCoffee->id,
                    'cost_price' => 4.50,
                    'sale_price' => 8.20,
                    'is_active' => true,
                    'is_visible_ecommerce' => true,
                ]
            ),
            Product::query()->updateOrCreate(
                ['sku' => 'VAR-COFFEE-MOLIDO'],
                [
                    'category_id' => $beveragesCategoryId,
                    'tax_id' => $ivaTaxId,
                    'name' => 'Cafe origen molido 500g',
                    'barcode' => '7501000001015',
                    'image_url' => '/images/products/cafe.svg',
                    'description' => 'Variante molido tradicional.',
                    'unit' => 'unit',
                    'product_type' => Product::TYPE_VARIANT,
                    'parent_product_id' => $baseCoffee->id,
                    'cost_price' => 4.40,
                    'sale_price' => 8.10,
                    'is_active' => true,
                    'is_visible_ecommerce' => true,
                ]
            ),
            Product::query()->updateOrCreate(
                ['sku' => 'VAR-COFFEE-DESCAFEINADO'],
                [
                    'category_id' => $beveragesCategoryId,
                    'tax_id' => $ivaTaxId,
                    'name' => 'Cafe descafeinado 500g',
                    'barcode' => '7501000001016',
                    'image_url' => '/images/products/cafe.svg',
                    'description' => 'Variante descafeinado.',
                    'unit' => 'unit',
                    'product_type' => Product::TYPE_VARIANT,
                    'parent_product_id' => $baseCoffee->id,
                    'cost_price' => 4.70,
                    'sale_price' => 8.60,
                    'is_active' => true,
                    'is_visible_ecommerce' => true,
                ]
            ),
        ]);

        // Two composite/kit examples
        $kitOne = Product::query()->updateOrCreate(
            ['sku' => 'KIT-HIDRA'],
            [
                'category_id' => $beveragesCategoryId,
                'tax_id' => $ivaTaxId,
                'name' => 'Kit hidratacion',
                'barcode' => '7501000001021',
                'image_url' => '/images/products/agua.svg',
                'description' => 'Incluye 1 agua + 1 refresco.',
                'unit' => 'unit',
                'product_type' => Product::TYPE_KIT,
                'parent_product_id' => null,
                'cost_price' => 0,
                'sale_price' => 1.95,
                'is_active' => true,
                'is_visible_ecommerce' => true,
            ]
        );

        $kitTwo = Product::query()->updateOrCreate(
            ['sku' => 'KIT-SNACK'],
            [
                'category_id' => $snacksCategoryId,
                'tax_id' => $ivaTaxId,
                'name' => 'Kit snack dulce',
                'barcode' => '7501000001022',
                'image_url' => '/images/products/galletas.svg',
                'description' => 'Incluye galletas + barra de chocolate.',
                'unit' => 'unit',
                'product_type' => Product::TYPE_KIT,
                'parent_product_id' => null,
                'cost_price' => 0,
                'sale_price' => 3.30,
                'is_active' => true,
                'is_visible_ecommerce' => true,
            ]
        );

        $waterId = Product::query()->where('sku', 'BEB-0002')->value('id');
        $colaId = Product::query()->where('sku', 'BEB-0001')->value('id');
        $cookiesId = Product::query()->where('sku', 'SNK-0002')->value('id');
        $chocoId = Product::query()->where('sku', 'SNK-0003')->value('id');

        if ($waterId && $colaId) {
            $kitOne->kitItems()->delete();
            $kitOne->kitItems()->createMany([
                ['component_product_id' => $waterId, 'quantity' => 1],
                ['component_product_id' => $colaId, 'quantity' => 1],
            ]);
        }

        if ($cookiesId && $chocoId) {
            $kitTwo->kitItems()->delete();
            $kitTwo->kitItems()->createMany([
                ['component_product_id' => $cookiesId, 'quantity' => 1],
                ['component_product_id' => $chocoId, 'quantity' => 1],
            ]);
        }

        $branches = Branch::query()->pluck('id');
        $inventoryProducts = collect([
            $baseTshirt->id,
            $baseCoffee->id,
        ])->merge($variantProducts->pluck('id'))->unique()->values();

        foreach ($branches as $branchId) {
            foreach ($inventoryProducts as $productId) {
                Inventory::query()->updateOrCreate(
                    ['branch_id' => $branchId, 'product_id' => $productId],
                    ['stock' => 30, 'min_stock' => 5]
                );
            }
        }

        StorefrontCache::bumpProductsVersion();
    }
}
