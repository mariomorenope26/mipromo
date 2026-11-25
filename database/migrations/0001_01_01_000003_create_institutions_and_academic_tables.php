<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_name', 60);
            $table->string('type', 30)->default('colegio');
            $table->string('modular_code', 30)->nullable();
            $table->string('city', 100);
            $table->string('region', 100);
            $table->string('address')->nullable();
            $table->unsignedSmallInteger('founded_year')->nullable();
            $table->string('motto')->nullable();
            $table->text('history')->nullable();
            $table->text('anthem_excerpt')->nullable();
            $table->string('primary_color', 20)->default('#1D70B8');
            $table->string('secondary_color', 20)->default('#0F2D4A');
            $table->string('logo_path')->nullable();
            $table->string('banner_path')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(10.00);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 40)->default('buyer')->index()->after('password');
            $table->foreignId('institution_id')->nullable()->after('role')->constrained('institutions')->nullOnDelete();
            $table->string('phone', 30)->nullable()->after('institution_id');
            $table->string('dni', 20)->nullable()->after('phone');
            $table->string('city', 100)->nullable()->after('dni');
            $table->boolean('is_active')->default(true)->after('city');
            $table->timestamp('privacy_consent_at')->nullable()->after('is_active');
        });

        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->string('name');
            $table->string('slug');
            $table->string('motto')->nullable();
            $table->foreignId('delegate_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['institution_id', 'year', 'slug']);
        });

        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained('promotions')->cascadeOnDelete();
            $table->string('grade', 60);
            $table->string('section', 40);
            $table->foreignId('delegate_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['promotion_id', 'grade', 'section']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classrooms');
        Schema::dropIfExists('promotions');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('institution_id');
            $table->dropColumn(['role', 'phone', 'dni', 'city', 'is_active', 'privacy_consent_at']);
        });
        Schema::dropIfExists('institutions');
    }
};
