<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hutang extends Model
{
    protected $table = 'tb_hutang';
    protected $primaryKey = 'id_hutang';
    public $timestamps = false;

    protected $fillable = ['id_qc','id_mitra','jumlah_tagihan','tanggal_jatuh_tempo','status_bayar','tanggal_lunas','bukti_bayar'];

    public function qc() { return $this->belongsTo(QualityControl::class,'id_qc','id_qc'); }
    public function mitra() { return $this->belongsTo(Mitra::class,'id_mitra','id_mitra'); }
}
