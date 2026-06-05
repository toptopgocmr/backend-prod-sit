<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Client;
use App\Models\Category;
use App\Models\Product;
use App\Models\ExpenseCategory;
use App\Models\Equipment;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Utilisateurs ───────────────────────────────────────────
        User::create([
            'name'      => 'Administrateur',
            'email'     => 'admin@gsit.art',
            'password'  => Hash::make('gsit2026'),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        User::create([
            'name'      => 'Marie Couturière',
            'email'     => 'marie@gsit.art',
            'password'  => Hash::make('gsit2026'),
            'role'      => 'couturier',
            'is_active' => true,
        ]);

        User::create([
            'name'      => 'Jean Stock',
            'email'     => 'jean@gsit.art',
            'password'  => Hash::make('gsit2026'),
            'role'      => 'stock_manager',
            'is_active' => true,
        ]);

        User::create([
            'name'      => 'Sophie Caisse',
            'email'     => 'sophie@gsit.art',
            'password'  => Hash::make('gsit2026'),
            'role'      => 'cashier',
            'is_active' => true,
        ]);

        User::create([
            'name'      => 'Pierre Livreur',
            'email'     => 'pierre@gsit.art',
            'password'  => Hash::make('gsit2026'),
            'role'      => 'delivery',
            'is_active' => true,
        ]);

        // ─── Catégories ─────────────────────────────────────────────
        $categories = [
            ['name' => 'Pagne Africain',      'slug' => 'pagne-africain',    'type' => 'tissu'],
            ['name' => 'Jean Denim',           'slug' => 'jean-denim',         'type' => 'tissu'],
            ['name' => 'Lin',                  'slug' => 'lin',                'type' => 'tissu'],
            ['name' => 'Wax',                  'slug' => 'wax',                'type' => 'tissu'],
            ['name' => 'Coton',                'slug' => 'coton',              'type' => 'tissu'],
            ['name' => 'Prêt-à-porter Homme',  'slug' => 'pap-homme',          'type' => 'pret_a_porter'],
            ['name' => 'Prêt-à-porter Femme',  'slug' => 'pap-femme',          'type' => 'pret_a_porter'],
            ['name' => 'Prêt-à-porter Enfant', 'slug' => 'pap-enfant',         'type' => 'pret_a_porter'],
            ['name' => 'Boutons & Fermetures', 'slug' => 'boutons',            'type' => 'accessoire'],
            ['name' => 'Fils & Aiguilles',     'slug' => 'fils',               'type' => 'accessoire'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // ─── Produits exemple ───────────────────────────────────────
        Product::create([
            'category_id'      => 1,
            'name'             => 'Pagne Batik Premium',
            'type'             => 'tissu',
            'price_per_meter'  => 3500,
            'available_meters' => 45.5,
            'alert_threshold'  => 5,
            'cost_price'       => 2500,
            'is_active'        => true,
        ]);

        Product::create([
            'category_id'      => 2,
            'name'             => 'Jean Stretch Bleu',
            'type'             => 'tissu',
            'price_per_meter'  => 4500,
            'available_meters' => 30,
            'alert_threshold'  => 5,
            'cost_price'       => 3200,
            'is_active'        => true,
        ]);

        Product::create([
            'category_id'     => 6,
            'name'            => 'Chemise Homme Classique',
            'type'            => 'pret_a_porter',
            'gender'          => 'homme',
            'price'           => 15000,
            'stock_quantity'  => 12,
            'size'            => 'M',
            'alert_threshold' => 3,
            'cost_price'      => 8500,
            'is_active'       => true,
        ]);

        // ─── Clients exemple ────────────────────────────────────────
        Client::create([
            'first_name' => 'Isabelle',
            'last_name'  => 'Moukala',
            'phone'      => '+242 06 000 0001',
            'email'      => 'isabelle@example.com',
            'city'       => 'Brazzaville',
            'gender'     => 'femme',
        ]);

        Client::create([
            'first_name' => 'Patrick',
            'last_name'  => 'Bouanga',
            'phone'      => '+242 06 000 0002',
            'city'       => 'Brazzaville',
            'gender'     => 'homme',
        ]);

        // ─── Catégories dépenses ────────────────────────────────────
        $expCats = [
            ['name' => 'Achat tissus',       'type' => 'achat',       'color' => '#3B82F6'],
            ['name' => 'Achat accessoires',  'type' => 'achat',       'color' => '#8B5CF6'],
            ['name' => 'Salaires',           'type' => 'salaire',     'color' => '#F59E0B'],
            ['name' => 'Transport',          'type' => 'transport',   'color' => '#10B981'],
            ['name' => 'Électricité',        'type' => 'charge',      'color' => '#F97316'],
            ['name' => 'Loyer',              'type' => 'charge',      'color' => '#EF4444'],
            ['name' => 'Maintenance',        'type' => 'maintenance', 'color' => '#6B7280'],
            ['name' => 'Divers',             'type' => 'autre',       'color' => '#9CA3AF'],
        ];

        foreach ($expCats as $cat) {
            ExpenseCategory::create($cat);
        }

        // ─── Équipements ────────────────────────────────────────────
        $equipment = [
            ['name' => 'Machine Singer Pro 1', 'type' => 'machine_a_coudre',  'brand' => 'Singer', 'status' => 'operationnel', 'location' => 'Atelier A'],
            ['name' => 'Machine Singer Pro 2', 'type' => 'machine_a_coudre',  'brand' => 'Singer', 'status' => 'operationnel', 'location' => 'Atelier A'],
            ['name' => 'Machine Surfileuse',   'type' => 'machine_a_coudre',  'brand' => 'Juki',   'status' => 'en_panne',     'location' => 'Atelier A'],
            ['name' => 'Climatiseur Bureau',   'type' => 'climatiseur',        'brand' => 'Midea',  'status' => 'operationnel', 'location' => 'Bureau'],
            ['name' => 'Groupe Électrogène',   'type' => 'groupe_electrogene', 'brand' => 'Kipor',  'status' => 'operationnel', 'location' => 'Extérieur'],
            ['name' => 'PC Bureau Admin',      'type' => 'ordinateur',         'brand' => 'HP',     'status' => 'operationnel', 'location' => 'Bureau'],
        ];

        foreach ($equipment as $eq) {
            Equipment::create(array_merge($eq, [
                'maintenance_interval_days' => 90,
                'next_maintenance_date'     => now()->addDays(rand(10, 90))->toDateString(),
            ]));
        }

        $this->command->info('✅ Base de données initialisée !');
        $this->command->table(
            ['Email', 'Mot de passe', 'Rôle'],
            [
                ['admin@gsit.art',  'gsit2026', 'Admin'],
                ['marie@gsit.art',  'gsit2026', 'Couturier'],
                ['jean@gsit.art',   'gsit2026', 'Stock'],
                ['sophie@gsit.art', 'gsit2026', 'Caissier'],
                ['pierre@gsit.art', 'gsit2026', 'Livreur'],
            ]
        );
    }
}
