<?php

namespace Database\Seeders;

use App\Models\ServiceUnit;
use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            [
                'name' => 'kwh',
                'description' => 'Tính chênh lệch đồng hồ điện'
            ],
            [
            'name' => 'cubic_meter',
            'description' => 'Tính chênh lệch đồng hồ nước'
            ],
            [
                'name' => 'month',
                'description' => 'Dịch vụ tính theo tháng như wifi, rác, vệ sinh,...'
            ],
            [
                'name' => 'person',
                'description' => 'Dịch vụ tính theo số thành viên đang thuê'
            ],
            [
                'name' => 'item',
            ],
            [
                'name' => 'time',
            ],
            [
                'name' => 'piece',
            ],
            [
                'name' => 'container',
            ],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
