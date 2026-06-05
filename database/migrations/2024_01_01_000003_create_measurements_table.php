<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('label')->default('Standard');
            $table->decimal('poitrine', 5, 1)->nullable();
            $table->decimal('taille', 5, 1)->nullable();
            $table->decimal('hanches', 5, 1)->nullable();
            $table->decimal('longueur_pantalon', 5, 1)->nullable();
            $table->decimal('longueur_manche', 5, 1)->nullable();
            $table->decimal('longueur_robe', 5, 1)->nullable();
            $table->decimal('cou', 5, 1)->nullable();
            $table->decimal('epaules', 5, 1)->nullable();
            $table->decimal('entrejambe', 5, 1)->nullable();
            $table->decimal('bras', 5, 1)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('measurements'); }
};
