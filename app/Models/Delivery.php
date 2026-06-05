<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Delivery extends Model {
    use HasFactory;
    protected $fillable = ['reference','deliverable_type','deliverable_id','client_id','driver_id','status','type','delivery_address','delivery_city','delivery_fee','latitude','longitude','proof_photo','recipient_name','notes','assigned_at','picked_up_at','delivered_at'];
    protected $casts = ['assigned_at'=>'datetime','picked_up_at'=>'datetime','delivered_at'=>'datetime'];
    public function deliverable() { return $this->morphTo(); }
    public function client()      { return $this->belongsTo(Client::class); }
    public function driver()      { return $this->belongsTo(User::class, 'driver_id'); }
}
