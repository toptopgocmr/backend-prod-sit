<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Ajouter les utilisateurs réels de l'équipe GSIT.
     * Les couturiers : Galina, Rodrigue, Mouanda, Modeste
     * Les autres : Dutran, Perla, Stéphane
     */
    public function up(): void
    {
        $users = [
            // ── Couturiers ──
            ['name' => 'Galina',    'email' => 'galina@gsit.art',    'role' => 'couturier',     'phone' => null],
            ['name' => 'Rodrigue',  'email' => 'rodrigue@gsit.art',  'role' => 'couturier',     'phone' => null],
            ['name' => 'Mouanda',   'email' => 'mouanda@gsit.art',   'role' => 'couturier',     'phone' => null],
            ['name' => 'Modeste',   'email' => 'modeste@gsit.art',   'role' => 'couturier',     'phone' => null],
            // ── Autres ──
            ['name' => 'Dutran',    'email' => 'dutran@gsit.art',    'role' => 'stock_manager', 'phone' => null],
            ['name' => 'Perla',     'email' => 'perla@gsit.art',     'role' => 'cashier',       'phone' => null],
            ['name' => 'Stéphane',  'email' => 'stephane@gsit.art',  'role' => 'delivery',      'phone' => null],
        ];

        foreach ($users as $user) {
            // N'insérer que si l'email n'existe pas déjà
            $exists = DB::table('users')->where('email', $user['email'])->exists();
            if (!$exists) {
                DB::table('users')->insert([
                    'name'       => $user['name'],
                    'email'      => $user['email'],
                    'password'   => Hash::make('Gsit2026!'),
                    'role'       => $user['role'],
                    'phone'      => $user['phone'],
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Désactiver les anciens comptes de démo qui ne correspondent plus
        $demoEmails = ['marie@gsit.art', 'jean@gsit.art', 'sophie@gsit.art', 'pierre@gsit.art'];
        DB::table('users')
            ->whereIn('email', $demoEmails)
            ->update(['is_active' => false, 'updated_at' => now()]);
    }

    public function down(): void
    {
        $emails = [
            'galina@gsit.art', 'rodrigue@gsit.art', 'mouanda@gsit.art', 'modeste@gsit.art',
            'dutran@gsit.art', 'perla@gsit.art', 'stephane@gsit.art',
        ];
        DB::table('users')->whereIn('email', $emails)->delete();

        // Réactiver les comptes démo
        $demoEmails = ['marie@gsit.art', 'jean@gsit.art', 'sophie@gsit.art', 'pierre@gsit.art'];
        DB::table('users')->whereIn('email', $demoEmails)->update(['is_active' => true]);
    }
};
