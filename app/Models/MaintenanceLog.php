<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MaintenanceLog extends Model {
    use HasFactory;
    protected $fillable = ['equipment_id','reported_by','assigned_to','type','status','title','description','resolution','technician_name','technician_phone','cost','invoice_photo','scheduled_date','started_at','resolved_at'];
    protected $casts = ['scheduled_date'=>'date','started_at'=>'datetime','resolved_at'=>'datetime','cost'=>'decimal:2'];
    public function equipment() { return $this->belongsTo(Equipment::class); }
    public function reporter()  { return $this->belongsTo(User::class, 'reported_by'); }
    public function assignee()  { return $this->belongsTo(User::class, 'assigned_to'); }
}
