<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class InvestorDashboardController extends Controller
{
    public function index(): View
    {
        return view('investor.dashboard');
    }
}
