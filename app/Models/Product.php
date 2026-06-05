<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id','name','slug','reference','description','type','gender',
        'price_per_meter','available_meters','min_meters',
        'price','size','color','stock_quantity','alert_threshold',
        'image','images','cost_price','is_active','is_featured',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'is_featured' => 'boolean',
        'images'      => 'array',
    ];

    protected static function boot() {
        parent::boot();
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name) . '-' . Str::random(6);
            }
            if (empty($product->reference)) {
                $prefix = strtoupper(substr($product->type, 0, 3));
                $product->reference = $prefix . '-' . strtoupper(Str::random(8));
            }
        });
    }

    // ─── Scopes ───────────────────────────────────────────────────
    public function scopeActive($q)            { return $q->where('is_active', true); }
    public function scopeTissus($q)            { return $q->where('type', 'tissu'); }
    public function scopePretAPorter($q)       { return $q->where('type', 'pret_a_porter'); }
    public function scopeAccessoires($q)       { return $q->where('type', 'accessoire'); }
    public function scopeLowStock($q)          { return $q->whereRaw('stock_quantity <= alert_threshold'); }

    // ─── Helpers ─────────────────────────────────────────────────
    public function isLowStock(): bool {
        if ($this->type === 'tissu') {
            return $this->available_meters <= $this->alert_threshold;
        }
        return $this->stock_quantity <= $this->alert_threshold;
    }

    public function getCurrentStock() {
        return $this->type === 'tissu' ? $this->available_meters : $this->stock_quantity;
    }

    public function getStockUnit(): string {
        return $this->type === 'tissu' ? 'm' : 'pcs';
    }

    public function getUnitPrice() {
        return $this->type === 'tissu' ? $this->price_per_meter : $this->price;
    }

    public function getImageUrlAttribute(): string {
        if ($this->image) return asset('storage/' . $this->image);
        return asset('images/placeholder-product.png');
    }

    public function getTypeLabel(): string {
        return match($this->type) {
            'tissu'         => 'Tissu',
            'pret_a_porter' => 'Prêt-à-porter',
            'accessoire'    => 'Accessoire',
            default         => $this->type,
        };
    }

    // ─── Relations ───────────────────────────────────────────────
    public function category()         { return $this->belongsTo(Category::class); }
    public function stockMovements()   { return $this->hasMany(StockMovement::class); }
    public function orderItems()       { return $this->hasMany(OrderItem::class); }
}
