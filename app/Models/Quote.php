<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Quote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference', 'client_id', 'created_by',
        // Legacy (devis à vêtement unique – conservé pour compatibilité)
        'gender', 'garment_type', 'model_name', 'model_description', 'model_photo',
        'fabric_product_id', 'fabric_name', 'fabric_price_per_meter',
        'fabric_meters', 'fabric_color',
        'fabric_cost',
        // Multi-vêtements / multi-tissus (nouveaux devis)
        'garments',
        // Coûts communs
        'labor_cost', 'accessories_cost', 'accessories', 'total',
        // Statut & dates
        'status', 'valid_until', 'delivery_date', 'notes', 'custom_order_id',
    ];

    protected $casts = [
        'accessories'  => 'array',
        'garments'     => 'array',
        'valid_until'  => 'date',
        'delivery_date'=> 'date',
    ];

    const STATUSES = [
        'brouillon' => ['label' => 'Brouillon',  'color' => 'gray'],
        'envoye'    => ['label' => 'Envoyé',      'color' => 'blue'],
        'accepte'   => ['label' => 'Accepté',     'color' => 'green'],
        'refuse'    => ['label' => 'Refusé',      'color' => 'red'],
        'expire'    => ['label' => 'Expiré',      'color' => 'orange'],
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($q) {
            if (empty($q->reference)) {
                $q->reference = 'DEV-' . date('Ymd') . '-' . strtoupper(Str::random(5));
            }
        });
    }

    // ── Helpers statut ──────────────────────────────────────────────────────

    public function getStatusInfo(): array
    {
        return self::STATUSES[$this->status] ?? ['label' => $this->status, 'color' => 'gray'];
    }

    public function getStatusLabel(): string { return $this->getStatusInfo()['label']; }
    public function getStatusColor(): string { return $this->getStatusInfo()['color']; }

    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->isPast() && $this->status === 'envoye';
    }

    // ── Accesseur : nombre de vêtements ────────────────────────────────────

    public function getGarmentCountAttribute(): int
    {
        if ($this->garments) {
            return count($this->garments);
        }
        return 1; // devis legacy = 1 vêtement
    }

    // ── Relations ───────────────────────────────────────────────────────────

    public function client()      { return $this->belongsTo(Client::class); }
    public function creator()     { return $this->belongsTo(User::class, 'created_by'); }
    public function fabric()      { return $this->belongsTo(Product::class, 'fabric_product_id'); }
    public function customOrder() { return $this->belongsTo(CustomOrder::class); }
}
