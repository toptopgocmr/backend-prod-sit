<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference','client_id','cashier_id','type','status','payment_status',
        'payment_method','subtotal','discount','total','amount_paid','notes',
        'confirmed_at','delivered_at',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'delivered_at' => 'datetime',
        'subtotal'     => 'decimal:2',
        'discount'     => 'decimal:2',
        'total'        => 'decimal:2',
        'amount_paid'  => 'decimal:2',
    ];

    protected static function boot() {
        parent::boot();
        static::creating(function ($order) {
            if (empty($order->reference)) {
                $order->reference = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }
        });
    }

    public function getStatusLabel(): string {
        return match($this->status) {
            'pending'    => 'En attente',
            'confirmed'  => 'Confirmée',
            'processing' => 'En cours',
            'ready'      => 'Prête',
            'delivered'  => 'Livrée',
            'cancelled'  => 'Annulée',
            default      => $this->status,
        };
    }

    public function getStatusColor(): string {
        return match($this->status) {
            'pending'    => 'yellow',
            'confirmed'  => 'blue',
            'processing' => 'indigo',
            'ready'      => 'green',
            'delivered'  => 'emerald',
            'cancelled'  => 'red',
            default      => 'gray',
        };
    }

    public function getBalanceAttribute(): float {
        return $this->total - $this->amount_paid;
    }

    public function client()   { return $this->belongsTo(Client::class); }
    public function cashier()  { return $this->belongsTo(User::class, 'cashier_id'); }
    public function items()    { return $this->hasMany(OrderItem::class); }
    public function payments() { return $this->morphMany(Payment::class, 'payable'); }
    public function delivery() { return $this->morphOne(Delivery::class, 'deliverable'); }
}


