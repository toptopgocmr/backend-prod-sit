<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseOrder extends Model {
    use HasFactory;
    protected $fillable = [
        'reference','user_id','supplier_name','supplier_phone',
        'status','total_amount','amount_paid',
        'expected_date','received_date','notes',
        'expense_id','payment_method',   // ← nouveaux champs finance
    ];
    protected $casts = ['expected_date'=>'date','received_date'=>'date'];
    public function items()   { return $this->hasMany(PurchaseOrderItem::class); }
    public function user()    { return $this->belongsTo(User::class); }
    public function expense() { return $this->belongsTo(Expense::class); }
}
