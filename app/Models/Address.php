<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'phone',
        'address',
        'city',
        'state',
        'country_id',
        'postal_code',
        'plus_code',
    ];
    
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
