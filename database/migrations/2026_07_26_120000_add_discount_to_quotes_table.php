<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute la remise (fixe ou %) appliquée au solde total du devis.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('quotes', 'discount_type')) {
            Schema::table('quotes', function (Blueprint $table) {
                $table->enum('discount_type', ['fixed', 'percent'])->nullable()->after('accessories_cost');
                $table->decimal('discount_value', 10, 2)->default(0)->after('discount_type');
                $table->decimal('discount_amount', 10, 2)->default(0)->after('discount_value');
            });
        }
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value', 'discount_amount']);
        });
    }
};
