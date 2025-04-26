<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Contract extends Model
{
    protected $table = 'contracts';
    protected $fillable = [
        'id',
        'user_id',
        'room_id',
        'start_date',
        'end_date',
        'remain_amount',
        'deposit_amount',
        'monthly_rent',
        'status',
        'lease_duration',
        'quantity',
        'full_name',
        'phone',
        'gender',
        'address',
        'identity_card',
        'date_of_birth',
        'relatives',
        'code',
        'has_been_billed'
    ];

    protected $keyType = "string";
    public $incrementing = false;
    protected $primaryKey = 'id';

    protected $dateFormat = 'Y-m-d H:i:s';
    protected $casts = [
      'relatives' => 'array',
      'lease_duration' => 'integer',
      'quantity' => 'integer',
      'full_name' => 'string',
      'phone' => 'string',
      'gender' => 'boolean',
      'address' => 'string',
      'identity_card' => 'string',

      'remain_amount' => 'decimal:2',
      'monthly_rent' => 'decimal:2',
      'status' => 'integer',
    ];

    protected $hidden = ['created_at','updated_at'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if(empty($model->id)){
                $id = Str::uuid();
                $model->id = $id;
                $model->code = Helper::generateUniqueCode($id, $model->room_id);
            }

            if($model->status == config('constant.contract.status.active')){
                self::addChannelMember($model);
            }
        });

        static::updating(function ($model) {
            if ($model->status == config('constant.contract.status.active')) {
                self::addChannelMember($model);
            } else {
                self::removeChannelMember($model);
            }
        });
    }

    protected static function addChannelMember($model)
    {
        $channel = Channel::where('room_id', $model->room_id)->first();
        if (!$channel) {
           return;
        }

        ChannelMember::firstOrCreate(
            [
                'channel_id' => $channel->id,
                'member_id' => $model->user_id,
                'member_type' => config('constant.object.type.user'),
            ],
            [
                'joined_at' => now(),
            ]
        );
    }

    protected static function removeChannelMember($model)
    {
        $channel = Channel::where('room_id', $model->room_id)->first();
        if (!$channel) {
            return;
        }

        $activeContracts = Contract::where([
            'room_id' => $model->room_id,
            'status' => config('constant.contract.status.active'),
            'user_id' => $model->user_id,
        ])->where('id', '!=', $model->id)->count();

        if($activeContracts !=  0){
            return;
        }

        ChannelMember::where([
            'channel_id' => $channel->id,
            'member_id' => $model->user_id,
            'member_type' => config('constant.object.type.user'),
        ])->delete();
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id')->with('lodging');
    }

    public function rentalHistories() {
        return $this->hasMany(RentPayment::class, 'contract_id');
    }

}
