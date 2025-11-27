<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DolarBcv extends Model
{
    protected $table = 'dolar_bcv';
    protected $fillable = [
        'fecha' => 'date',
        'hora' => 'dateTime:H:i:s',
        'tasa' => 'decimal:4'
    ];

    public static function tasaActual()
    {
        return static::latest('created_at')->first()?->tasa;
    }

    public static function tasaHoy()
    {
        return static::whereDate('created_at', today())->orderBy('created_at', 'desc')->first();
    }
}
