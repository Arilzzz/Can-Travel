<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'province'];

    public function routesAsOrigin()
    {
        return $this->hasMany(Route::class, 'origin_city_id');
    }

    public function routesAsDestination()
    {
        return $this->hasMany(Route::class, 'destination_city_id');
    }
}
