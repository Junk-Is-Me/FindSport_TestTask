<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingSlot;

class BookingSlotService
{
    // Проверка пересечений внутри переданных слотов
    public function checkInternalOverlaps(array $slots)
    {
        $countSlots = count($slots);


        for ($i = 0; $i < $countSlots; $i++) {
            for ($j = $i + 1; $j < $countSlots; $j++) {
                if ($this->hasOverlap($slots[$i], $slots[$j])) {
                    throw new \Exception('Updated slot overlaps with other slots in this booking');
                }
            }
        }
    }

    // Проверка пересечений с уже существующими слотами
    public function checkExternalOverlaps(array $slots)
    {
        foreach ($slots as $slot) {
            $conflict = BookingSlot::where('start_time', '<', $slot['end_time'])
                ->where('end_time', '>', $slot['start_time'])
                ->exists();

            if ($conflict) {
                throw new \Exception("Slot conflict with existing bookings");
            }
        }
    }

    // Добавление слота к бронированию с проверками
    public function addSlot(Booking $booking, array $slotData)
    {
        $existingSlots = $booking->slot()->get()->toArray();
        $slotsToCheck = array_merge($existingSlots, [$slotData]);

        $this->checkInternalOverlaps($slotsToCheck);
        $this->checkExternalOverlaps([$slotData]);

        return $booking->slot()->create($slotData);
    }

    // Обновление слота с проверками
    public function updateSlot(Booking $booking, int $slotId, array $data)
    {
        $slot = $booking->slot()->findOrFail($slotId);

        $otherSlots = $booking->slot()->where('id', '!=', $slotId)->get()->toArray();
        $slotsToCheck = array_merge($otherSlots, [$data]);

        $this->checkInternalOverlaps($slotsToCheck);

        // Проверяем внешние пересечения, исключая текущий слот
        $conflict = BookingSlot::where('id', '!=', $slotId)
            ->where('start_time', '<', $data['end_time'])
            ->where('end_time', '>', $data['start_time'])
            ->exists();

        if ($conflict) {
            throw new \Exception('Slot conflict with existing bookings');
        }

        $slot->update($data);

        return $slot;
    }

    // Проверка пересечения двух слотов
    private function hasOverlap(array $firstSlot, array $secondSlot): bool
    {
        return $firstSlot['start_time'] < $secondSlot['end_time'] && $firstSlot['end_time'] > $secondSlot['start_time'];
    }
}
