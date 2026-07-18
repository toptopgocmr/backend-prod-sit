<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Ajouter la colonne JSON "values" à measurements ──
        // Le formulaire envoie new_measurement[f_tour_poitrine] etc.
        // On les stocke dans un JSON flexible plutôt que des colonnes fixes
        if (!Schema::hasColumn('measurements', 'values')) {
            Schema::table('measurements', function (Blueprint $table) {
                $table->json('values')->nullable()->after('label');
            });
        }

        // ── 2. Table devis (quotes) ──
        // Gardée par hasTable() : une exécution précédente de cette migration
        // a pu créer la table puis échouer plus loin (étape 3 ci-dessous),
        // ce qui la faisait replanter à chaque déploiement avec
        // "Table 'quotes' already exists" et bloquait donc TOUTES les
        // migrations suivantes (dont l'ajout de la colonne `garments`).
        if (!Schema::hasTable('quotes')) {
            Schema::create('quotes', function (Blueprint $table) {
                $table->id();
                $table->string('reference')->unique();
                $table->foreignId('client_id')->constrained()->restrictOnDelete();
                $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
                $table->enum('gender', ['homme', 'femme', 'enfant'])->nullable();
                $table->string('garment_type')->nullable();
                $table->string('model_name')->nullable();
                $table->text('model_description')->nullable();
                $table->string('model_photo')->nullable();
                // Tissu
                $table->foreignId('fabric_product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->decimal('fabric_meters', 6, 2)->nullable();
                $table->string('fabric_color')->nullable();
                $table->decimal('fabric_cost', 10, 2)->default(0);
                // Coûts
                $table->decimal('labor_cost', 10, 2)->default(0);
                $table->decimal('accessories_cost', 10, 2)->default(0);
                $table->json('accessories')->nullable();
                $table->decimal('total', 10, 2)->default(0);
                // Statut devis
                $table->enum('status', ['brouillon', 'envoye', 'accepte', 'refuse', 'expire'])->default('brouillon');
                $table->date('valid_until')->nullable();    // date d'expiration du devis
                $table->date('delivery_date')->nullable();
                $table->text('notes')->nullable();
                // Si converti en commande
                $table->foreignId('custom_order_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ── 3. Lier les bons de commande achat aux dépenses finance ──
        if (!Schema::hasColumn('purchase_orders', 'expense_id')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete()->after('status');
                $table->enum('payment_method', ['cash', 'mobile_money', 'virement', 'credit'])->default('cash')->after('expense_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
        Schema::table('measurements', function (Blueprint $table) {
            $table->dropColumn('values');
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['expense_id']);
            $table->dropColumn(['expense_id', 'payment_method']);
        });
    }
};
