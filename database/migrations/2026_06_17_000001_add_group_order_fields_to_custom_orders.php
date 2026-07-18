<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Chaque colonne est ajoutée individuellement et de façon idempotente
        // (hasColumn) car une exécution précédente de cette migration a pu
        // s'arrêter en cours de route (voir plus bas) et laisser certaines
        // colonnes déjà en place.
        if (!Schema::hasColumn('custom_orders', 'is_group_order')) {
            Schema::table('custom_orders', function (Blueprint $table) {
                // Type de commande
                $table->boolean('is_group_order')->default(false)->after('reference');
            });
        }
        if (!Schema::hasColumn('custom_orders', 'group_name')) {
            Schema::table('custom_orders', function (Blueprint $table) {
                $table->string('group_name')->nullable()->after('is_group_order');
            });
        }
        if (!Schema::hasColumn('custom_orders', 'group_occasion')) {
            Schema::table('custom_orders', function (Blueprint $table) {
                $table->string('group_occasion')->nullable()->after('group_name');
            });
        }
        if (!Schema::hasColumn('custom_orders', 'group_members')) {
            Schema::table('custom_orders', function (Blueprint $table) {
                // Membres du groupe :
                // [{ id, type (homme|femme|enfant), nom, garments:[{garment_type, model_name, model_description, labor_cost, qty}], measurements:{...} }]
                $table->json('group_members')->nullable()->after('group_occasion');
            });
        }
        if (!Schema::hasColumn('custom_orders', 'model_photos')) {
            Schema::table('custom_orders', function (Blueprint $table) {
                // Photos multiples du modèle
                $table->json('model_photos')->nullable()->after('model_photo');
            });
        }

        // Rendre gender et garment_type nullable (non obligatoires en mode groupe).
        // On utilise du SQL brut plutôt que Blueprint::change() car ce dernier
        // nécessite le package doctrine/dbal (non installé sur ce projet) ---
        // c'est ce qui bloquait silencieusement cette migration (et donc
        // toutes les migrations suivantes, dont l'ajout de la colonne
        // `garments` à la table `quotes`) sur l'environnement de production.
        DB::statement("ALTER TABLE custom_orders MODIFY gender VARCHAR(255) NULL");
        DB::statement("ALTER TABLE custom_orders MODIFY garment_type VARCHAR(255) NULL");
    }

    public function down(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $columns = array_filter(
                ['is_group_order', 'group_name', 'group_occasion', 'group_members', 'model_photos'],
                fn ($col) => Schema::hasColumn('custom_orders', $col)
            );
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });

        DB::statement("ALTER TABLE custom_orders MODIFY gender ENUM('homme','femme','enfant') NOT NULL");
        DB::statement("ALTER TABLE custom_orders MODIFY garment_type ENUM('robe','costume','pantalon','chemise','boubou','ensemble','autre') NOT NULL");
    }
};
