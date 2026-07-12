<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BroadcastToken extends Model
{
    protected $table = 'tb_broadcast_token';
    protected $primaryKey = 'id_token';
    public $timestamps = false;

    protected $fillable = ['id_broadcast','id_mitra','token','used'];

    public function broadcast() { return $this->belongsTo(Broadcast::class,'id_broadcast','id_broadcast'); }
    public function mitra() { return $this->belongsTo(Mitra::class,'id_mitra','id_mitra'); }
}
