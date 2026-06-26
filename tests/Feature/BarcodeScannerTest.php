<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarcodeScannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_resolves_product_by_barcode_and_sku(): void
    {
        $this->seed();
        $user = User::where('email', 'cashier@pos.test')->firstOrFail();
        $product = Product::whereNotNull('barcode')->where('is_active', true)->firstOrFail();

        $this->actingAs($user)
            ->getJson(route('pos.products.resolve', [
                'barcode' => $product->barcode,
                'branch_id' => $user->branch_id,
            ]))
            ->assertOk()
            ->assertJson([
                'id' => $product->id,
                'barcode' => $product->barcode,
                'sku' => $product->sku,
            ])
            ->assertJsonPath('available_stock', 100);

        $this->getJson(route('pos.products.resolve', [
            'barcode' => $product->sku,
            'branch_id' => $user->branch_id,
        ]))
            ->assertOk()
            ->assertJsonPath('id', $product->id);
    }

    public function test_pos_searches_products_by_barcode_or_product_code(): void
    {
        $this->seed();
        $user = User::where('email', 'cashier@pos.test')->firstOrFail();
        $product = Product::whereNotNull('barcode')->where('is_active', true)->firstOrFail();

        $this->actingAs($user)
            ->getJson(route('pos.products', [
                'q' => $product->barcode,
                'branch_id' => $user->branch_id,
            ]))
            ->assertOk()
            ->assertJsonPath('0.id', $product->id);

        $this->getJson(route('pos.products', [
            'q' => $product->sku,
            'branch_id' => $user->branch_id,
        ]))
            ->assertOk()
            ->assertJsonPath('0.id', $product->id);

        $this->getJson(route('pos.products', [
            'q' => $product->name,
            'branch_id' => $user->branch_id,
        ]))
            ->assertOk()
            ->assertJsonPath('0.id', $product->id);
    }

    public function test_product_list_searches_by_name_or_partial_code(): void
    {
        $this->seed();
        $user = User::where('email', 'admin@pos.test')->firstOrFail();
        $product = Product::withoutGlobalScopes()->create([
            'company_id' => $user->company_id,
            'name' => 'Chocolate especial',
            'sku' => 'CHOCO-98765',
            'barcode' => '7701234567890',
            'unit' => 'unit',
            'product_type' => Product::TYPE_SIMPLE,
            'cost_price' => 2,
            'sale_price' => 4,
            'is_active' => true,
            'is_visible_ecommerce' => false,
        ]);

        $this->actingAs($user)
            ->get(route('products.index', ['q' => '34567']))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee($product->barcode);

        $this->actingAs($user)
            ->get(route('products.index', ['q' => 'ocolate']))
            ->assertOk()
            ->assertSee($product->name);

        $this->actingAs($user)
            ->get(route('products.index', ['q' => '9876']))
            ->assertOk()
            ->assertSee($product->name);
    }

    public function test_pos_rejects_unknown_or_out_of_stock_barcode(): void
    {
        $this->seed();
        $user = User::where('email', 'cashier@pos.test')->firstOrFail();
        $product = Product::whereNotNull('barcode')->where('is_active', true)->firstOrFail();

        Inventory::where('branch_id', $user->branch_id)
            ->where('product_id', $product->id)
            ->update(['stock' => 0]);

        $this->actingAs($user)
            ->getJson(route('pos.products.resolve', [
                'barcode' => $product->barcode,
                'branch_id' => $user->branch_id,
            ]))
            ->assertUnprocessable()
            ->assertJsonPath('message', 'El producto no tiene stock disponible en esta sucursal.');

        $this->getJson(route('pos.products.resolve', [
            'barcode' => 'CODIGO-INEXISTENTE',
            'branch_id' => $user->branch_id,
        ]))
            ->assertNotFound()
            ->assertJsonPath('message', 'No se encontro un producto con ese codigo.');
    }

    public function test_service_barcode_resolves_without_inventory(): void
    {
        $this->seed();
        $user = User::where('email', 'cashier@pos.test')->firstOrFail();
        $service = Product::create([
            'name' => 'Servicio escaneable',
            'sku' => 'SRV-SCAN-001',
            'barcode' => '7700000000001',
            'unit' => 'service',
            'product_type' => Product::TYPE_SERVICE,
            'cost_price' => 0,
            'sale_price' => 20,
            'is_active' => true,
            'is_visible_ecommerce' => false,
        ]);

        $this->actingAs($user)
            ->getJson(route('pos.products.resolve', [
                'barcode' => $service->barcode,
                'branch_id' => $user->branch_id,
            ]))
            ->assertOk()
            ->assertJson([
                'id' => $service->id,
                'tracks_inventory' => false,
                'product_type' => Product::TYPE_SERVICE,
            ]);
    }

    public function test_remote_scanner_delivers_barcode_to_pos_session(): void
    {
        $this->seed();
        $user = User::where('email', 'cashier@pos.test')->firstOrFail();

        $sessionResponse = $this->actingAs($user)
            ->postJson(route('pos.scanner.session'))
            ->assertOk()
            ->assertJsonStructure(['token', 'remote_url', 'expires_at']);

        $token = $sessionResponse->json('token');

        $this->postJson(route('pos.scanner.remote.scan', ['token' => $token]), [
            'barcode' => '7501000000011',
        ])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->getJson(route('pos.scanner.poll', ['token' => $token]))
            ->assertOk()
            ->assertJsonPath('events.0.barcode', '7501000000011');

        $this->getJson(route('pos.scanner.poll', ['token' => $token]))
            ->assertOk()
            ->assertJsonCount(0, 'events');
    }

    public function test_product_manager_can_create_remote_scanner_session(): void
    {
        $this->seed();
        $user = User::where('email', 'admin@pos.test')->firstOrFail();

        $this->actingAs($user)
            ->postJson(route('pos.scanner.session'))
            ->assertOk()
            ->assertJsonStructure(['token', 'remote_url', 'expires_at']);
    }

    public function test_product_remote_scanner_keeps_session_open_to_replace_wrong_code(): void
    {
        $script = file_get_contents(resource_path('js/product-barcode-scanner.js'));

        $this->assertStringContainsString(
            "applyBarcode(event.barcode, 'Codigo recibido del celular');",
            $script
        );
        $this->assertStringContainsString(
            'Puedes escanear otro para cambiarlo nuevamente.',
            $script
        );
        $this->assertStringNotContainsString(
            "applyBarcode(event.barcode, 'Codigo recibido del celular');\n            remoteStatus.textContent = `Codigo recibido: \${event.barcode}`;\n            clearRemoteSession();",
            $script
        );
    }

    public function test_remote_phone_scanner_stops_after_send_and_can_start_clean_replacement(): void
    {
        $script = file_get_contents(resource_path('js/remote-scanner.js'));

        $this->assertStringContainsString('stopCamera(false);', $script);
        $this->assertStringContainsString("lastCode = '';", $script);
        $this->assertStringContainsString(
            'Pulsa "Escanear otro codigo" para reemplazarlo.',
            $script
        );
    }

    public function test_pos_places_scanned_code_in_search_before_resolving_product(): void
    {
        $this->seed();
        $user = User::where('email', 'cashier@pos.test')->firstOrFail();

        $this->actingAs($user)
            ->get(route('pos.index'))
            ->assertOk()
            ->assertSee('const requestSequence = ++this.scannerRequestSequence;', false)
            ->assertSee('this.search = barcode;', false)
            ->assertSee('this.$refs.searchInput.value = barcode;', false)
            ->assertSee('requestSequence !== this.scannerRequestSequence', false)
            ->assertSee('this.restoreRemoteScannerSession();', false)
            ->assertSee('this.startRemoteScannerPolling();', false)
            ->assertSee('await this.fetchProducts();', false)
            ->assertSee('window.PosBarcodeCamera.start(', false)
            ->assertSee('window.PosBarcodeCamera?.stop();', false)
            ->assertSee('codigo ${barcode} encontrado. Agregado ${product.name}.', false);
    }
}
