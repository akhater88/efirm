<?php

namespace App\Livewire\Dashboard;

use App\Models\AiTwinWaitlistEntry;
use Illuminate\Support\Carbon;
use Livewire\Component;

class DashboardHero extends Component
{
    public bool $showAiTwinModal = false;

    public string $waitlistEmail = '';

    public function getGreeting(): string
    {
        $hour = Carbon::now()->hour;
        $name = auth()->user()->name;

        if ($hour < 12) {
            return __('dashboard.greeting_morning', ['name' => $name]);
        }

        if ($hour < 17) {
            return __('dashboard.greeting_afternoon', ['name' => $name]);
        }

        return __('dashboard.greeting_evening', ['name' => $name]);
    }

    public function getFormattedDate(): string
    {
        return Carbon::now()->translatedFormat('l، j F Y');
    }

    public function submitWaitlist(): void
    {
        $this->validate([
            'waitlistEmail' => ['required', 'email:rfc', 'max:255'],
        ]);

        AiTwinWaitlistEntry::firstOrCreate(
            ['email' => $this->waitlistEmail],
            [
                'locale' => app()->getLocale(),
                'workspace_id' => auth()->user()->currentWorkspace()?->id,
            ]
        );

        $this->dispatch('notify', message: __('brand.waitlist_success'), type: 'success');
        $this->showAiTwinModal = false;
        $this->reset('waitlistEmail');
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-hero', [
            'greeting' => $this->getGreeting(),
            'formattedDate' => $this->getFormattedDate(),
        ]);
    }
}
