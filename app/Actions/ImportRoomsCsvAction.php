<?php

namespace App\Actions;

use App\Models\Building;
use App\Models\Room;
use App\Services\RoomBedService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ImportRoomsCsvAction
{
    /**
     * @throws InvalidArgumentException
     */
    public function execute(UploadedFile $csvFile, RoomBedService $roomBedService): int
    {
        $requiredHeaders = [
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
        ];

        $handle = fopen($csvFile->getRealPath(), 'rb');

        if ($handle === false) {
            throw new InvalidArgumentException('Unable to read uploaded CSV file.');
        }

        $headerRow = fgetcsv($handle);

        if (!is_array($headerRow)) {
            fclose($handle);
            throw new InvalidArgumentException('CSV file must include a header row.');
        }

        $headers = array_map(static fn(string $value): string => trim($value), $headerRow);

        if ($headers !== $requiredHeaders) {
            fclose($handle);
            throw new InvalidArgumentException('CSV header format is invalid. Please use the exported template.');
        }

        $processed = 0;

        DB::transaction(function () use ($handle, &$processed, $roomBedService): void {
            while (($row = fgetcsv($handle)) !== false) {
                if ($this->isEmptyRow($row)) {
                    continue;
                }

                $data = $this->mapRow($row);
                $this->validateRow($data);

                $building = Building::query()->where('code', $data['building_code'])->first();

                if (!$building) {
                    throw new InvalidArgumentException('Unknown building code: ' . $data['building_code']);
                }

                $room = Room::query()->updateOrCreate(
                    ['code' => $data['code']],
                    [
                        'building_id' => $building->id,
                        'name' => $data['name'],
                        'floor' => (int) $data['floor'],
                        'room_type' => $data['room_type'],
                        'status' => $data['status'],
                        'base_nightly_rate' => (float) $data['base_nightly_rate'],
                        'maximum_guests' => (int) $data['maximum_guests'],
                        'notes' => $data['notes'] !== '' ? $data['notes'] : null,
                        'is_active' => (int) $data['is_active'] === 1,
                    ],
                );

                $roomBedService->sync($room, [
                    'queen_bed_quantity' => (int) $data['queen_bed_quantity'],
                    'sofa_bed_quantity' => (int) $data['sofa_bed_quantity'],
                ]);

                $processed++;
            }
        });

        fclose($handle);

        return $processed;
    }

    /**
     * @param array<int, string|null> $row
     * @return array<string, string>
     */
    private function mapRow(array $row): array
    {
        return [
            'building_code' => trim((string) ($row[0] ?? '')),
            'name' => trim((string) ($row[1] ?? '')),
            'code' => trim((string) ($row[2] ?? '')),
            'floor' => trim((string) ($row[3] ?? '')),
            'room_type' => trim((string) ($row[4] ?? '')),
            'status' => trim((string) ($row[5] ?? '')),
            'base_nightly_rate' => trim((string) ($row[6] ?? '')),
            'maximum_guests' => trim((string) ($row[7] ?? '')),
            'notes' => trim((string) ($row[8] ?? '')),
            'is_active' => trim((string) ($row[9] ?? '')),
            'queen_bed_quantity' => trim((string) ($row[10] ?? '')),
            'sofa_bed_quantity' => trim((string) ($row[11] ?? '')),
        ];
    }

    /**
     * @param array<string, string> $data
     */
    private function validateRow(array $data): void
    {
        if ($data['building_code'] === '' || $data['name'] === '' || $data['code'] === '') {
            throw new InvalidArgumentException('CSV row has missing required room identity fields.');
        }

        if (!in_array($data['status'], Room::STATUSES, true)) {
            throw new InvalidArgumentException('Invalid room status in CSV: ' . $data['status']);
        }

        if (!is_numeric($data['floor']) || (int) $data['floor'] < 1) {
            throw new InvalidArgumentException('Floor must be a positive integer.');
        }

        if (!is_numeric($data['base_nightly_rate']) || (float) $data['base_nightly_rate'] < 0) {
            throw new InvalidArgumentException('Base nightly rate must be a valid positive number.');
        }

        if (!is_numeric($data['maximum_guests']) || (int) $data['maximum_guests'] < 1) {
            throw new InvalidArgumentException('Maximum guests must be a positive integer.');
        }

        foreach (['is_active', 'queen_bed_quantity', 'sofa_bed_quantity'] as $intField) {
            if (!preg_match('/^\d+$/', $data[$intField])) {
                throw new InvalidArgumentException('CSV value for ' . $intField . ' must be a non-negative integer.');
            }
        }
    }

    /**
     * @param array<int, string|null> $row
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}
