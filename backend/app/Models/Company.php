<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Company extends Model {
    protected $fillable = ['name','slug','email','phone','country','currency','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
    public function users(): HasMany { return $this->hasMany(User::class); }
    public function routers(): HasMany { return $this->hasMany(Router::class); }
}
