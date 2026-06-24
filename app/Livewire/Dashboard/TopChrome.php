<?php

namespace App\Livewire\Dashboard;

use App\Models\Matter;
use App\Services\QuickTimerService;
use Livewire\Component;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class TopChrome extends Component
{
    public bool $showSearchModal = false;

    public bool $showQuickAddMenu = false;

    public ?string $activeTimerMatterTitle = null;

    public ?string $activeTimerElapsed = null;

    public ?string $activeTimerId = null;

    public function mount(): void
    {
        $this->refreshTimer();
    }

    public function refreshTimer(): void
    {
        $timer = app(QuickTimerService::class)->getActiveTimerForUser(auth()->user());

        if ($timer) {
            $this->activeTimerId = $timer->id;
            $this->activeTimerMatterTitle = $timer->matter?->title;
            $this->activeTimerElapsed = $timer->started_at->diffForHumans(now(), true);
        } else {
            $this->activeTimerId = null;
            $this->activeTimerMatterTitle = null;
            $this->activeTimerElapsed = null;
        }
    }

    public function startTimerForMatter(string $matterId): void
    {
        $matter = Matter::findOrFail($matterId);
        $this->authorize('view', $matter);

        try {
            app(QuickTimerService::class)->startForMatter($matter, auth()->user());
            $this->refreshTimer();
        } catch (ConflictHttpException) {
            $this->dispatch('notify', message: __('shell.timer_already_active'), type: 'warning');
        }
    }

    public function stopTimer(): void
    {
        $timer = app(QuickTimerService::class)->getActiveTimerForUser(auth()->user());

        if ($timer) {
            app(QuickTimerService::class)->stop($timer, auth()->user());
            $this->refreshTimer();
            $this->dispatch('notify', message: __('shell.timer_stopped'), type: 'success');
        }
    }

    public function openChat(): void
    {
        $this->dispatch('notify', message: __('shell.chat_coming_soon'), type: 'info');
    }

    public function render()
    {
        $user = auth()->user();
        $workspace = $user->currentWorkspace();

        $recentMatters = Matter::query()
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get(['id', 'title']);

        $notificationCount = $user->unreadNotifications()->count();

        return view('livewire.dashboard.top-chrome', [
            'workspace' => $workspace,
            'user' => $user,
            'recentMatters' => $recentMatters,
            'notificationCount' => $notificationCount,
        ]);
    }
}
