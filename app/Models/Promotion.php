<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Promotion extends Model {
    use HasFactory;
    protected $fillable = ['name','code','type','value','min_amount','usage_limit','usage_count','starts_at','ends_at','is_active'];
    protected $casts = ['starts_at'=>'date','ends_at'=>'date','is_active'=>'boolean'];
    public function calculate(float $amount): float {
        if ($amount < $this->min_amount) return 0;
        if ($this->type === 'percentage') return round($amount * $this->value / 100, 2);
        return min($this->value, $amount);
    }
}
