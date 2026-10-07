<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'housing_unit_id',
        'description',
        'urgency',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function housingUnit()
    {
        return $this->belongsTo(HousingUnit::class);
    }
}