<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalaryPayment extends Model {
    use HasFactory;
    protected $fillable = ['employee_id','paid_by','base_salary','bonus','deduction','net_amount','month','year','payment_method','paid_at','notes'];
    protected $casts = ['paid_at'=>'date'];
    public function employee() { return $this->belongsTo(User::class, 'employee_id'); }
    public function paidBy()   { return $this->belongsTo(User::class, 'paid_by'); }
}
