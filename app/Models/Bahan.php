<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bahan extends Model
{
    protected $table = 'tb_bahan';
    protected $primaryKey = 'id_bahan';
    public $timestamps = false;

    protected $fillable = ['nama_bahan','satuan','jumlah_stok','batas_minimum','tanggal_update','status_stok'];

    public function updateStatus()
    {
        if ($this->jumlah_stok <= 0) $this->status_stok = 'HABIS';
        elseif ($this->jumlah_stok <= $this->batas_minimum) $this->status_stok = 'RENDAH';
        else $this->status_stok = 'NORMAL';
    }
}
