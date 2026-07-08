<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk tabel "partner_offers" (tawaran harga dari petani).
 */
class PartnerOffer extends Model
{
    protected $fillable = ['restock_request_id', 'partner_id', 'price_per_unit', 'eta_days', 'status'];

    public function restockRequest() { return $this->belongsTo(RestockRequest::class); }
    public function partner() { return $this->belongsTo(Partner::class); }
}
