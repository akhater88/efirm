<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\Matter;

class GlobalSearchService
{
    /**
     * Search across Contacts and Matters within the current workspace scope.
     *
     * @return array{groups: array<int, array{type: string, label: string, results: array}>}
     */
    public function search(string $query, int $limit = 10): array
    {
        $groups = [];

        $contacts = Contact::where('display_name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->latest('updated_at')
            ->limit($limit)
            ->get(['id', 'type', 'display_name', 'email', 'is_client', 'is_counterparty']);

        if ($contacts->isNotEmpty()) {
            $groups[] = [
                'type' => 'contacts',
                'label' => __('contacts.contacts'),
                'results' => $contacts->map(fn ($c) => [
                    'id' => $c->id,
                    'title' => $c->display_name,
                    'subtitle' => $c->email,
                    'type' => $c->type,
                    'is_client' => $c->is_client,
                    'is_counterparty' => $c->is_counterparty,
                ])->toArray(),
            ];
        }

        $matters = Matter::where('title', 'LIKE', "%{$query}%")
            ->orWhere('internal_reference', 'LIKE', "%{$query}%")
            ->latest('updated_at')
            ->limit($limit)
            ->get(['id', 'title', 'status', 'practice_area', 'internal_reference']);

        if ($matters->isNotEmpty()) {
            $groups[] = [
                'type' => 'matters',
                'label' => __('matters.matters'),
                'results' => $matters->map(fn ($m) => [
                    'id' => $m->id,
                    'title' => $m->title,
                    'subtitle' => $m->internal_reference,
                    'status' => $m->status?->value,
                    'practice_area' => $m->practice_area?->value,
                ])->toArray(),
            ];
        }

        return ['groups' => $groups];
    }
}
