<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QualityControl extends Model
{
    protected $table = 'tb_quality_control';
    protected $primaryKey = 'id_qc';
    public $timestamps = false;

    protected $fillable = ['id_penerimaan','hasil_qc','catatan_qc','foto_dokumentasi','tanggal_qc','id_admin'];

    public function penerimaan() { return $this->belongsTo(Penerimaan::class,'id_penerimaan','id_penerimaan'); }
    public function hutang() { return $this->hasOne(Hutang::class,'id_qc','id_qc'); }
}
