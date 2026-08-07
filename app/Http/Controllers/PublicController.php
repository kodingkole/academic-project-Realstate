<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PublicController extends Controller
{
    /**
     * Display the public homepage.
     */
    public function landing(): View
    {
        $projects = [
            [
                'name' => 'Skyline Residence',
                'location' => 'Uttara, Dhaka',
                'type' => 'Residential',
                'progress' => 72,
                'price' => 'Starting from ৳85 Lakh',
            ],
            [
                'name' => 'Green Valley Heights',
                'location' => 'Bashundhara, Dhaka',
                'type' => 'Luxury Apartment',
                'progress' => 48,
                'price' => 'Starting from ৳1.2 Crore',
            ],
            [
                'name' => 'Urban Trade Center',
                'location' => 'Gazipur',
                'type' => 'Commercial',
                'progress' => 86,
                'price' => 'Starting from ৳45 Lakh',
            ],
        ];

        return view('landing', compact('projects'));
    }
}