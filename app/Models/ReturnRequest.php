<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnRequest extends Model
{
    protected $table = 'return_requests';

    protected $fillable = ['return_code', 'user_id', 'order_id', 'store_id', 'purchase_code', 'type', 'method', 'reason', 'photo', 'status', 'admin_note', 'approved_at', 'completed_at'];

    protected $casts = ['approved_at' => 'datetime', 'completed_at' => 'datetime'];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function toko(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function item(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_request_id');
    }
}
