<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DolarBcv extends Model
{
    protected $table = 'dolar_bcv';

    protected $fillable = ['fecha', 'hora', 'tasa'];

    protected $casts = [
        'fecha' => 'date',
        'hora'  => 'datetime:H:i:s',
        'tasa'  => 'decimal:4',
    ];

    public static function ultimaTasa(): ?float
    {
        return static::orderByDesc('fecha')
            ->orderByDesc('hora')
            ->first()?->tasa;
    }

    public static function tasaHoy(): ?float
    {
        return static::where('fecha', Carbon::today())
            ->orderByDesc('hora')
            ->first()?->tasa;
    }
}
