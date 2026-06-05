<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockMovement extends Model {
    use HasFactory;
    protected $fillable = ['product_id','user_id','type','quantity','quantity_before','quantity_after','unit_cost','reference','reason','movable_type','movable_id','notes'];
    public function product() { return $this->belongsTo(Product::class); }
    public function user()    { return $this->belongsTo(User::class); }
    public function movable() { return $this->morphTo(); }
}
