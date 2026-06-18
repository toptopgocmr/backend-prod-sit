<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Permet de saisir un tissu "hors stock" (non référencé dans products)
        // avec son propre nom et son propre prix au mètre.
        // Utilisé uniquement quand fabric_product_id est vide.
        if (!Schema::hasColumn('quotes', 'fabric_name')) {
            Schema::table('quotes', function (Blueprint $table) {
                $table->string('fabric_name')->nullable()->after('fabric_product_id');
                $table->decimal('fabric_price_per_meter', 10, 2)->nullable()->after('fabric_name');
            });
        }
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn(['fabric_name', 'fabric_price_per_meter']);
        });
    }
};
