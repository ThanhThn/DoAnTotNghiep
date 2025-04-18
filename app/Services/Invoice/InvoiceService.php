<?php

namespace App\Services\Invoice;

use App\Models\RoomRentalHistory;
use App\Models\RoomServiceUsage;
use App\Services\RoomUsageService\RoomUsageService;

class InvoiceService
{
    function list($data)
    {
        try {
            $model = match ($data['type']) {
                "rent" => RoomRentalHistory::class,
                "service" => RoomServiceUsage::class,
                default => throw new \Exception('Loại hoá đơn không hỗ trợ'),
            };

            $service = (new $model())->on('pgsqlReplica');

            if ($data['type'] === 'rent') {
                $service = $service->with('room');
            } else {
                $service = $service->with(['room', 'unit', 'service']);
            }

            // Filters
            if (!empty($data['room_code'])) {
                $roomCode = trim($data['room_code']);
                $service = $service->whereHas('room', function ($query) use ($roomCode) {
                    $query->where('room_code', 'ILIKE', "%{$roomCode}%");
                });
            }

            if (!empty($data['month']) && !empty($data['year'])) {
                $service = $service->where([
                    'month_billing' => $data['month'],
                    'year_billing' => $data['year'],
                ]);
            }

            if (!empty($data['status'])) {
                $status = strtolower($data['status']);
                if ($status === 'paid') {
                    $service = $service->whereColumn('total_price', '=', 'amount_paid');
                } elseif ($status === 'unpaid') {
                    $service = $service->whereColumn('total_price', '>', 'amount_paid');
                }
            }

            $total = $service->count();

            $offset = intval($data['offset'] ?? 0);
            $limit = intval($data['limit'] ?? 10);

            $result = $service->orderBy('created_at', 'desc')
                ->offset($offset)
                ->limit($limit)
                ->get();

            return [
                'total' => $total,
                'data' => $result,
            ];

        } catch (\Exception $exception) {
            return [
                'errors' => [[
                    'message' => $exception->getMessage(),
                ]]
            ];
        }
    }

}
