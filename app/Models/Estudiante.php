<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Estudiante extends Model
{
    protected $table = 'estudiantes';

    public $timestamps = false;

    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_ENVIADO = 'enviado';

    protected $fillable = [
        'nombre',
        'correo',
        'celular',
        'carrera_id',
        'estado',
        'fecha_solicitud',
        'fecha_envio',
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'fecha_envio' => 'datetime',
    ];

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
    }
}
