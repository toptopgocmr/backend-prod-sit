<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            // Type de commande
            $table->boolean('is_group_order')->default(false)->after('reference');
            $table->string('group_name')->nullable()->after('is_group_order');
            $table->string('group_occasion')->nullable()->after('group_name');

            // Membres du groupe :
            // [{ id, type (homme|femme|enfant), nom, garments:[{garment_type, model_name, model_description, labor_cost, qty}], measurements:{...} }]
            $table->json('group_members')->nullable()->after('group_occasion');

            // Photos multiples du modèle
            $table->json('model_photos')->nullable()->after('model_photo');

            // Rendre gender et garment_type nullable (non obligatoires en mode groupe)
            $table->string('gender')->nullable()->change();
            $table->string('garment_type')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->dropColumn(['is_group_order', 'group_name', 'group_occasion', 'group_members', 'model_photos']);
            $table->enum('gender', ['homme','femme','enfant'])->nullable(false)->change();
            $table->enum('garment_type', ['robe','costume','pantalon','chemise','boubou','ensemble','autre'])->nullable(false)->change();
        });
    }
};
