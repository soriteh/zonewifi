<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Router extends Model {
    protected $fillable = ['company_id','name','host','api_port','username','password_encrypted','routeros_version','status','last_seen_at'];
    protected $hidden = ['password_encrypted'];
    protected function casts(): array { return ['password_encrypted'=>'encrypted','last_seen_at'=>'datetime']; }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
