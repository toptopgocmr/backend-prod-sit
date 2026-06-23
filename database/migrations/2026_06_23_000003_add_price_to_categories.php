<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Prix par défaut pour les produits de cette catégorie
            // Tissu  → prix au mètre ; Accessoire / Prêt-à-porter → prix unitaire
            $table->decimal('price', 10, 2)->default(0)->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
