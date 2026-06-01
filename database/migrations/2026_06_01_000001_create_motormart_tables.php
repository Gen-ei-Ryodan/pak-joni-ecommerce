<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo_path')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('motor_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::table('motors', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('id')->constrained('brands')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->after('brand_id')->constrained('motor_categories')->nullOnDelete();
            $table->decimal('price', 14, 2)->nullable()->after('year');
        });

        Schema::create('motor_colors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motor_id')->constrained('motors')->cascadeOnDelete();
            $table->string('name');
            $table->string('color_code', 7)->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('motor_specifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motor_id')->constrained('motors')->cascadeOnDelete();
            $table->string('group')->index();
            $table->string('key');
            $table->text('value');
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('motor_360_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motor_id')->constrained('motors')->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->string('type')->default('hero')->after('title')->index();
            $table->string('subtitle')->nullable()->after('type');
            $table->string('button_text')->nullable()->after('link_url');
        });

        Schema::create('product_highlights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motor_id')->constrained('motors')->cascadeOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('why_choose_us', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('thumbnail_path')->nullable();
            $table->longText('content');
            $table->string('author')->nullable();
            $table->string('category')->nullable()->index();
            $table->dateTime('publish_date')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('thumbnail_path')->nullable();
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('location')->nullable();
            $table->dateTime('event_date')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('event_galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('path');
            $table->string('type')->default('image');
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('csr_articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('thumbnail_path')->nullable();
            $table->longText('content');
            $table->json('documentation')->nullable();
            $table->dateTime('publish_date')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('careers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->longText('requirements')->nullable();
            $table->dateTime('publish_date')->nullable()->index();
            $table->string('status')->default('active')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('internal_activities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('thumbnail_path')->nullable();
            $table->longText('content')->nullable();
            $table->dateTime('publish_date')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('internal_activity_galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internal_activity_id')->constrained('internal_activities')->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('dealers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address');
            $table->string('city')->index();
            $table->string('province')->index();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('google_maps_url')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('price_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motor_id')->constrained('motors')->cascadeOnDelete();
            $table->string('name');
            $table->string('pdf_path');
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('part_catalogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motor_id')->constrained('motors')->cascadeOnDelete();
            $table->string('name');
            $table->string('pdf_path');
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('quotation_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message');
            $table->boolean('is_read')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->timestamps();
        });

        Schema::create('footer_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('footer_settings');
        Schema::dropIfExists('company_profiles');
        Schema::dropIfExists('quotation_requests');
        Schema::dropIfExists('part_catalogs');
        Schema::dropIfExists('price_lists');
        Schema::dropIfExists('dealers');
        Schema::dropIfExists('internal_activity_galleries');
        Schema::dropIfExists('internal_activities');
        Schema::dropIfExists('careers');
        Schema::dropIfExists('csr_articles');
        Schema::dropIfExists('event_galleries');
        Schema::dropIfExists('events');
        Schema::dropIfExists('news');
        Schema::dropIfExists('why_choose_us');
        Schema::dropIfExists('product_highlights');
        Schema::dropIfExists('motor_360_images');
        Schema::dropIfExists('motor_specifications');
        Schema::dropIfExists('motor_colors');
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['type', 'subtitle', 'button_text']);
        });
        Schema::table('motors', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropForeign(['category_id']);
            $table->dropColumn(['brand_id', 'category_id', 'price']);
        });
        Schema::dropIfExists('motor_categories');
        Schema::dropIfExists('brands');
    }
};
