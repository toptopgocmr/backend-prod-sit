<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute la colonne JSON `garments` à la table quotes.
     *
     * Structure d'un élément garment :
     * {
     *   "garment_type": "boubou",
     *   "model_name": "Boubou homme",
     *   "model_description": "...",
     *   "qty": 1,
     *   "fabrics": [
     *     {
     *       "mode": "stock|custom",
     *       "fabric_product_id": 3,       // mode stock
     *       "fabric_name": null,           // mode custom
     *       "fabric_price_per_meter": null,
     *       "fabric_meters": 3.5,
     *       "fabric_color": "blanc cassé",
     *       "fabric_cost": 17500
     *     },
     *     { ... }  // 2e tissu éventuel
     *   ]
     * }
     *
     * Les colonnes legacy (garment_type, model_name, fabric_product_id, etc.)
     * sont conservées pour la compatibilité avec les devis existants.
     * Pour les nouveaux devis, seul `garments` est renseigné et les colonnes
     * legacy sont NULL.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('quotes', 'garments')) {
            Schema::table('quotes', function (Blueprint $table) {
                $table->json('garments')->nullable()->after('model_photo');
            });
        }
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('garments');
        });
    }
};
