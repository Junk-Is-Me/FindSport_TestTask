<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\DB;

class BookingService
{
    protected BookingSlotService $slotService;

    public function __construct(BookingSlotService $slotService)
    {
        $this->slotService = $slotService;
    }

    // Создание бронирования с слотами
    public function createBookingWithSlots($user, array $slots)
    {
        // Проверяем пересечения слотов
        $this->slotService->checkInternalOverlaps($slots);
        $this->slotService->checkExternalOverlaps($slots);

        return DB::transaction(function () use ($user, $slots) {
            $booking = Booking::create(['user_id' => $user->id]);

            foreach ($slots as $slot) {
                $booking->slot()->create($slot);
            }

            return $booking;
        });
    }

    // Удаление бронирования вместе со слотами
    public function deleteBooking(Booking $booking)
    {
        return DB::transaction(function () use ($booking) {
            $booking->slot()->delete();
            $booking->delete();
        });
    }
}
