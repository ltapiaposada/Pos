<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tax;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categoryBebidasId = Category::query()->where('name', 'Bebidas')->value('id');
        $categorySnacksId = Category::query()->where('name', 'Snacks')->value('id');
        $taxIvaId = Tax::query()->where('name', 'IVA 16%')->value('id');
        $taxExentoId = Tax::query()->where('name', 'Exento 0%')->value('id');

        $rows = [
            [
                'category_id' => $categoryBebidasId,
                'tax_id' => $taxIvaId,
                'name' => 'Refresco Cola 600ml',
                'sku' => 'BEB-0001',
                'barcode' => '7501000000011',
                'image_url' => '/images/products/cola.svg',
                'description' => 'Bebida gaseosa sabor cola',
                'unit' => 'unit',
                'product_type' => Product::TYPE_SIMPLE,
                'cost_price' => 0.60,
                'sale_price' => 1.20,
                'is_active' => true,
                'is_visible_ecommerce' => true,
            ],
            [
                'category_id' => $categoryBebidasId,
                'tax_id' => $taxExentoId,
                'name' => 'Agua 600ml',
                'sku' => 'BEB-0002',
                'barcode' => '7501000000028',
                'image_url' => '/images/products/agua.svg',
                'description' => 'Agua purificada sin gas',
                'unit' => 'unit',
                'product_type' => Product::TYPE_SIMPLE,
                'cost_price' => 0.40,
                'sale_price' => 0.90,
                'is_active' => true,
                'is_visible_ecommerce' => true,
            ],
            [
                'category_id' => $categoryBebidasId,
                'tax_id' => $taxIvaId,
                'name' => 'Cafe Molido Premium 250g',
                'sku' => 'BEB-0003',
                'barcode' => '7501000000035',
                'image_url' => '/images/products/cafe.svg',
                'description' => 'Cafe tostado molido premium',
                'unit' => 'unit',
                'product_type' => Product::TYPE_SIMPLE,
                'cost_price' => 2.40,
                'sale_price' => 4.80,
                'is_active' => true,
                'is_visible_ecommerce' => true,
            ],
            [
                'category_id' => $categorySnacksId,
                'tax_id' => $taxIvaId,
                'name' => 'Papas Fritas 150g',
                'sku' => 'SNK-0001',
                'barcode' => '7501000000042',
                'image_url' => '/images/products/papas.svg',
                'description' => 'Snack salado crujiente',
                'unit' => 'unit',
                'product_type' => Product::TYPE_SIMPLE,
                'cost_price' => 0.70,
                'sale_price' => 1.50,
                'is_active' => true,
                'is_visible_ecommerce' => true,
            ],
            [
                'category_id' => $categorySnacksId,
                'tax_id' => $taxIvaId,
                'name' => 'Galletas Chocolate 120g',
                'sku' => 'SNK-0002',
                'barcode' => '7501000000059',
                'image_url' => '/images/products/galletas.svg',
                'description' => 'Galletas rellenas de chocolate',
                'unit' => 'unit',
                'product_type' => Product::TYPE_SIMPLE,
                'cost_price' => 0.90,
                'sale_price' => 1.90,
                'is_active' => true,
                'is_visible_ecommerce' => true,
            ],
            [
                'category_id' => $categorySnacksId,
                'tax_id' => $taxIvaId,
                'name' => 'Barra de Chocolate 90g',
                'sku' => 'SNK-0003',
                'barcode' => '7501000000066',
                'image_url' => '/images/products/chocolate.svg',
                'description' => 'Chocolate semiamargo',
                'unit' => 'unit',
                'product_type' => Product::TYPE_SIMPLE,
                'cost_price' => 0.80,
                'sale_price' => 1.70,
                'is_active' => true,
                'is_visible_ecommerce' => true,
            ],
        ];

        foreach ($rows as $row) {
            Product::query()->updateOrCreate(
                ['sku' => $row['sku']],
                $row
            );
        }
    }
}
