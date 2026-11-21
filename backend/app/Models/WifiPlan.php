<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class WifiPlan extends Model {
    protected $fillable = ['company_id','name','duration_minutes','price','currency','router_profile','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean','duration_minutes'=>'integer','price'=>'integer']; }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
