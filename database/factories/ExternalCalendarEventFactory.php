<?php

namespace Database\Factories;

use App\Models\CalendarIntegration;
use App\Models\ExternalCalendarEvent;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExternalCalendarEvent>
 */
class ExternalCalendarEventFactory extends Factory
{
    protected $model = ExternalCalendarEvent::class;

    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('now', '+30 days');
        $endsAt = (clone $startsAt)->modify('+1 hour');

        return [
            'workspace_id' => Workspace::factory(),
            'user_id' => User::factory(),
            'calendar_integration_id' => CalendarIntegration::factory(),
            'provider_event_id' => fake()->unique()->uuid(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'timezone' => 'Asia/Amman',
            'is_all_day' => false,
            'attendees' => [['email' => fake()->safeEmail(), 'name' => fake()->name()]],
            'location' => fake()->address(),
            'linked_matter_id' => null,
            'linked_hearing_id' => null,
            'last_synced_at' => now(),
        ];
    }
}
