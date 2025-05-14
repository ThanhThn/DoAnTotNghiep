<?php

namespace App\Jobs;

use App\Models\Contract;
use App\Models\Room;
use App\Services\Notification\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class HandleOverdueContract implements ShouldQueue
{
    use Queueable;

    private Contract $contract;
    /**
     * Create a new job instance.
     */
    public function __construct(Contract $contract)
    {
        $this->contract = $contract;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->contract->update([
            'status' => config('constant.contract.status.overdue')
        ]);

        $this->contract->load('user');

        $room = Room::on('pgsqlReplica')->with("lodging")->find($this->contract->room_id);

        $lodging = $room->lodging;
        $notifyService = new NotificationService();

        if($this->contract->user_id){

            $nameLodgingWithType = strtolower($lodging->type->name). ' '. $lodging->name;

            $notifyService->createNotification([
                'title' => "Hợp đồng thuê phòng của bạn đã quá hạn",
                'body' => "Hợp đồng thuê phòng tại $nameLodgingWithType của bạn đã quá hạn. Vui lòng liên hệ với chủ trọ để gia hạn hợp đồng hoặc thanh toán khoản phí còn lại nếu có. Đảm bảo hoàn tất các thủ tục để tránh gián đoạn trong việc sử dụng phòng thuê.",
                'target_endpoint' => "/contract/detail/{$this->contract->id}",
                'type' => config('constant.notification.type.important')
            ], config('constant.object.type.user'), $this->contract->user_id, $this->contract->user_id);
        }

        $notifyService->createNotification([
            'title' => "Hợp đồng thuê của khách đã quá hạn",
            'body' => "Hợp đồng thuê phòng với khách {$this->contract->user->full_name} tại phòng {$room->room_code} đã quá hạn. Vui lòng kiểm tra và xử lý ngay để tránh ảnh hưởng đến công việc quản lý.",
            'target_endpoint' => "/lodging/{$lodging->id}/contract/detail/{$this->contract->id}",
            'type' => config('constant.notification.type.important')
        ], config('constant.object.type.lodging'), $lodging->id, $lodging->user_id, config('constant.rule.manager'));
    }
}
