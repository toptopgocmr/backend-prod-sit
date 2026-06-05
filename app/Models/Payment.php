<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model {
    use HasFactory;
    protected $fillable = ['reference','payable_type','payable_id','client_id','cashier_id','amount','method','transaction_id','notes'];
    protected $casts = ['amount'=>'decimal:2'];
    public function payable()  { return $this->morphTo(); }
    public function client()   { return $this->belongsTo(Client::class); }
    public function cashier()  { return $this->belongsTo(User::class, 'cashier_id'); }
}
