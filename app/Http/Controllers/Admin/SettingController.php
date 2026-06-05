<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'app_name'            => config('app.name'),
            'currency'            => env('CURRENCY', 'FCFA'),
            'low_stock_threshold' => env('LOW_STOCK_THRESHOLD', 5),
            'mail_from_address'   => env('MAIL_FROM_ADDRESS', ''),
            'mail_from_name'      => env('MAIL_FROM_NAME', ''),
        ];

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_name'            => 'required|string|max:100',
            'currency'            => 'required|string|max:10',
            'low_stock_threshold' => 'required|integer|min:1|max:9999',
            'mail_from_address'   => 'nullable|email|max:100',
            'mail_from_name'      => 'nullable|string|max:100',
        ], [
            'app_name.required'            => 'Le nom de l\'application est obligatoire.',
            'currency.required'            => 'La devise est obligatoire.',
            'low_stock_threshold.required' => 'Le seuil de stock est obligatoire.',
            'low_stock_threshold.integer'  => 'Le seuil doit être un nombre entier.',
            'low_stock_threshold.min'      => 'Le seuil doit être au moins 1.',
            'mail_from_address.email'      => 'L\'adresse e-mail n\'est pas valide.',
        ]);

        $this->updateEnv([
            'APP_NAME'            => '"' . $request->app_name . '"',
            'CURRENCY'            => $request->currency,
            'LOW_STOCK_THRESHOLD' => $request->low_stock_threshold,
            'MAIL_FROM_ADDRESS'   => '"' . ($request->mail_from_address ?? '') . '"',
            'MAIL_FROM_NAME'      => '"' . ($request->mail_from_name ?? $request->app_name) . '"',
        ]);

        // Log de l'activité
        try {
            \App\Models\ActivityLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'updated',
                'model_type'  => 'Settings',
                'model_id'    => 0,
                'description' => 'Modification des paramètres de l\'application',
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
            ]);
        } catch (\Exception $e) {}

        return back()->with('success', 'Paramètres enregistrés avec succès.');
    }

    /**
     * Met à jour les clés dans le fichier .env
     */
    private function updateEnv(array $data): void
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            return;
        }

        $content = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            $pattern     = '/^' . preg_quote($key, '/') . '=.*/m';
            $replacement = $key . '=' . $value;

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $replacement, $content);
            } else {
                $content .= PHP_EOL . $replacement;
            }
        }

        file_put_contents($envPath, $content);
    }
}
