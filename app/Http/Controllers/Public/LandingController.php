<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(Request $request): View
    {
        $pricingTiers = config('marketing.pricing_tiers', []);
        $foundingBadgeEnabled = config('marketing.founding_firm_badge_enabled', true);

        return view('public.landing', [
            'pricingTiers' => $pricingTiers,
            'foundingBadgeEnabled' => $foundingBadgeEnabled,
        ]);
    }

    public function demoRequest(): View
    {
        return view('public.demo-request.create');
    }

    public function thankYou(Request $request): View
    {
        return view('public.demo-request.thank-you');
    }
}
