<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('business_name');
            $table->string('ruc', 20)->nullable();
            $table->string('slug')->unique();
            $table->string('city', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->decimal('commission_rate', 5, 2)->default(12.00);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->string('icon', 50)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->nullable()->constrained('institutions')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku_prefix', 30);
            $table->string('short_description')->nullable();
            $table->text('description');
            $table->decimal('base_price', 10, 2);
            $table->decimal('member_price', 10, 2)->nullable();
            $table->boolean('is_customizable')->default(true);
            $table->boolean('is_preorder_only')->default(false);
            $table->string('image_path')->nullable();
            $table->json('gallery')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->string('size', 20)->nullable();
            $table->string('color', 50)->nullable();
            $table->string('color_hex', 20)->nullable();
            $table->string('material', 80)->nullable();
            $table->decimal('price_adjustment', 10, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->unsignedInteger('low_stock_threshold')->default(5);
            $table->timestamps();
        });

        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment');
            $table->boolean('is_approved')->default(true);
            $table->timestamps();
        });

        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'product_id']);
        });

        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('promotion_id')->nullable()->constrained('promotions')->nullOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->decimal('unit_price', 10, 2);
            $table->unsignedTinyInteger('deposit_percent')->default(50);
            $table->unsignedInteger('target_units');
            $table->unsignedInteger('current_units')->default(0);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at');
            $table->string('delivery_mode', 30)->default('consolidated_delegate');
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();
        });

        Schema::create('campaign_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->string('participant_name');
            $table->string('participant_phone', 30)->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('custom_name', 80)->nullable();
            $table->string('custom_year', 20)->nullable();
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('payment_status', 30)->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_participants');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('product_reviews');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('vendors');
    }
};
