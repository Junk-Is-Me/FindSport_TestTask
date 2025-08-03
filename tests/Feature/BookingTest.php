<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\User;


class BookingTest extends TestCase
{
    use RefreshDatabase;
    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::first();

        if (!$this->user) {
            $this->user = User::factory()->create();
        }

        $this->token = $this->user->api_token;
    }

    public function test_create_booking_with_multiple_slots()
    {
        $slots = [
            ['start_time' => '2025-08-10 10:00:00', 'end_time' => '2025-08-10 11:00:00'],
            ['start_time' => '2025-08-10 11:30:00', 'end_time' => '2025-08-10 12:30:00'],
        ];

        $response = $this->postJson(
            '/api/bookings',
            [
                'slots' => $slots
            ],
            [
                'Authorization' => 'Bearer ' . $this->token
            ]
        );

        $response->assertStatus(201);
        $response->assertJsonStructure(['message', 'id']);
        $response->assertJson(['message' => 'Booking created']);
        $this->assertDatabaseCount('booking_slots', 2);
        $this->assertIsInt($response->json('id'));
    }

    public function test_fail_to_add_slot_with_conflict()
    {
        $now = Carbon::now();

        // Создаём бронирование с одним слотом
        $bookingResponse = $this->postJson(
            '/api/bookings',
            [
                'slots' => [
                    ['start_time' => $now->toIso8601String(), 'end_time' => $now->copy()->addHour()->toIso8601String()],
                ],
            ],
            [
                'Authorization' => 'Bearer ' . $this->token
            ]
        );

        $bookingResponse->assertStatus(201);
        $bookingId = $bookingResponse->json('id');

        // Пытаемся добавить конфликтующий слот через addSlot API
        $response = $this->postJson(
            "/api/bookings/{$bookingId}/slots",
            [
                'start_time' => $now->copy()->addMinutes(30)->toIso8601String(),
                'end_time' => $now->copy()->addHour()->addMinutes(30)->toIso8601String(),
            ],
            [
                'Authorization' => 'Bearer ' . $this->token
            ]
        );

        $response->assertStatus(422);
        $response->assertJson(['message' => 'Slot conflict with existing bookings']);
    }

    public function test_update_slot_successfully()
    {
        $now = Carbon::now();

        // Создаём бронирование с одним слотом
        $bookingResponse = $this->postJson(
            '/api/bookings',
            [
                'slots' => [
                    [
                        'start_time' => $now->toIso8601String(),
                        'end_time' => $now->copy()->addHour()->toIso8601String()
                    ],
                ],
            ],
            [
                'Authorization' => 'Bearer ' . $this->token
            ]
        );
        $bookingResponse->assertStatus(201);
        $bookingId = $bookingResponse->json('id');

        $slot = BookingSlot::where('booking_id', $bookingId)->firstOrFail();

        // Обновляем слот с новым временем без конфликта
        $response = $this->patchJson(
            "/api/bookings/{$bookingId}/slots/{$slot->id}",
            [
                'start_time' => $now->copy()->addHours(2)->toIso8601String(),
                'end_time' => $now->copy()->addHours(3)->toIso8601String(),
            ],
            [
                'Authorization' => 'Bearer ' . $this->token
            ]
        );

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Slot updated',
            'id' => $bookingId,
        ]);
    }

    public function test_reject_unauthenticated_request()
    {
        $response = $this->postJson('/api/bookings', [
            'slots' => [
                [
                    'start_time' => '2025-08-10T10:00:00',
                    'end_time' => '2025-08-10T11:00:00'
                ],
            ],
        ]);

        $response->assertStatus(401);
        $response->assertJsonStructure([
            'message',
        ]);
    }
}
