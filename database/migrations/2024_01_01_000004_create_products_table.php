<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['tissu','pret_a_porter','accessoire']);
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('reference')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['tissu','pret_a_porter','accessoire']);
            $table->enum('gender', ['homme','femme','enfant_fille','enfant_garcon','mixte'])->nullable();
            // Pour tissus
            $table->decimal('price_per_meter', 10, 2)->nullable();
            $table->decimal('available_meters', 8, 2)->nullable();
            $table->decimal('min_meters', 4, 2)->default(0.5)->nullable();
            // Pour prêt-à-porter
            $table->decimal('price', 10, 2)->nullable();
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->integer('alert_threshold')->default(5);
            // Commun
            $table->string('image')->nullable();
            $table->json('images')->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void {
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
