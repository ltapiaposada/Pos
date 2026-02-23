<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('order_source', 20)->default('pos')->after('status')->index();
            $table->decimal('shipping_total', 12, 2)->default(0)->after('tax_total');
            $table->decimal('coupon_discount_total', 12, 2)->default(0)->after('shipping_total');
            $table->string('coupon_code', 50)->nullable()->after('coupon_discount_total');
            $table->string('delivery_address')->nullable()->after('coupon_code');
            $table->string('customer_note')->nullable()->after('delivery_address');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'order_source',
                'shipping_total',
                'coupon_discount_total',
                'coupon_code',
                'delivery_address',
                'customer_note',
            ]);
        });
    }
};
