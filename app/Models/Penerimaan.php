<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penerimaan extends Model
{
    protected $table = 'tb_penerimaan';
    protected $primaryKey = 'id_penerimaan';
    public $timestamps = false;

    protected $fillable = ['id_po','tanggal_terima','jumlah_diterima','kondisi_fisik','id_admin'];

    public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class,'id_po','id_po'); }
    public function qc() { return $this->hasOne(QualityControl::class,'id_penerimaan','id_penerimaan'); }
}
