<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table): void {
            $table->string('trade_name')->nullable()->after('business_name');
            $table->string('contact_name', 120)->nullable()->after('phone');
            $table->string('email', 150)->nullable()->after('contact_name');
            $table->string('address')->nullable()->after('city');
            $table->string('category_specialty', 120)->nullable()->after('description');
            $table->string('payment_terms', 80)->default('50% adelanto / 50% contraentrega')->after('commission_rate');
            $table->string('bank_name', 80)->nullable()->after('payment_terms');
            $table->string('bank_account', 80)->nullable()->after('bank_name');
            $table->string('bank_cci', 80)->nullable()->after('bank_account');
            $table->unsignedSmallInteger('lead_time_days')->default(7)->after('bank_cci');
            $table->decimal('rating', 3, 2)->default(4.90)->after('lead_time_days');
            $table->boolean('is_verified')->default(false)->after('is_active');
            $table->timestamp('verified_at')->nullable()->after('is_verified');
            $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
            $table->text('internal_notes')->nullable()->after('verified_by');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->decimal('purchase_price', 10, 2)->default(0)->after('description');
            $table->boolean('show_verified_vendor')->default(true)->after('is_preorder_only');
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->decimal('unit_cost', 10, 2)->default(0)->after('unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropColumn('unit_cost');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['purchase_price', 'show_verified_vendor']);
        });

        Schema::table('vendors', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('verified_by');
            $table->dropColumn([
                'trade_name',
                'contact_name',
                'email',
                'address',
                'category_specialty',
                'payment_terms',
                'bank_name',
                'bank_account',
                'bank_cci',
                'lead_time_days',
                'rating',
                'is_verified',
                'verified_at',
                'internal_notes',
            ]);
        });
    }
};
