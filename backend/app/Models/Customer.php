<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Customer extends Model {
    protected $fillable = ['company_id','name','phone','email','external_reference'];
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
