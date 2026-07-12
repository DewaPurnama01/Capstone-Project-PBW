<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penawaran extends Model
{
    protected $table = 'tb_penawaran';
    protected $primaryKey = 'id_penawaran';
    public $timestamps = false;

    protected $fillable = ['id_broadcast','id_mitra','harga_satuan','jumlah_tersedia','estimasi_kirim','catatan_mitra','status_penawaran','tanggal_input'];

    public function broadcast() { return $this->belongsTo(Broadcast::class,'id_broadcast','id_broadcast'); }
    public function mitra() { return $this->belongsTo(Mitra::class,'id_mitra','id_mitra'); }
    public function purchaseOrder() { return $this->hasOne(PurchaseOrder::class,'id_penawaran','id_penawaran'); }
}
