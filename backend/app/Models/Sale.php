<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Sale extends Model {
    protected $fillable = ['company_id','wifi_ticket_id','customer_id','seller_id','amount','currency','payment_method','status','sold_at'];
    protected function casts(): array { return ['amount'=>'integer','sold_at'=>'datetime']; }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function ticket(): BelongsTo { return $this->belongsTo(WifiTicket::class, 'wifi_ticket_id'); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function seller(): BelongsTo { return $this->belongsTo(User::class, 'seller_id'); }
}
