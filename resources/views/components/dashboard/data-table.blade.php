{{-- Reusable data table component for CRUD list pages --}}
@props(['title' => '', 'createUrl' => '', 'createLabel' => __('common.create')])

<div>
    {{-- Page header --}}
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <h1 style="font-size: 24px; font-weight: 700; color: var(--text-primary, #1C1917); margin: 0;">{{ $title }}</h1>
        @if ($createUrl)
            <a href="{{ $createUrl }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: var(--color-brand-500, #520000); color: #FFFFFF; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                {{ $createLabel }}
            </a>
        @endif
    </div>

    {{-- Search + filters slot --}}
    @if (isset($filters))
        <div style="margin-bottom: 16px;">
            {{ $filters }}
        </div>
    @endif

    {{-- Table --}}
    <div style="background: var(--surface-card, #FFFFFF); border: 1px solid var(--border-default, #E7E5E4); border-radius: 8px; box-shadow: var(--shadow-sm); overflow: hidden;">
        {{ $slot }}
    </div>

    {{-- Pagination slot --}}
    @if (isset($pagination))
        <div style="margin-top: 12px;">
            {{ $pagination }}
        </div>
    @endif
</div>
