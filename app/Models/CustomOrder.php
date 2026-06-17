<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class CustomOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference','client_id','measurement_id','assigned_to','cashier_id',
        // Commande individuelle
        'model_name','model_photo','model_description','gender','garment_type',
        // Commande groupe
        'is_group_order','group_name','group_occasion','group_members','model_photos',
        // Tissu & accessoires
        'fabric_product_id','fabric_meters','fabric_color','accessories',
        // Statuts & paiement
        'status','payment_status','payment_method',
        'fabric_cost','labor_cost','accessories_cost','total','amount_paid','deposit',
        'delivery_date','started_at','completed_at','delivered_at','notes',
    ];

    protected $casts = [
        'accessories'   => 'array',
        'group_members' => 'array',
        'model_photos'  => 'array',
        'is_group_order'=> 'boolean',
        'delivery_date' => 'date',
        'started_at'    => 'datetime',
        'completed_at'  => 'datetime',
        'delivered_at'  => 'datetime',
    ];

    protected static function boot() {
        parent::boot();
        static::creating(function ($co) {
            if (empty($co->reference)) {
                $co->reference = 'SM-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }
        });
    }

    // Workflow statuts ordonnés
    const STATUSES = [
        'recu'             => ['label' => 'Reçu',             'color' => 'gray',   'icon' => 'inbox'],
        'en_decoupe'       => ['label' => 'En découpe',       'color' => 'orange', 'icon' => 'scissors'],
        'en_couture'       => ['label' => 'En couture',       'color' => 'blue',   'icon' => 'thread'],
        'finition'         => ['label' => 'Finition',         'color' => 'purple', 'icon' => 'sparkles'],
        'controle_qualite' => ['label' => 'Contrôle qualité', 'color' => 'yellow', 'icon' => 'check-circle'],
        'pret'             => ['label' => 'Prêt',             'color' => 'green',  'icon' => 'package'],
        'livre'            => ['label' => 'Livré',            'color' => 'emerald','icon' => 'truck'],
        'annule'           => ['label' => 'Annulé',           'color' => 'red',    'icon' => 'x-circle'],
    ];

    public function getStatusInfo(): array {
        return self::STATUSES[$this->status] ?? ['label' => $this->status, 'color' => 'gray', 'icon' => 'circle'];
    }

    public function getStatusLabel(): string { return $this->getStatusInfo()['label']; }
    public function getStatusColor(): string { return $this->getStatusInfo()['color']; }

    public function getBalanceAttribute(): float {
        return $this->total - $this->amount_paid;
    }

    public function getProgressPercentAttribute(): int {
        $order = array_keys(self::STATUSES);
        $pos = array_search($this->status, $order);
        if ($pos === false || $this->status === 'annule') return 0;
        return (int)(($pos / (count($order) - 2)) * 100);
    }

    public function client()         { return $this->belongsTo(Client::class); }
    public function measurement()    { return $this->belongsTo(Measurement::class); }
    public function couturier()      { return $this->belongsTo(User::class, 'assigned_to'); }
    public function cashier()        { return $this->belongsTo(User::class, 'cashier_id'); }
    public function fabric()         { return $this->belongsTo(Product::class, 'fabric_product_id'); }
    public function statusHistory()  { return $this->hasMany(CustomOrderStatus::class)->orderBy('created_at', 'desc'); }
    public function payments()       { return $this->morphMany(Payment::class, 'payable'); }
    public function delivery()       { return $this->morphOne(Delivery::class, 'deliverable'); }
}
