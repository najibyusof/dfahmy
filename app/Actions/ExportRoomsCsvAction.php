<?php

namespace App\Actions;

use App\Models\Room;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportRoomsCsvAction
{
    public function execute(): StreamedResponse
    {
        $fileName = 'rooms-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'wb');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, [
                'building_code',
                'name',
                'code',
                'floor',
                'room_type',
                'status',
                'base_nightly_rate',
                'maximum_guests',
                'notes',
                'is_active',
                'queen_bed_quantity',
                'sofa_bed_quantity',
            ]);

            Room::query()
                ->with(['building:id,code', 'beds'])
                ->orderBy('code')
                ->cursor()
                ->each(function (Room $room) use ($handle): void {
                    $queen = (int) $room->beds->firstWhere('bed_type', 'queen_bed')?->quantity;
                    $sofa = (int) $room->beds->firstWhere('bed_type', 'sofa_bed')?->quantity;

                    fputcsv($handle, [
                        $room->building->code,
                        $room->name,
                        $room->code,
                        $room->floor,
                        $room->room_type,
                        $room->status,
                        (float) $room->base_nightly_rate,
                        $room->maximum_guests,
                        $room->notes,
                        $room->is_active ? 1 : 0,
                        $queen,
                        $sofa,
                    ]);
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
