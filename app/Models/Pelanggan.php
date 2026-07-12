<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['nama','no_hp','email','tanggal_lahir','tanggal_daftar','poin','total_kunjungan','segmen','catatan'];

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class,'id','id');
    }
}
