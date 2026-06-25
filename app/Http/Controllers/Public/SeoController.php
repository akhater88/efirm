<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $baseUrl = config('app.url', 'https://efirm.io');
        $lastmod = now()->toW3cString();

        $routes = [
            ['loc' => '/', 'priority' => '1.0'],
            ['loc' => '/demo-request', 'priority' => '0.6'],
            ['loc' => '/terms', 'priority' => '0.3'],
            ['loc' => '/privacy', 'priority' => '0.3'],
            ['loc' => '/dpa', 'priority' => '0.3'],
            ['loc' => '/ai-disclaimer', 'priority' => '0.3'],
            ['loc' => '/ar', 'priority' => '1.0'],
            ['loc' => '/ar/demo-request', 'priority' => '0.6'],
            ['loc' => '/ar/terms', 'priority' => '0.3'],
            ['loc' => '/ar/privacy', 'priority' => '0.3'],
            ['loc' => '/ar/dpa', 'priority' => '0.3'],
            ['loc' => '/ar/ai-disclaimer', 'priority' => '0.3'],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($routes as $route) {
            $xml .= '  <url>'."\n";
            $xml .= '    <loc>'.$baseUrl.$route['loc'].'</loc>'."\n";
            $xml .= '    <lastmod>'.$lastmod.'</lastmod>'."\n";
            $xml .= '    <changefreq>weekly</changefreq>'."\n";
            $xml .= '    <priority>'.$route['priority'].'</priority>'."\n";
            $xml .= '  </url>'."\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    public function robots(): Response
    {
        $baseUrl = config('app.url', 'https://efirm.io');

        $content = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "Disallow: /app/\n";
        $content .= "Disallow: /admin/\n";
        $content .= "\n";
        $content .= "Sitemap: {$baseUrl}/sitemap.xml\n";

        return response($content, 200, [
            'Content-Type' => 'text/plain',
        ]);
    }
}
