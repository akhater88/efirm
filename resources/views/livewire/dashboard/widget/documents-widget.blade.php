<x-dashboard.widget-card
    :title="__('dashboard.widget_documents')"
    :state="$documents->isEmpty() ? 'empty' : 'default'"
    :empty-message="__('dashboard.no_recent_documents')"
    view-all-url="/app/documents"
>
    <div style="padding: 0;">
        @foreach ($documents as $document)
            <div style="
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 10px 16px;
                border-bottom: 1px solid var(--border-default, #E7E5E4);
            ">
                <span style="flex-shrink: 0; color: var(--text-tertiary, #7A5050);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                </span>
                <div style="min-width: 0; flex: 1;">
                    <div style="font-size: 13px; font-weight: 500; color: var(--text-primary, #2D0A0A); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $document->title }}
                    </div>
                    @if ($document->matter)
                        <div style="font-size: 12px; color: var(--text-tertiary, #7A5050); margin-top: 1px;">
                            {{ $document->matter->title }}
                        </div>
                    @endif
                </div>
                <span style="flex-shrink: 0; font-size: 11px; color: var(--text-tertiary, #7A5050);">
                    {{ $document->updated_at->diffForHumans() }}
                </span>
            </div>
        @endforeach
    </div>
</x-dashboard.widget-card>
