<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expense extends Model {
    use HasFactory;
    protected $fillable = ['expense_category_id','user_id','label','amount','payment_method','expense_date','receipt_photo','reference','notes','is_validated','validated_by'];
    protected $casts = ['expense_date'=>'date','is_validated'=>'boolean','amount'=>'decimal:2'];
    public function category()  { return $this->belongsTo(ExpenseCategory::class, 'expense_category_id'); }
    public function user()      { return $this->belongsTo(User::class); }
    public function validator() { return $this->belongsTo(User::class, 'validated_by'); }
}
