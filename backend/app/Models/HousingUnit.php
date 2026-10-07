<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HousingUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_number',
        'capacity',
        'status',
    ];

    public function placements()
    {
        return $this->hasMany(Placement::class);
    }

    public function maintenanceTickets()
    {
        return $this->hasMany(MaintenanceTicket::class);
    }
}