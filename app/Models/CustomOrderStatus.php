<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomOrderStatus extends Model {
    use HasFactory;
    protected $fillable = ['custom_order_id','user_id','status','comment'];
    public function customOrder() { return $this->belongsTo(CustomOrder::class); }
    public function user()        { return $this->belongsTo(User::class); }
}
