<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DolarParalelo extends Model
{
    protected $table = 'dolar_paralelo';

    // ESTO ES LO QUE FALTABA
    protected $fillable = ['fecha', 'hora', 'tasa'];

    // O si prefieres la forma más segura:
    // protected $guarded = [];

    public static function tasaActualRaw(): float
    {
        return (float) static::latest('created_at')->first()?->tasa ?? 0.00;
    }

    public static function tasaActualFormateada(): string
    {
        $tasa = static::tasaActualRaw();
        return number_format($tasa, 2, ',', '.');
    }
}
