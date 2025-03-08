<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomSetup extends Model
{
    protected $table = 'room_setups';
    protected $fillable = [
      'room_id', 'equipment_id', 'status', 'quantity', 'installation_date', 'last_serviced'];
}
