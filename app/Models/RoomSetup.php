<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomSetup extends Model
{
    use SoftDeletes;
    protected $table = 'room_setups';
    protected $fillable = [
      'room_id', 'equipment_id', 'status', 'quantity', 'installation_date', 'last_serviced'];

    protected $dates = ['deleted_at'];
    public $timestamps = true;

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }
}
