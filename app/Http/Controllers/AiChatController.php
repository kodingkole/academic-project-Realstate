<?php

namespace App\Http\Controllers;

use App\Models\LandSubmission;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AiChatController extends Controller
{
    /**
     * Process incoming AI Assistant chat query and return contextual responses.
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userMessage = trim($validated['message']);
        $lowerMsg = mb_strtolower($userMessage);

        $reply = '';
        $suggestions = [];
        $actionLink = null;

        // Fetch current active projects and land submissions for context
        $projects = Project::where('status', 'active')->get();
        if ($projects->isEmpty()) {
            $projects = Project::all();
        }

        $landSubmissions = LandSubmission::latest()->take(3)->get();

        // Intent 1: Sqft Size Query (e.g., "1200 sqft", "1500 square feet")
        if (Str::contains($lowerMsg, ['sqft', 'sq ft', 'square feet', 'size', '1200', '1400', '1500', '1800', '2000'])) {
            $sqft = $this->extractNumber($lowerMsg) ?? 1450;
            if ($sqft < 100) { $sqft = $sqft * 100; } // Handle shortcut numbers like 14 => 1400

            $reply = "**AI Property Size & Valuation Analysis:**\n" .
                     "For a flat/unit size of ~" . number_format($sqft) . " Sq Ft, here is our smart estimate and project matching:\n\n";

            foreach ($projects->take(3) as $p) {
                $unitCost = $p->total_budget > 0 ? (int) ($p->total_budget / 20) : ($p->estimated_cost ?? 6000000);
                $perSqftRate = (int) ($unitCost / max(1, $sqft));
                $reply .= "• **" . $p->title . "** (" . $p->location . ")\n" .
                          "  - Estimated Size: **" . number_format($sqft) . " Sq Ft**\n" .
                          "  - Total Valuation: BDT **" . number_format($unitCost) . "** (~BDT " . number_format($perSqftRate) . "/Sq Ft)\n" .
                          "  - Key Features: Modern Elevator, 24/7 Generator, Solar Backup, Substation\n\n";
            }

            $reply .= "Would you like to review installment plans or reserve a share?";
            $suggestions = ['How does EMI work?', 'Show projects under 50 lakh', 'Slot & Investment Benefits'];
            if ($projects->isNotEmpty()) {
                $actionLink = [
                    'text' => 'Reserve Share / Checkout',
                    'url' => route('checkout.show', ['type' => 'project', 'id' => $projects->first()->id]),
                ];
            }

        // Intent 2: Plot / Project Recommendation & Location Choice
        } elseif (Str::contains($lowerMsg, ['plot', 'recommend', 'best', 'location', 'bashundhara', 'uttara', 'dhanmondi', 'gazipur', 'dhaka', 'where'])) {
            $reply = "**AI Recommended Best Locations & Projects:**\n" .
                     "Based on capital growth potential, location accessibility, and legal title safety, here are our top picks:\n\n";

            $rank = 1;
            foreach ($projects->take(3) as $p) {
                $unitCost = $p->total_budget > 0 ? (int) ($p->total_budget / 20) : ($p->estimated_cost ?? 6000000);
                $reply .= "Rank #" . $rank . ": **" . $p->title . "**\n" .
                          "• Location: **" . $p->location . "**\n" .
                          "• Valuation: BDT " . number_format($unitCost) . "\n" .
                          "• Investment Highlight: High rental demand & 10-14% projected annual appreciation.\n\n";
                $rank++;
            }

            $reply .= "All plots and developments undergo RAJUK / City Corporation approval check before construction.";
            $suggestions = ['1500 sqft flat cost', 'Slot Benefits & Perks', 'How does EMI work?'];
            if ($projects->isNotEmpty()) {
                $actionLink = [
                    'text' => 'View Top Project Details',
                    'url' => route('checkout.show', ['type' => 'project', 'id' => $projects->first()->id]),
                ];
            }

        // Intent 3: Slot / Share Investment Benefits & Cost Breakdown
        } elseif (Str::contains($lowerMsg, ['benefit', 'perk', 'slot', 'why', 'return', 'roi', 'yield', 'advantage', 'profit'])) {
            $reply = "**Slot & Share Investment Benefits Breakdown:**\n\n" .
                     "1. **Joint Equity Ownership Deed**: Registered legal share in the project land & structure.\n" .
                     "2. **High ROI & Rental Yield**: Earn estimated **8-12% annual rental return** upon completion.\n" .
                     "3. **Zero Developer Markup Cost**: Save up to 25-30% compared to traditional commercial developer pricing.\n" .
                     "4. **Flexible Credit Card EMI**: Pay over 12, 24, or 36 months with partner bank Credit Cards.\n" .
                     "5. **Audited & Verified Title**: Vetted by assigned legal panel lawyers.\n\n" .
                     "Would you like to calculate estimated installment costs?";

            $suggestions = ['Show projects under 50 lakh', 'How does EMI work?', 'Submit Land for JV'];

        // Intent 4: Projects / Investment Search / Budget Query
        } elseif (Str::contains($lowerMsg, ['project', 'investment', 'invest', 'budget', 'price', 'cost', 'property', ' buy', ' flat', ' share', 'lakh', 'crore'])) {
            $budget = $this->extractNumber($lowerMsg);

            if ($budget && $budget > 0) {
                if ($budget < 1000) {
                    $budgetTaka = $budget * 100000;
                } else {
                    $budgetTaka = $budget;
                }

                $matchingProjects = $projects->filter(function ($p) use ($budgetTaka) {
                    $unitCost = $p->total_budget > 0 ? (int) ($p->total_budget / 20) : ($p->estimated_cost ?? 6000000);
                    return $unitCost <= ($budgetTaka * 1.3);
                });

                if ($matchingProjects->isNotEmpty()) {
                    $reply = "**AI Investment Matcher:**\nBased on your budget (~BDT " . number_format($budget < 1000 ? $budget . ' Lakh' : $budget) . "), here are top recommended verified projects:\n\n";
                    foreach ($matchingProjects->take(3) as $p) {
                        $unitCost = $p->total_budget > 0 ? (int) ($p->total_budget / 20) : ($p->estimated_cost ?? 6000000);
                        $reply .= "• **" . $p->title . "** (" . $p->location . ")\n  - Share Valuation: **BDT " . number_format($unitCost) . "**\n  - Status: " . ucfirst($p->status) . "\n\n";
                    }
                    $reply .= "You can reserve shares or pay via EMI directly from our gateway!";
                    $firstProj = $matchingProjects->first();
                    $actionLink = [
                        'text' => 'Checkout Verified Share',
                        'url' => route('checkout.show', ['type' => 'project', 'id' => $firstProj->id]),
                    ];
                } else {
                    $reply = "**AI Property Recommendation:**\nWe currently have prime real estate developments in Dhaka starting from BDT 42-60 Lakh per share. Would you like to inspect our active portfolio?";
                }
            } else {
                $reply = "**Intern Estate Active Projects Portfolio:**\nWe have " . $projects->count() . " active construction & real estate developments:\n\n";
                foreach ($projects->take(4) as $p) {
                    $unitCost = $p->total_budget > 0 ? (int) ($p->total_budget / 20) : ($p->estimated_cost ?? 6000000);
                    $reply .= "• **" . $p->title . "**\n  Location: " . $p->location . "\n  Share Valuation: BDT " . number_format($unitCost) . "\n\n";
                }
                $reply .= "Select any project to review documentation or proceed with installment booking.";
                if ($projects->isNotEmpty()) {
                    $actionLink = [
                        'text' => 'View Checkout & Payment Gateway',
                        'url' => route('checkout.show', ['type' => 'project', 'id' => $projects->first()->id]),
                    ];
                }
            }

            $suggestions = ['How does EMI work?', 'Slot & Investment Benefits', 'Submit Land for JV'];

        // Intent 5: EMI / Installments / Payment Plans
        } elseif (Str::contains($lowerMsg, ['emi', 'installment', 'payment', 'bkash', 'nagad', 'bank', 'card', 'credit card', 'pay', 'monthly'])) {
            $reply = "**AI Payment & Installment Guidance:**\nIntern Estate offers flexible payment solutions:\n\n" .
                     "1. **Full One-Time Payment**: Instant booking confirmation via bKash, Nagad, SSLCommerz, or Bank Transfer.\n" .
                     "2. **1-3 Years EMI Installments**: Backed by Credit Card guarantee (City Bank Amex, BRAC Bank, EBL, SCB, DBBL).\n" .
                     "3. **Verification**: All payments undergo automatic 256-bit encryption & Admin audit review before final deed issue.\n\n" .
                     "Would you like to initiate booking for a project share?";

            $suggestions = ['Show active projects', 'NID & Tax Document rules', 'Submit Land for JV'];
            if ($projects->isNotEmpty()) {
                $actionLink = [
                    'text' => 'Go to Payment Gateway',
                    'url' => route('checkout.show', ['type' => 'project', 'id' => $projects->first()->id]),
                ];
            }

        // Intent 6: Land Submission / Landowner Joint Venture
        } elseif (Str::contains($lowerMsg, ['land', 'landowner', 'jv', 'joint venture', 'submit land', 'sell land', 'katha', 'bigha'])) {
            $reply = "**Landowner Joint-Venture (JV) Portal:**\nDo you own prime land in Bangladesh? Partner with Intern Estate for modern building development!\n\n" .
                     "• **Process**: Submit your land location, Katha size, and asking ratio.\n" .
                     "• **Legal Verification**: Our panel of vetted lawyers conducts title deed verification.\n" .
                     "• **Profit Sharing**: Earn guaranteed 50-50 share ratio or lucrative JV terms.\n\n" .
                     "Click below to submit your land details to our legal & engineering team.";

            $suggestions = ['Submit Land Form', 'Active Projects', 'How does EMI work?'];
            $actionLink = [
                'text' => 'Submit Land Proposal',
                'url' => route('land.submit'),
            ];

        // Intent 7: KYC / Verification / NID / Legal Documents
        } elseif (Str::contains($lowerMsg, ['kyc', 'nid', 'document', 'tax', 'tin', 'bill', 'lawyer', 'legal', 'verify'])) {
            $reply = "**Legal & KYC Verification Compliance:**\nFor secure real estate transactions, Intern Estate strictly enforces regulatory compliance:\n\n" .
                     "1. **National ID (NID)**: Smart NID or 10-17 digit NID number.\n" .
                     "2. **Tax Clearance Certificate**: Valid E-TIN registration number.\n" .
                     "3. **Utility Bill**: Recent Electricity or Gas bill as proof of address.\n\n" .
                     "All uploaded documents are audited by assigned legal advisors before final contract creation.";

            $suggestions = ['Active Projects', 'How does EMI work?', 'Contact Support'];

        // Intent 8: Default Greeting & General Support
        } else {
            $reply = "Hello! I am your **Intern Estate Smart AI Assistant**.\n\nI can help you with:\n" .
                     "• Finding projects matching your exact budget (e.g. 50 lakh)\n" .
                     "• Estimating flat cost by Sq Ft size (e.g. 1500 sqft)\n" .
                     "• Recommending prime plot locations & slot benefits\n" .
                     "• Calculating 1-3 year Credit Card EMI plans\n" .
                     "• Submitting land for Joint-Venture (JV) development\n\n" .
                     "What would you like to explore today?";

            $suggestions = ['Top Recommended Projects', '1500 sqft flat cost', 'Slot & Investment Benefits', 'How does EMI work?'];
        }

        return response()->json([
            'status' => 'success',
            'reply' => $reply,
            'suggestions' => $suggestions,
            'action_link' => $actionLink,
        ]);
    }

    /**
     * Helper to extract numeric values from user prompt (e.g., "50 lakh", "1500 sqft", "6000000")
     */
    private function extractNumber(string $text): ?int
    {
        if (preg_match('/(\d+(?:\.\d+)?)\s*(?:lakh|lac|l|lac|k|crore|cr|sqft|sq ft|sq)?/i', $text, $matches)) {
            $val = (float) $matches[1];
            if (Str::contains(mb_strtolower($text), ['crore', 'cr'])) {
                return (int) ($val * 100);
            }
            return (int) $val;
        }
        return null;
    }
}
