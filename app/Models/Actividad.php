<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    protected $table = 'actividades';
    public $timestamps = false;

    protected $fillable = [
        'codigo_segmento',
        'segmento',
        'codigo_familia',
        'familia',
        'codigo_clase',
        'clase',
        'codigo_producto',
        'producto',
    ];

    /**
     * Compatibilidad dual para permitir tanto $actividad->segmento como $actividad->Segmento
     */
    protected $appends = ['Segmento'];

    public function getSegmentoAttribute()
    {
        return $this->attributes['segmento'] ?? $this->attributes['Segmento'] ?? null;
    }

    public function ofertas()
    {
        return $this->hasMany(Oferta::class, 'actividad_id');
    }
}
