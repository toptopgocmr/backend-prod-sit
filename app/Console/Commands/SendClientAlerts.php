<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendClientAlerts extends Command
{
    protected $signature   = 'clients:alerts';
    protected $description = 'Envoie les alertes anniversaires et relances clients inactifs via WhatsApp/Email';

    public function handle(): void
    {
        $today = Carbon::today();
        $this->info("=== Alertes clients — {$today->format('d/m/Y')} ===");

        // ── 1. Anniversaires du jour ───────────────────────────────
        $birthdays = Client::whereNotNull('birth_date')->get()->filter(function ($c) use ($today) {
            $bday = Carbon::parse($c->birth_date)->setYear($today->year);
            return $bday->isToday();
        });

        $this->info("🎂 Anniversaires aujourd'hui : {$birthdays->count()}");

        foreach ($birthdays as $client) {
            $this->sendBirthdayAlert($client);
        }

        // ── 2. Clients inactifs depuis 90 jours (relance 1 fois/mois) ──
        // On n'envoie la relance que le 1er de chaque mois pour éviter le spam
        if ($today->day === 1) {
            $threshold = $today->copy()->subDays(90);
            $inactive  = Client::with([
                'orders'       => fn($q) => $q->latest()->limit(1),
                'customOrders' => fn($q) => $q->latest()->limit(1),
            ])->get()->filter(function ($c) use ($threshold) {
                $last = collect([
                    $c->orders->first()?->created_at,
                    $c->customOrders->first()?->created_at,
                ])->filter()->max();
                return $last && Carbon::parse($last)->lt($threshold);
            });

            $this->info("😴 Clients inactifs à relancer : {$inactive->count()}");

            foreach ($inactive as $client) {
                $this->sendInactiveAlert($client);
            }
        }

        $this->info('✅ Traitement terminé.');
    }

    // ── Alerte anniversaire ───────────────────────────────────────
    private function sendBirthdayAlert(Client $client): void
    {
        $msg = "Bonjour {$client->first_name} ! 🎂\n\n"
             . "Toute l'équipe *GSIT Haute Couture* vous souhaite un joyeux anniversaire ! 🎉\n\n"
             . "Pour célébrer ce jour spécial, bénéficiez d'une *réduction exclusive* sur votre prochaine commande.\n\n"
             . "Contactez-nous pour en profiter !\nÀ très bientôt 🙏";

        if ($client->phone) {
            $this->sendWhatsApp($client->phone, $msg);
            $this->info("  ✓ WA anniversaire → {$client->full_name} ({$client->phone})");
        }

        if ($client->email) {
            $this->sendEmail(
                $client->email,
                "Joyeux anniversaire de la part de GSIT Haute Couture ! 🎂",
                "Bonjour {$client->first_name},\n\n"
              . "Toute l'équipe GSIT Haute Couture vous souhaite un joyeux anniversaire !\n\n"
              . "Pour fêter ça, nous vous offrons une réduction exclusive sur votre prochaine commande.\n\n"
              . "À très bientôt !\nL'équipe GSIT Haute Couture"
            );
            $this->info("  ✓ Email anniversaire → {$client->full_name} ({$client->email})");
        }

        Log::info("Alerte anniversaire envoyée", ['client_id' => $client->id, 'name' => $client->full_name]);
    }

    // ── Alerte inactivité ─────────────────────────────────────────
    private function sendInactiveAlert(Client $client): void
    {
        $msg = "Bonjour {$client->first_name} ! 👋\n\n"
             . "Cela fait un moment que nous n'avons pas eu le plaisir de vous servir chez *GSIT Haute Couture*.\n\n"
             . "De nouvelles créations vous attendent ! Passez commande ou venez nous rendre visite.\n\n"
             . "À très bientôt 🙏";

        if ($client->phone) {
            $this->sendWhatsApp($client->phone, $msg);
            $this->info("  ✓ WA relance → {$client->full_name} ({$client->phone})");
        }

        if ($client->email) {
            $this->sendEmail(
                $client->email,
                "GSIT Haute Couture vous manque !",
                "Bonjour {$client->first_name},\n\n"
              . "Nous espérons que vous allez bien ! Cela fait un moment que nous n'avons pas eu le plaisir de vous servir.\n\n"
              . "De nouvelles créations vous attendent chez GSIT Haute Couture. N'hésitez pas à nous contacter.\n\n"
              . "À très bientôt !\nL'équipe GSIT Haute Couture"
            );
            $this->info("  ✓ Email relance → {$client->full_name} ({$client->email})");
        }

        Log::info("Alerte inactivité envoyée", ['client_id' => $client->id, 'name' => $client->full_name]);
    }

    // ── Envoi WhatsApp via CallMeBot (gratuit) ────────────────────
    // ou via votre propre gateway WhatsApp Business API
    private function sendWhatsApp(string $phone, string $message): void
    {
        $phone = preg_replace('/\D/', '', $phone);

        // Option A : CallMeBot (nécessite inscription sur callmebot.com)
        $apiKey = config('services.callmebot.api_key');
        if ($apiKey) {
            $url = "https://api.callmebot.com/whatsapp.php"
                 . "?phone={$phone}&text=" . urlencode($message) . "&apikey={$apiKey}";
            try {
                file_get_contents($url);
            } catch (\Exception $e) {
                Log::warning("WhatsApp échoué pour {$phone}: " . $e->getMessage());
            }
            return;
        }

        // Option B : Lien wa.me loggé (pour envoi manuel depuis le tableau de bord)
        $link = "https://wa.me/{$phone}?text=" . urlencode($message);
        Log::info("WhatsApp à envoyer manuellement", ['link' => $link]);
    }

    // ── Envoi Email via le système mail Laravel ───────────────────
    private function sendEmail(string $to, string $subject, string $body): void
    {
        try {
            \Illuminate\Support\Facades\Mail::raw($body, function ($mail) use ($to, $subject) {
                $mail->to($to)
                     ->subject($subject)
                     ->from(config('mail.from.address'), config('mail.from.name', 'GSIT Haute Couture'));
            });
        } catch (\Exception $e) {
            Log::warning("Email échoué pour {$to}: " . $e->getMessage());
        }
    }
}
