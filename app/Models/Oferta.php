<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Oferta extends Model
{
    protected $table = 'ofertas';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'consecutivo',
        'objeto',
        'descripcion',
        'moneda',
        'presupuesto',
        'actividad_id',
        'fecha_inicio',
        'hora_inicio',
        'fecha_cierre',
        'hora_cierre',
        'estado',
    ];

    protected $casts = [
        'presupuesto' => 'float',
        'actividad_id' => 'integer',
        'fecha_inicio' => 'date:Y-m-d',
        'fecha_cierre' => 'date:Y-m-d',
    ];

    /**
     * Sincroniza automáticamente en la base de datos el estado a 'Cerrada' para
     * todas las ofertas cuya fecha y hora de cierre hayan expirado y se encuentren 'Publicada' o 'Activo'.
     */
    public static function sincronizarEstadosCierre(): void
    {
        try {
            static::whereIn('estado', ['Publicada', 'Activo'])
                ->whereRaw("TIMESTAMP(fecha_cierre, hora_cierre) <= NOW()")
                ->update(['estado' => 'Cerrada']);
        } catch (\Throwable $e) {
            error_log("Error en Oferta::sincronizarEstadosCierre: " . $e->getMessage());
        }
    }

    /**
     * Accesor para asegurar que cualquier consulta o serialización del modelo
     * retorne 'Cerrada' en tiempo real si el cronograma de cierre ya se cumplió.
     */
    public function getEstadoAttribute($value)
    {
        if (in_array($value, ['Publicada', 'Activo']) && !empty($this->attributes['fecha_cierre']) && !empty($this->attributes['hora_cierre'])) {
            $fecha = explode(' ', explode('T', $this->attributes['fecha_cierre'])[0])[0];
            $hora  = substr($this->attributes['hora_cierre'], 0, 8);
            $ts = strtotime("{$fecha} {$hora}");
            if ($ts !== false && time() >= $ts) {
                return 'Cerrada';
            }
        }
        return $value;
    }

    public function actividad()
    {
        return $this->belongsTo(Actividad::class, 'actividad_id');
    }

    public function documentos()
    {
        return $this->hasMany(OfertaDocumento::class, 'licitacion_id');
    }
}

