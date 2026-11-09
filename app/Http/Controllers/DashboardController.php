<?php

namespace App\Http\Controllers;

use App\Services\DashboardStats;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardStats $stats): Response
    {
        return Inertia::render('Dashboard', [
            'stats' => $stats->for($request->user()),
        ]);
    }
}
