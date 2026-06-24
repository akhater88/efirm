{{-- 2×2 top widget grid + 2-col feed strip bottom --}}
<div>
    {{-- Top grid: 2×2 --}}
    <div style="
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        margin-bottom: 16px;
    " class="widget-grid-top">
        {{ $topLeft ?? '' }}
        {{ $topRight ?? '' }}
        {{ $bottomLeft ?? '' }}
        {{ $bottomRight ?? '' }}
    </div>

    {{-- Bottom feeds: 2-col --}}
    @if (isset($feedLeft) || isset($feedRight))
        <div style="
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        " class="widget-grid-feeds">
            {{ $feedLeft ?? '' }}
            {{ $feedRight ?? '' }}
        </div>
    @endif
</div>

@once
<style>
    @media (max-width: 1023px) {
        .widget-grid-top,
        .widget-grid-feeds {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endonce
