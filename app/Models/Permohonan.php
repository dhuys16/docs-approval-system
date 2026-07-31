<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permohonan extends Model
{
    protected $guarded = ['id'];

    public function pemohon()
    {
        return $this->belongsTo(User::class, 'pemohon_id');
    }

    public function dokumen()
    {
        return $this->hasMany(DokumenPermohonan::class);
    }

    public function riwayat()
    {
        return $this->hasMany(RiwayatPenilaian::class)->latest();
    }
}