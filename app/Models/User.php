<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'avatar', 'password', 'role', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // ─── Helpers rôles ───────────────────────────────────────────
    public function isAdmin(): bool        { return $this->role === 'admin'; }
    public function isCouturier(): bool    { return $this->role === 'couturier'; }
    public function isStockManager(): bool { return $this->role === 'stock_manager'; }
    public function isCashier(): bool      { return $this->role === 'cashier'; }
    public function isDelivery(): bool     { return $this->role === 'delivery'; }

    public function getRoleLabel(): string {
        return match($this->role) {
            'admin'         => 'Administrateur',
            'couturier'     => 'Couturier',
            'stock_manager' => 'Responsable Stock',
            'cashier'       => 'Caissier',
            'delivery'      => 'Livreur',
            'client'        => 'Client',
            default         => $this->role,
        };
    }

    // ─── Relations ───────────────────────────────────────────────
    public function client()            { return $this->hasOne(Client::class); }
    public function customOrders()      { return $this->hasMany(CustomOrder::class, 'assigned_to'); }
    public function deliveries()        { return $this->hasMany(Delivery::class, 'driver_id'); }
    public function expenses()          { return $this->hasMany(Expense::class); }
    public function stockMovements()    { return $this->hasMany(StockMovement::class); }
    public function activityLogs()      { return $this->hasMany(ActivityLog::class); }
    public function maintenanceLogs()   { return $this->hasMany(MaintenanceLog::class, 'reported_by'); }
    public function salaryPayments()    { return $this->hasMany(SalaryPayment::class, 'employee_id'); }

    // Avatar URL
    public function getAvatarUrlAttribute(): string {
        if ($this->avatar) return asset('storage/' . $this->avatar);
        $initial = strtoupper(substr($this->name, 0, 1));
        return "https://ui-avatars.com/api/?name={$this->name}&background=1a1a2e&color=e8820c&bold=true";
    }
}
