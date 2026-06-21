<form method="POST" action="{{ route('locale.switch') }}" class="flex items-center">
    @csrf
    @if (app()->getLocale() === 'ar')
        <input type="hidden" name="locale" value="en">
        <button type="submit"
                class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400
                       dark:hover:text-gray-200 px-2 py-1">
            English
        </button>
    @else
        <input type="hidden" name="locale" value="ar">
        <button type="submit"
                class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400
                       dark:hover:text-gray-200 px-2 py-1">
            العربية
        </button>
    @endif
</form>
