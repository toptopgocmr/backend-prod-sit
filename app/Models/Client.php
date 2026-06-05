<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id','first_name','last_name','email','phone',
        'address','city','gender','birth_date','notes','total_spent','orders_count',
    ];

    protected $casts = ['birth_date' => 'date'];

    public function getFullNameAttribute(): string {
        return "{$this->first_name} {$this->last_name}";
    }

    public function user()         { return $this->belongsTo(User::class); }
    public function measurements() { return $this->hasMany(Measurement::class); }
    public function orders()       { return $this->hasMany(Order::class); }
    public function customOrders() { return $this->hasMany(CustomOrder::class); }
    public function deliveries()   { return $this->hasMany(Delivery::class); }
    public function payments()     { return $this->hasMany(Payment::class); }

    public function defaultMeasurement() {
        return $this->hasOne(Measurement::class)->where('is_default', true);
    }
}
