<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatPenilaian extends Model
{
    protected $guarded = ['id'];

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function penilai()
    {
        return $this->belongsTo(User::class, 'penilai_id');
    }
}