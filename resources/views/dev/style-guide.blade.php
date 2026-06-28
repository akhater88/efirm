<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>eFirm Style Guide — Dev Only</title>
    @vite(['resources/css/app.css'])
    <style>
        body { margin: 0; padding: 32px; font-family: 'Source Sans Pro', system-ui, sans-serif; }
        .section { margin-bottom: 48px; }
        .section-title { font-size: 24px; font-weight: 700; margin-bottom: 16px; border-bottom: 2px solid var(--border-default, #E7E5E4); padding-bottom: 8px; }
        .swatch-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 8px; }
        .swatch { border-radius: 8px; padding: 12px; font-size: 11px; font-weight: 500; min-height: 60px; display: flex; flex-direction: column; justify-content: flex-end; }
        .type-row { margin-bottom: 12px; }
        .type-label { font-size: 12px; color: var(--text-tertiary); margin-bottom: 4px; }
    </style>
</head>
<body>
    <h1 style="font-family: 'Playfair Display', Georgia, serif; font-size: 36px; margin-bottom: 8px;">eFirm Style Guide</h1>
    <p style="color: var(--text-tertiary); margin-bottom: 32px;">SURGE-DASH-01 Wave 1 design tokens. Dev-only — gated behind <code>APP_ENV=local</code>.</p>

    {{-- Colors: Brand --}}
    <div class="section">
        <h2 class="section-title">Brand Colors</h2>
        <div class="swatch-grid">
            @foreach ([
                ['50', '#FDF2F2', '#000'], ['100', '#F9DADA', '#000'], ['200', '#F0ABAB', '#000'],
                ['300', '#E07070', '#000'], ['400', '#C93A3A', '#FFF'], ['500', '#520000', '#FFF'],
                ['600', '#440000', '#FFF'], ['700', '#330000', '#FFF'], ['800', '#260000', '#FFF'],
                ['900', '#1A0000', '#FFF'], ['950', '#0D0000', '#FFF'],
            ] as [$shade, $hex, $fg])
                <div class="swatch" style="background: {{ $hex }}; color: {{ $fg }};">
                    <span>brand-{{ $shade }}</span>
                    <span>{{ $hex }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Colors: Neutral --}}
    <div class="section">
        <h2 class="section-title">Neutral Colors</h2>
        <div class="swatch-grid">
            @foreach ([
                ['50', '#FAFAF9', '#000'], ['100', '#F5F5F4', '#000'], ['200', '#E7E5E4', '#000'],
                ['300', '#D6D3D1', '#000'], ['400', '#A8A29E', '#000'], ['500', '#78716C', '#FFF'],
                ['600', '#57534E', '#FFF'], ['700', '#44403C', '#FFF'], ['800', '#292524', '#FFF'],
                ['900', '#1C1917', '#FFF'], ['950', '#0C0A09', '#FFF'],
            ] as [$shade, $hex, $fg])
                <div class="swatch" style="background: {{ $hex }}; color: {{ $fg }};">
                    <span>neutral-{{ $shade }}</span>
                    <span>{{ $hex }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Colors: Semantic --}}
    <div class="section">
        <h2 class="section-title">Semantic Colors</h2>
        <div class="swatch-grid">
            @foreach ([
                ['success-50', '#F0FDF4', '#000'], ['success-500', '#15803D', '#FFF'], ['success-700', '#166534', '#FFF'],
                ['warning-50', '#FFFBEB', '#000'], ['warning-500', '#F59E0B', '#000'], ['warning-700', '#B45309', '#FFF'],
                ['danger-50', '#FEF2F2', '#000'], ['danger-500', '#DC2626', '#FFF'], ['danger-700', '#B91C1C', '#FFF'],
                ['info-50', '#EFF6FF', '#000'], ['info-500', '#2563EB', '#FFF'], ['info-700', '#1D4ED8', '#FFF'],
            ] as [$name, $hex, $fg])
                <div class="swatch" style="background: {{ $hex }}; color: {{ $fg }};">
                    <span>{{ $name }}</span>
                    <span>{{ $hex }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Colors: Tier --}}
    <div class="section">
        <h2 class="section-title">Tier Badges</h2>
        <div style="display: flex; gap: 12px;">
            <span style="background: #64748B; color: #FFF; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; padding: 4px 12px; border-radius: 4px;">Starter</span>
            <span style="background: #520000; color: #FFF; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; padding: 4px 12px; border-radius: 4px;">Pro</span>
            <span style="background: #D97706; color: #FFF; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; padding: 4px 12px; border-radius: 4px;">Enterprise</span>
        </div>
    </div>

    {{-- Typography --}}
    <div class="section">
        <h2 class="section-title">Typography</h2>

        <div class="type-row">
            <div class="type-label">font-display · Playfair Display 700</div>
            <div style="font-family: 'Playfair Display', Georgia, serif; font-size: 36px; font-weight: 700;">The quick brown fox jumps over the lazy dog</div>
        </div>

        <div class="type-row">
            <div class="type-label">font-sans · Source Sans Pro 400</div>
            <div style="font-family: 'Source Sans Pro', system-ui, sans-serif; font-size: 16px;">The quick brown fox jumps over the lazy dog — 0123456789</div>
        </div>

        <div class="type-row">
            <div class="type-label">font-sans · Source Sans Pro 600</div>
            <div style="font-family: 'Source Sans Pro', system-ui, sans-serif; font-size: 16px; font-weight: 600;">The quick brown fox jumps over the lazy dog</div>
        </div>

        <div class="type-row">
            <div class="type-label">font-arabic · IBM Plex Sans Arabic 400</div>
            <div style="font-family: 'IBM Plex Sans Arabic', Tahoma, sans-serif; font-size: 16px; direction: rtl;">كن أول من يعرف عند إطلاق المساعد الذكي — 0123456789</div>
        </div>

        <div class="type-row">
            <div class="type-label">font-arabic · IBM Plex Sans Arabic 700</div>
            <div style="font-family: 'IBM Plex Sans Arabic', Tahoma, sans-serif; font-size: 16px; font-weight: 700; direction: rtl;">منصة إدارة الممارسة القانونية الذكية للشام</div>
        </div>
    </div>

    {{-- Shadows --}}
    <div class="section">
        <h2 class="section-title">Shadows</h2>
        <div style="display: flex; gap: 24px; flex-wrap: wrap;">
            @foreach (['xs', 'sm', 'md', 'lg', 'xl'] as $size)
                <div style="width: 120px; height: 80px; background: #FFF; border-radius: 8px; box-shadow: var(--shadow-{{ $size }}); display: flex; align-items: center; justify-content: center; font-size: 13px; color: var(--text-tertiary);">
                    shadow-{{ $size }}
                </div>
            @endforeach
        </div>
    </div>

    {{-- Logo Assets --}}
    <div class="section">
        <h2 class="section-title">Logo Assets</h2>
        <div style="display: flex; gap: 24px; flex-wrap: wrap; align-items: center;">
            <div style="padding: 16px; background: #FAFAF9; border-radius: 8px; border: 1px solid #E7E5E4;">
                <img src="{{ asset('img/brand/efirm-horizontal-compact.svg') }}" alt="Logo" style="height: 40px;">
                <div style="font-size: 11px; color: var(--text-tertiary); margin-top: 8px;">efirm-horizontal-compact.svg</div>
            </div>
            <div style="padding: 16px; background: #330000; border-radius: 8px;">
                <img src="{{ asset('img/brand/efirm-horizontal-compact-reversed.svg') }}" alt="Logo reversed" style="height: 40px;">
                <div style="font-size: 11px; color: #D6D3D1; margin-top: 8px;">efirm-horizontal-compact-reversed.svg</div>
            </div>
            <div style="padding: 16px; background: #FAFAF9; border-radius: 8px; border: 1px solid #E7E5E4;">
                <img src="{{ asset('img/brand/efirm-mark.svg') }}" alt="Mark" style="height: 40px;">
                <div style="font-size: 11px; color: var(--text-tertiary); margin-top: 8px;">efirm-mark.svg</div>
            </div>
            <div style="padding: 16px; background: #330000; border-radius: 8px;">
                <img src="{{ asset('img/brand/efirm-mark-reversed.svg') }}" alt="Mark reversed" style="height: 40px;">
                <div style="font-size: 11px; color: #D6D3D1; margin-top: 8px;">efirm-mark-reversed.svg</div>
            </div>
            <div style="padding: 16px; background: #FAFAF9; border-radius: 8px; border: 1px solid #E7E5E4;">
                <img src="{{ asset('img/brand/efirm-favicon.svg') }}" alt="Favicon" style="height: 32px;">
                <div style="font-size: 11px; color: var(--text-tertiary); margin-top: 8px;">efirm-favicon.svg</div>
            </div>
        </div>
    </div>
</body>
</html>
