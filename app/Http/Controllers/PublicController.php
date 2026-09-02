<?php

namespace App\Http\Controllers;

use App\Models\LandSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicController extends Controller
{
    /**
     * Display the public homepage.
     */
    public function landing(): View
    {
        $projects = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('projects')) {
            $dbProjects = \App\Models\Project::take(3)->get();
            if ($dbProjects->count() > 0) {
                $projects = $dbProjects->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'name' => $p->title,
                        'location' => $p->location,
                        'type' => 'Residential & Commercial',
                        'progress' => $p->progress_percentage,
                        'price' => 'Starting from ৳' . number_format($p->total_budget / 10) . ' BDT',
                    ];
                })->toArray();
            }
        }

        if (empty($projects)) {
            $projects = [
                [
                    'id' => 1,
                    'name' => 'Skyline Residence',
                    'location' => 'Uttara, Dhaka',
                    'type' => 'Residential',
                    'progress' => 72,
                    'price' => 'Starting from ৳85 Lakh',
                ],
                [
                    'id' => 1,
                    'name' => 'Green Valley Heights',
                    'location' => 'Bashundhara, Dhaka',
                    'type' => 'Luxury Apartment',
                    'progress' => 48,
                    'price' => 'Starting from ৳1.2 Crore',
                ],
                [
                    'id' => 1,
                    'name' => 'Urban Trade Center',
                    'location' => 'Gazipur',
                    'type' => 'Commercial',
                    'progress' => 86,
                    'price' => 'Starting from ৳45 Lakh',
                ],
            ];
        }

        return view('landing', compact('projects'));
    }

    public function submitLand(): View
    {
        return view('public.submit-land', ['divisions'=>['Dhaka','Chittagong','Rajshahi','Sylhet','Khulna','Barisal','Rangpur','Mymensingh']]);
    }

    public function submitLandPost(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone'=>['required','regex:/^01[3-9]\d{8}$/'], 'division'=>'required|string|max:80', 'district'=>'required|string|max:100',
            'location'=>'required|string|max:500', 'katha'=>'required|numeric|min:0.01|max:999999', 'road_width'=>'required|integer|min:1|max:500',
            'landmark'=>'nullable|string|max:255', 'description'=>'nullable|string|max:3000', 'owner_name'=>'required|string|max:255',
            'nid_number'=>'required|string|min:10|max:20', 'deed_path'=>'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'nid_path'=>'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);
        $code = $this->trackingCode();
        $deedPath = $request->file('deed_path')->store('land-submissions/'.$code.'/deeds');
        $nidPath = $request->file('nid_path')->store('land-submissions/'.$code.'/nid');
        LandSubmission::create([
            'code'=>$code, 'user_id'=>$request->user()?->id, 'landowner_id'=>$request->user()?->id,
            'landowner_name'=>$data['owner_name'], 'phone'=>$data['phone'], 'division'=>$data['division'], 'district'=>$data['district'],
            'location'=>$data['location'], 'title'=>$data['district'].' JV Land', 'katha_size'=>$data['katha'], 'area_sqft'=>$data['katha']*720,
            'road_width'=>$data['road_width'], 'landmark'=>$data['landmark']??null, 'description'=>$data['description']??null,
            'nid_number'=>$data['nid_number'], 'deed_path'=>$deedPath, 'nid_path'=>$nidPath, 'asking_price'=>0,
            'status'=>'submitted', 'stage'=>'Submitted', 'submitted_at'=>now(),
        ]);
        return redirect()->route('land.submit')->with('submission_success', $code);
    }

    private function trackingCode(): string
    {
        do { $code = 'LND-'.now()->format('Y').'-'.strtoupper(Str::random(5)); } while (LandSubmission::where('code',$code)->exists());
        return $code;
    }
}
