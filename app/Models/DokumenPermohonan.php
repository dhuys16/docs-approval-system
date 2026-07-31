<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DokumenPermohonan extends Model
{
    protected $guarded = ['id'];

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }
}