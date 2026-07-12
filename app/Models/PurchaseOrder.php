<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $table = 'tb_purchase_order';
    protected $primaryKey = 'id_po';
    public $timestamps = false;

    protected $fillable = ['nomor_po','id_penawaran','tanggal_terbit','total_nilai','status_po'];

    public function penawaran() { return $this->belongsTo(Penawaran::class,'id_penawaran','id_penawaran'); }
    public function penerimaan() { return $this->hasOne(Penerimaan::class,'id_po','id_po'); }
}
