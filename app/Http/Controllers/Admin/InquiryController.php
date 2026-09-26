<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InquiryStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inquiries\IndexInquiryRequest;
use App\Http\Requests\Admin\Inquiries\StoreInquiryNoteRequest;
use App\Http\Requests\Admin\Inquiries\UpdateInquiryStatusRequest;
use App\Models\AuditLog;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function index(IndexInquiryRequest $request): View
    {
        $filters = $request->validated();
        $inquiries = Inquiry::query()
            ->with('assignedUser:id,name')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('reference', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($filters['type'] ?? null, fn ($query, string $type) => $query->where('type', $type))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.inquiries.index', compact('inquiries', 'filters'));
    }

    public function show(Inquiry $inquiry): View
    {
        Gate::authorize('view', $inquiry);
        $inquiry->load(['assignedUser:id,name', 'packageDetails.tourPackage:id,title,slug', 'rentalDetails.vehicle:id,title,slug', 'rentalDetails.category:id,name', 'propertyDetails.property:id,name,slug', 'visaDetails', 'baggageDetails', 'notes.author:id,name', 'statusHistories.changedBy:id,name']);

        return view('admin.inquiries.show', compact('inquiry'));
    }

    public function updateStatus(UpdateInquiryStatusRequest $request, Inquiry $inquiry): RedirectResponse
    {
        $data = $request->validated();
        $newStatus = InquiryStatus::from($data['status']);
        $oldStatus = $inquiry->status;

        if ($oldStatus === $newStatus) {
            return back()->with('success', 'Inquiry status is unchanged.');
        }

        DB::transaction(function () use ($request, $inquiry, $oldStatus, $newStatus, $data): void {
            $history = $inquiry->statusHistories()->make();
            $history->forceFill([
                'changed_by' => $request->user()->getKey(),
                'from_status' => $oldStatus->value,
                'to_status' => $newStatus->value,
                'reason' => $data['reason'] ?? null,
            ])->save();
            $inquiry->forceFill([
                'status' => $newStatus,
                'closed_at' => $newStatus === InquiryStatus::Completed ? now() : null,
            ])->save();
            $this->audit('inquiry.status_updated', $inquiry, ['from' => $oldStatus->value, 'to' => $newStatus->value]);
        });

        return back()->with('success', 'Inquiry status updated.');
    }

    public function storeNote(StoreInquiryNoteRequest $request, Inquiry $inquiry): RedirectResponse
    {
        $note = $inquiry->notes()->make(['body' => $request->validated('body')]);
        $note->forceFill(['author_id' => $request->user()->getKey()])->save();
        $this->audit('inquiry.note_added', $inquiry);

        return back()->with('success', 'Internal note added.');
    }

    /** @param array<string, mixed>|null $changes */
    private function audit(string $action, Inquiry $inquiry, ?array $changes = null): void
    {
        (new AuditLog)->forceFill([
            'actor_id' => auth()->id(), 'action' => $action, 'subject_type' => Inquiry::class,
            'subject_id' => $inquiry->id, 'subject_label' => $inquiry->reference,
            'changes' => $changes, 'request_id' => (string) Str::uuid(),
        ])->save();
    }
}
