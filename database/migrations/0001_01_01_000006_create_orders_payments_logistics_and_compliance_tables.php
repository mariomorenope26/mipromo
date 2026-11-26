<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('description')->nullable();
            $table->string('type', 20)->default('percent');
            $table->decimal('value', 10, 2);
            $table->decimal('min_order_amount', 10, 2)->default(0);
            $table->foreignId('institution_id')->nullable()->constrained('institutions')->nullOnDelete();
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('courier_name', 80);
            $table->string('scope', 30)->default('national');
            $table->decimal('rate', 10, 2)->default(0);
            $table->string('estimated_days', 50);
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 40)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('institution_id')->nullable()->constrained('institutions')->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 30);
            $table->string('customer_dni_ruc', 20)->nullable();
            $table->foreignId('shipping_zone_id')->nullable()->constrained('shipping_zones')->nullOnDelete();
            $table->string('shipping_address');
            $table->string('city', 100);
            $table->string('department', 100);
            $table->string('tracking_code', 80)->nullable();
            $table->string('courier_name', 80)->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->decimal('institution_commission_amount', 10, 2)->default(0);
            $table->string('coupon_code', 40)->nullable();
            $table->string('payment_status', 30)->default('pending')->index();
            $table->string('order_status', 30)->default('pending')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->string('product_name');
            $table->string('variant_label')->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->json('customization_data')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('method', 30);
            $table->decimal('amount', 10, 2);
            $table->boolean('is_partial_deposit')->default(false);
            $table->string('reference_code', 80)->nullable();
            $table->string('voucher_path')->nullable();
            $table->string('status', 30)->default('pending_review');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('document_type', 20)->default('boleta');
            $table->string('series', 10);
            $table->unsignedInteger('correlative');
            $table->string('customer_doc_type', 10);
            $table->string('customer_doc_number', 20);
            $table->string('customer_legal_name');
            $table->decimal('subtotal_gravado', 10, 2);
            $table->decimal('igv', 10, 2);
            $table->decimal('total', 10, 2);
            $table->string('sunat_status', 30)->default('accepted');
            $table->string('sunat_hash', 64)->nullable();
            $table->dateTime('issued_at');
            $table->timestamps();

            $table->unique(['series', 'correlative']);
        });

        Schema::create('content_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('reportable_type');
            $table->unsignedBigInteger('reportable_id');
            $table->string('reason');
            $table->text('details')->nullable();
            $table->string('status', 20)->default('open');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complaint_code', 40)->unique();
            $table->string('order_number', 40)->nullable();
            $table->string('customer_name');
            $table->string('customer_doc_number', 20);
            $table->string('customer_email');
            $table->string('customer_phone', 30);
            $table->string('type', 20)->default('reclamo');
            $table->string('subject');
            $table->text('detail');
            $table->text('request_detail');
            $table->string('status', 20)->default('pending');
            $table->text('response')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 80);
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->text('description');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('content_reports');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('shipping_zones');
        Schema::dropIfExists('coupons');
    }
};
