<?php

namespace App\Console\Commands;

use App\Models\ChannelMember;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class FlushUserChannelState extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flush:user-channel-state';
    protected $description = 'Flush user channel lastLeftAt from Redis to Database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $pattern = "*:channel:*:lastLeftAt";
        $cursor = null;
        $batchData = [];
        do{
            [$cursor, $keys] = Redis::scan($cursor, 'MATCH', $pattern, 'COUNT', 1000);

            foreach ($keys as $key) {
                try{
                    $timestamp = Redis::get($key);

                    if (!$timestamp) continue;

                    $parts = explode(":", $key);

                    $objectId = $parts[0] ?? null;
                    $channelId = $parts[2] ?? null;

                    if(!$objectId || !$channelId) continue;

                    $batchData[] = [
                        'member_id' => $objectId,
                        'channel_id' => $channelId,
                        'last_left_at' => now()->setTimestamp($timestamp),
                    ];

                    // Xóa key Redis luôn, sau khi backup vào batchData
                    Redis::del($key);
                } catch (\Exception $exception) {
                    Log::error('Error processing key ' . $key . ': ' . $exception->getMessage());
                    continue;
                }
            }

            if (count($batchData) >= 1000) {
                $this->bulkUpsert($batchData);
                $batchData = []; // Clear batch sau khi flush
            }
        } while ($cursor != 0);

        if (!empty($batchData)) {
            $this->bulkUpsert($batchData);
        }

//        Log::info('Flushed user channel states successfully!');
    }

    private function bulkUpsert(array $data)
    {
        if (empty($data)) {
            return;
        }

        foreach ($data as $row) {
            if (!isset($row['member_id']) || !isset($row['channel_id'])) {
                continue;
            }

            ChannelMember::where([
                'member_id' => $row['member_id'],
                'channel_id' => $row['channel_id']])
                ->update([
                    'last_left_at' => $row['last_left_at'],
                ]);
        }
    }
}
