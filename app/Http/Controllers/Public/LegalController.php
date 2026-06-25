<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LegalController extends Controller
{
    public function show(string $slug): View
    {
        $locale = app()->getLocale();
        $markdownPath = resource_path("markdown/legal/{$slug}-{$locale}.md");

        if (! File::exists($markdownPath)) {
            // Fallback to English
            $markdownPath = resource_path("markdown/legal/{$slug}-en.md");
        }

        $content = File::exists($markdownPath)
            ? Str::markdown(File::get($markdownPath))
            : '';

        $titles = [
            'terms' => __('marketing.legal.terms_title'),
            'privacy' => __('marketing.legal.privacy_title'),
            'dpa' => __('marketing.legal.dpa_title'),
            'ai-disclaimer' => __('marketing.legal.ai_disclaimer_title'),
        ];

        return view('public.legal.show', [
            'title' => $titles[$slug] ?? Str::title($slug),
            'content' => $content,
            'slug' => $slug,
        ]);
    }
}
