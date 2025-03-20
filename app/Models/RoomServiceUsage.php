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
        'initial_index',
        'final_index',
        'month_billing',
        'year_billing',
        'is_need_close',
        'unit_id',
        'service_id',
        'service_name',
    ];

    protected $primaryKey = "id";
    protected $keyType = "string";
    public $incrementing = false;

    protected $hidden = ['created_at','updated_at'];

    protected static function boot(){
        parent::boot();
        static::creating(function ($model) {
            if(empty($model->id)){
                $model->id  = Str::uuid();
            }
        });

        static::updating(function ($model) {
            if(!empty($model->initial_index) && !empty($model->final_index) && $model->final_index < $model->initial_index){
                throw new \Exception('Cannot update model: final_index smaller than initial_index.');
            }

            if($model->value < 0){
                throw new \Exception('Cannot update model: Value must be greater than 0.');
            }
        });
    }

    public function room() {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function lodgingService()
    {
        return $this->belongsTo(LodgingService::class, 'lodging_service_id')->with(['unit', 'service']);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function service(){
        return $this->belongsTo(Service::class, 'service_id');
    }

}
