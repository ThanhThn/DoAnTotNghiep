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


    public $incrementing = false;

    protected $primaryKey = null;
    protected $dates = ['deleted_at'];
    public $timestamps = true;


    public function restore(array $options = [])
    {
        return static::withTrashed()
            ->where('equipment_id', $this->equipment_id)
            ->where('room_id', $this->room_id)
            ->update(['deleted_at' => null]);
    }
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function room(): BelongsTo{
        return $this->belongsTo(Room::class);
    }
}
