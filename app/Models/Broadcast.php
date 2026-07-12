<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model
{
    protected $table = 'tb_broadcast';
    protected $primaryKey = 'id_broadcast';
    public $timestamps = false;

    protected $fillable = ['id_bahan','jumlah_dibutuhkan','harga_target','tanggal_kirim','batas_respon','status_broadcast'];

    public function bahan()
    {
        return $this->belongsTo(Bahan::class, 'id_bahan', 'id_bahan');
    }

    public function penawaran()
    {
        return $this->hasMany(Penawaran::class, 'id_broadcast', 'id_broadcast');
    }

    public function tokens()
    {
        return $this->hasMany(BroadcastToken::class, 'id_broadcast', 'id_broadcast');
    }
}
