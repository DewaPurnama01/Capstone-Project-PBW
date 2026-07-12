<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';
    public $timestamps = false;

    protected $fillable = ['id','tanggal_transaksi','total_bayar','metode_bayar','diskon','poin_digunakan','poin_didapat','catatan','id_kasir'];

    public function pelanggan() { return $this->belongsTo(Pelanggan::class,'id','id'); }
    public function details() { return $this->hasMany(DetailTransaksi::class,'id_transaksi','id_transaksi'); }
}
