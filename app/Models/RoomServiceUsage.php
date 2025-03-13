<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RoomServiceUsage extends Model
{
    protected $table = 'room_service_usages';

    protected $fillable = [
        'id',
        'room_id',
        'lodging_service_id',
        'total_price',
        'amount_paid',
        'value',
        'finalized',
        'month_billing',
        'year_billing',
        'is_finalized_early',
    ];

    protected $primaryKey = "id";
    protected $keyType = "string";
    public $incrementing = false;

    protected static function boot(){
        parent::boot();
        static::creating(function ($model) {
            if(empty($model->id)){
                $model->id  = Str::uuid();
            }
        });
    }

    public function room() {
        return $this->belongsTo(Room::class, 'room_id');
    }
}
