@if (app()->getLocale() === 'ar')
    <a href="{{ url('/locale/en') }}"
       style="font-size: 13px; color: #6B7280; text-decoration: none; padding: 4px 8px; border-radius: 4px;"
       onmouseover="this.style.color='#111827'"
       onmouseout="this.style.color='#6B7280'">
        English
    </a>
@else
    <a href="{{ url('/locale/ar') }}"
       style="font-size: 13px; color: #6B7280; text-decoration: none; padding: 4px 8px; border-radius: 4px;"
       onmouseover="this.style.color='#111827'"
       onmouseout="this.style.color='#6B7280'">
        العربية
    </a>
@endif
