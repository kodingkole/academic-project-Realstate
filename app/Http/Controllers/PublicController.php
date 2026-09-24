<?php

namespace App\Http\Controllers;

use App\Models\LandSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Services\ProjectProgressService;

class PublicController extends Controller
{
    /**
     * Display the public homepage.
     */
    public function landing(Request $request, ProjectProgressService $progressService): View
    {
        $search = trim((string) $request->query('search', ''));
        $location = trim((string) $request->query('location', ''));
        $budget = (string) $request->query('budget', '');
        $projects = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('projects')) {
            $dbProjects = \App\Models\Project::query()->with('tasks')
                ->whereRaw('LOWER(status) = ?', ['active'])
                ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
                ->when($location !== '', fn ($query) => $query->where('location', 'like', "%{$location}%"))
                ->when($budget === 'under-5cr' || $budget === 'under-60' || $budget === 'under-50l', fn ($query) => $query->where('total_budget', '<', 50000000))
                ->when($budget === '5cr-10cr' || $budget === '60-90' || $budget === '50l-1cr', fn ($query) => $query->whereBetween('total_budget', [50000000, 100000000]))
                ->when($budget === 'over-10cr' || $budget === 'over-90' || $budget === 'over-1cr', fn ($query) => $query->where('total_budget', '>', 100000000))
                ->latest()
                ->take(12)
                ->get();
            if ($dbProjects->count() > 0) {
                $projects = $dbProjects->map(function ($p) use ($progressService) {
                    $unitCost = $p->total_budget > 0 ? (int) ($p->total_budget / 40) : ($p->estimated_cost ?? 6000000);
                    return [
                        'id' => $p->id,
                        'name' => $p->title,
                        'location' => $p->location,
                        'type' => 'Residential & Commercial',
                        'progress' => $progressService->percentage($p),
                        'price' => 'Starting from BDT ' . number_format($unitCost),
                    ];
                })->toArray();
            }
        }

        $activeProjects = \Illuminate\Support\Facades\Schema::hasTable('projects')
            ? \App\Models\Project::query()->whereRaw('LOWER(status) = ?', ['active'])->count()
            : 0;
        $investors = \Illuminate\Support\Facades\Schema::hasTable('users')
            ? \App\Models\User::query()->where('role', 'investor')->count()
            : 0;

        return view('landing', compact('projects', 'search', 'location', 'budget', 'activeProjects', 'investors'));
    }

    /**
     * Dedicated page showing all active projects from database.
     */
    public function allProjects(Request $request, ProjectProgressService $progressService): View
    {
        $search = trim((string) $request->query('search', ''));

        $dbProjects = \App\Models\Project::query()->with('tasks')
            ->whereRaw('LOWER(status) = ?', ['active'])
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->latest()
            ->paginate(5)
            ->withQueryString();

        return view('public.projects', compact('dbProjects', 'search'));
    }

    /**
     * Dedicated page showing detailed services, investor/landowner benefits & platform trust.
     */
    public function services(): View
    {
        return view('public.services');
    }

    /**
     * Dedicated page showing detailed 4-step process timeline.
     */
    public function howItWorks(): View
    {
        return view('public.how-it-works');
    }

    public function submitLand(): View
    {
        return view('public.submit-land', ['districtsByDivision' => $this->districtsByDivision()]);
    }

    public function submitLandPost(Request $request): RedirectResponse
    {
        $districtsByDivision = $this->districtsByDivision();
        $division = (string) $request->input('division', '');
        $data = $request->validate([
            'phone'=>['required','regex:/^01[3-9]\d{8}$/'], 'division'=>['required', Rule::in(array_keys($districtsByDivision))], 'district'=>['required', Rule::in($districtsByDivision[$division] ?? [])],
            'location'=>'required|string|max:500', 'katha'=>'required|numeric|min:2|max:999999', 'road_width'=>'required|integer|min:20|max:500',
            'landmark'=>'nullable|string|max:255', 'description'=>'nullable|string|max:3000', 'owner_name'=>'required|string|max:255',
            'nid_number'=>['required', 'regex:/^(?:\d{10}|\d{13}|\d{17})$/'], 'deed_path'=>'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'nid_path'=>'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'phone.required' => 'Enter your mobile number.',
            'phone.regex' => 'Use an 11-digit Bangladeshi number starting from 013 to 019.',
            'division.required' => 'Select the division where the land is located.',
            'district.required' => 'Enter the official district name.',
            'location.required' => 'Enter the full land location and address.',
            'katha.min' => 'Land size must be at least 2 Katha.',
            'road_width.min' => 'Road width must be at least 20 feet.',
            'owner_name.required' => 'Enter the owner name exactly as it appears on the NID and deed.',
            'nid_number.regex' => 'Enter a valid NID containing exactly 10, 13, or 17 digits.',
            'deed_path.required' => 'Upload the title deed or ownership document.',
            'deed_path.mimes' => 'Title deed must be a PDF, JPG or PNG file.',
            'deed_path.max' => 'Title deed file must not exceed 5 MB.',
            'nid_path.required' => 'Upload the owner NID copy.',
            'nid_path.mimes' => 'NID copy must be a PDF, JPG or PNG file.',
            'nid_path.max' => 'NID copy must not exceed 5 MB.',
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

    private function districtsByDivision(): array
    {
        return [
            'Dhaka' => ['Dhaka', 'Faridpur', 'Gazipur', 'Gopalganj', 'Kishoreganj', 'Madaripur', 'Manikganj', 'Munshiganj', 'Narayanganj', 'Narsingdi', 'Rajbari', 'Shariatpur', 'Tangail'],
            'Chittagong' => ['Bandarban', 'Brahmanbaria', 'Chandpur', 'Chattogram', 'Cumilla', "Cox's Bazar", 'Feni', 'Khagrachhari', 'Lakshmipur', 'Noakhali', 'Rangamati'],
            'Rajshahi' => ['Bogura', 'Joypurhat', 'Naogaon', 'Natore', 'Chapai Nawabganj', 'Pabna', 'Rajshahi', 'Sirajganj'],
            'Sylhet' => ['Habiganj', 'Moulvibazar', 'Sunamganj', 'Sylhet'],
            'Khulna' => ['Bagerhat', 'Chuadanga', 'Jashore', 'Jhenaidah', 'Khulna', 'Kushtia', 'Magura', 'Meherpur', 'Narail', 'Satkhira'],
            'Barisal' => ['Barguna', 'Barishal', 'Bhola', 'Jhalokati', 'Patuakhali', 'Pirojpur'],
            'Rangpur' => ['Dinajpur', 'Gaibandha', 'Kurigram', 'Lalmonirhat', 'Nilphamari', 'Panchagarh', 'Rangpur', 'Thakurgaon'],
            'Mymensingh' => ['Jamalpur', 'Mymensingh', 'Netrokona', 'Sherpur'],
        ];
    }

    private function trackingCode(): string
    {
        do { $code = 'LND-'.now()->format('Y').'-'.strtoupper(Str::random(5)); } while (LandSubmission::where('code',$code)->exists());
        return $code;
    }
}
