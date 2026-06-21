<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $workspace = $user->currentWorkspace();

        return view('dashboard', [
            'workspace' => $workspace,
            'user' => $user,
        ]);
    }
}
