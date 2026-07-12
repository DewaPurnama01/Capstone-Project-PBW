<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mitra extends Model
{
    protected $table = 'tb_mitra';
    protected $primaryKey = 'id_mitra';
    public $timestamps = false;

    protected $fillable = ['nama_mitra','no_hp','alamat','komoditas','status_aktif','tanggal_daftar'];

    public function penawaran()
    {
        return $this->hasMany(Penawaran::class, 'id_mitra', 'id_mitra');
    }

    public function hutang()
    {
        return $this->hasMany(Hutang::class, 'id_mitra', 'id_mitra');
    }
}
