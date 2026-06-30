<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carrera extends Model
{
    protected $table = 'carreras';

    public $timestamps = false;

    protected $fillable = [
        'carrera',
        'descripcion',
        'malla_curricular',
    ];

    public function estudiantes(): HasMany
    {
        return $this->hasMany(Estudiante::class, 'carrera_id');
    }
}
