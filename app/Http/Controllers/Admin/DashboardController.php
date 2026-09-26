<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\TourPackage;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $canManageInquiries = Gate::allows('viewAny', Inquiry::class);
        $metrics = [
            'packages' => TourPackage::query()->count(),
            'publishedPackages' => TourPackage::query()->published()->count(),
            'vehicles' => Vehicle::query()->count(),
            'newInquiries' => $canManageInquiries ? Inquiry::query()->withStatus(InquiryStatus::New)->count() : 0,
            'visaRequests' => $canManageInquiries ? Inquiry::query()->ofType(InquiryType::Visa)->count() : 0,
            'baggageRequests' => $canManageInquiries ? Inquiry::query()->ofType(InquiryType::Baggage)->count() : 0,
        ];

        $recentInquiries = $canManageInquiries ? Inquiry::query()
            ->select(['id', 'reference', 'type', 'status', 'name', 'email', 'subject', 'assigned_to', 'created_at'])
            ->with('assignedUser:id,name')
            ->latest()
            ->limit(8)
            ->get() : collect();

        return view('admin.dashboard', compact('metrics', 'recentInquiries', 'canManageInquiries'));
    }
}
