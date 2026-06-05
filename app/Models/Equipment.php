<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model {
    use HasFactory, SoftDeletes;
    protected $fillable = ['name','reference','type','brand','model','serial_number','purchase_date','purchase_price','status','location','photo','maintenance_interval_days','last_maintenance_date','next_maintenance_date','notes'];
    protected $casts = ['purchase_date'=>'date','last_maintenance_date'=>'date','next_maintenance_date'=>'date','purchase_price'=>'decimal:2'];
    public function maintenanceLogs() { return $this->hasMany(MaintenanceLog::class); }
    public function getTypeLabel(): string {
        return match($this->type) {
            'machine_a_coudre'   => 'Machine à coudre',
            'climatiseur'        => 'Climatiseur',
            'groupe_electrogene' => 'Groupe électrogène',
            'ordinateur'         => 'Ordinateur',
            default              => 'Autre',
        };
    }
    public function isOverdue(): bool {
        return $this->next_maintenance_date && $this->next_maintenance_date->isPast();
    }
}
