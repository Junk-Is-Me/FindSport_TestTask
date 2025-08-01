<?php

namespace App\Http\Controllers;

use App\Services\BookingService;
use App\Services\BookingSlotService;
use Illuminate\Http\Request;
use App\Models\Booking;

class BookingController extends Controller
{
    protected BookingService $bookingService;
    protected BookingSlotService $slotService;

    public function __construct(BookingService $bookingService, BookingSlotService $slotService)
    {
        $this->bookingService = $bookingService;
        $this->slotService = $slotService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $booking = Booking::with('slot')->where('user_id', $user->id)->get();

        return response()->json($booking);
    }

    public function store(Request $request)
    {
        $request->validate([
            'slots' => 'required|array|min:1',
            'slots.*.start_time' => 'required|date',
            'slots.*.end_time' => 'required|date|after:slots.*.start_time',
        ]);

        try {
            $booking = $this->bookingService->createBookingWithSlots($request->user(), $request->slots);

            return response()->json(['message' => 'Booking created', 'id' => $booking->id], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function updateSlot(Request $request, Booking $booking, $slotId)
    {
        $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        try {
            $slot = $this->slotService->updateSlot($booking, $slotId, $request->only('start_time', 'end_time'));

            return response()->json(['message' => 'Slot updated', 'id' => $booking->id], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function addSlot(Request $request, Booking $booking)
    {
        $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        try {
            $slot = $this->slotService->addSlot($booking, $request->only('start_time', 'end_time'));

            return response()->json(['message' => 'Slot added', 'slot' => $slot], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function deleteBooking(Booking $booking)
    {
        try {
            $this->bookingService->deleteBooking($booking);

            return response()->json(['message' => 'Booking deleted']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}


