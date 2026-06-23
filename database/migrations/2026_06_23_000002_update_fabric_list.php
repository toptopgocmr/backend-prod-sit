<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Mettre à jour la liste des tissus disponibles en stock (sur mesure).
     * Remplace les anciens libellés par la liste officielle demandée.
     */
    public function up(): void
    {
        // Liste officielle des tissus GSIT
        $officialFabrics = [
            'Jean',
            'Cashmire super 220',
            'ADZIRI',
            'Raphia',
            'Pagne petite pièce',
            'Pagne wax',
            'Pagne woodin',
            'Super 100',
            'Crèpe',
            'Lin simple',
            'Lin haut gamme',
            'Pagne ouest-africain',
        ];

        // Récupérer ou créer la catégorie "Tissus"
        $categoryId = DB::table('categories')->where('name', 'Tissus')->value('id');
        if (!$categoryId) {
            $categoryId = DB::table('categories')->insertGetId([
                'name'       => 'Tissus',
                'slug'       => 'tissus',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Désactiver les anciens tissus qui ne sont pas dans la liste officielle
        DB::table('products')
            ->where('type', 'tissu')
            ->whereNotIn('name', $officialFabrics)
            ->update(['is_active' => false, 'updated_at' => now()]);

        // Ajouter ou réactiver chaque tissu officiel
        foreach ($officialFabrics as $index => $fabricName) {
            $reference = 'TIS-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);

            $existing = DB::table('products')
                ->where('type', 'tissu')
                ->where('name', $fabricName)
                ->first();

            if ($existing) {
                // Réactiver s'il existait déjà
                DB::table('products')
                    ->where('id', $existing->id)
                    ->update(['is_active' => true, 'updated_at' => now()]);
            } else {
                // Créer le nouveau tissu
                $existingRef = DB::table('products')->where('reference', $reference)->exists();
                if ($existingRef) {
                    $reference = 'TIS-' . strtoupper(substr(str_replace(' ', '', $fabricName), 0, 6));
                }

                DB::table('products')->insert([
                    'name'              => $fabricName,
                    'reference'         => $reference,
                    'type'              => 'tissu',
                    'category_id'       => $categoryId,
                    'description'       => 'Tissu ' . $fabricName,
                    'available_meters'  => 0,
                    'alert_threshold'   => 5,
                    'cost_price'        => 0,
                    'price_per_meter'   => 0,
                    'is_active'         => true,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Réactiver tous les anciens tissus (rollback basique)
        DB::table('products')->where('type', 'tissu')->update(['is_active' => true]);
    }
};
