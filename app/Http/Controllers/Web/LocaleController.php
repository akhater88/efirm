<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\SwitchLocaleRequest;
use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    public function switch(SwitchLocaleRequest $request): RedirectResponse
    {
        $locale = $request->validated('locale');

        if ($request->user()) {
            $request->user()->update(['preferred_locale' => $locale]);
        }

        session(['locale' => $locale]);

        return redirect()->back();
    }
}
