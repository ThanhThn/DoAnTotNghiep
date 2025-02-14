<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $commonly = [[
            'name' => "holding_room",
            'type' => 'commonly',
            'end_point' => '/contact/holding',
            'description' => 'Khách thuê đặt cọc giữ chỗ khi đã quyết đinh thuê nhưng chưa vào ở.'
        ],
        [
            'name' => "create_contact",
            'type' => 'commonly',
            'end_point' => '/contact/create',
            'description' => 'Lập hợp đồng mới khi có khách thuê nhận phòng.'
        ],
        [
            'name' => "delete_contact",
            'type' => 'commonly',
            'end_point' => '/contact/delete',
            'description' => 'Thực hiện thanh lý phòng khi khách muốn trả phòng hoặc đã hết thời hạn hợp đồng.'
        ]];

        $management = [
            [
                'name' => "room_management",
                'type' => 'management',
                'end_point' => '/room/management',
                'description' => 'Hiển thị danh sách tất cả các phòng của nhà trọ kèm trạng thái tương ứng, có thể xem chi tiết hoặc thêm/xoá/chỉnh sửa thông tin phòng.'
            ],
            [
                'name' => "service_management",
                'type' => 'management',
                'end_point' => '/service/management',
                'description' => 'Quản lý (thêm/xoá/sửa) các dịch vụ phòng sử dụng như điện, nước, wifi, rác...'
            ],
            [
                'name' => "equipment_management",
                'type' => 'management',
                'end_point' => '/equipment/management',
                'description' => 'Quản lý (thêm/xoá/sửa) các trang thiết bị phòng sử dụng như bếp, tủ lạnh, máy giặt...'
            ]
        ];

        foreach ($commonly as $common) {
            Permission::create($common);
        }

        foreach ($management as $manage) {
            Permission::create($manage);
        }
    }
}
