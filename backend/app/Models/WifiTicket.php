<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class WifiTicket extends Model {
    protected $fillable = ['company_id','wifi_plan_id','customer_id','username','password_encrypted','status','activated_at','expires_at'];
    protected $hidden = ['password_encrypted'];
    protected function casts(): array { return ['password_encrypted'=>'encrypted','activated_at'=>'datetime','expires_at'=>'datetime']; }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function plan(): BelongsTo { return $this->belongsTo(WifiPlan::class, 'wifi_plan_id'); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
}
