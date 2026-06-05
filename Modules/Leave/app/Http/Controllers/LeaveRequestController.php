<?php

namespace Modules\Leave\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Employee\Models\Employee;
use Modules\Leave\Actions\Backend\ApproveLeaveAction;
use Modules\Leave\Actions\Backend\CancelLeaveRequestAction;
use Modules\Leave\Actions\Backend\RejectLeaveAction;
use Modules\Leave\Actions\Backend\StoreLeaveRequestAction;
use Modules\Leave\Data\Requests\StoreLeaveRequestData;
use Modules\Leave\Enums\LeaveRequestStatus;
use Modules\Leave\Enums\LeaveType;
use Modules\Leave\Models\LeaveBalance;
use Modules\Leave\Models\LeaveRequest;
use Modules\Leave\Queries\ListLeaveRequestsHandler;
use Modules\Leave\Queries\ListLeaveRequestsQuery;
use Modules\Leave\Queries\ListPendingApprovalHandler;
use Modules\Leave\Queries\ListPendingApprovalQuery;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $query = new ListLeaveRequestsQuery(
            employee_id: $request->integer('employee_id') ?: null,
            status:      $request->input('status'),
            date_from:   $request->input('date_from'),
            date_to:     $request->input('date_to'),
            leave_type:  $request->input('leave_type'),
        );

        $requests = (new ListLeaveRequestsHandler)->handle($query)->paginate(20)->withQueryString();

        $leaveTypes = collect(LeaveType::cases())
            ->map(fn ($t) => ['value' => $t->value, 'text' => $t->label()])
            ->all();

        $statuses = collect(LeaveRequestStatus::cases())
            ->map(fn ($s) => ['value' => $s->value, 'text' => $s->label()])
            ->all();

        return view('leave::requests.index', compact('requests', 'leaveTypes', 'statuses'));
    }

    public function create()
    {
        $this->authorize('create', LeaveRequest::class);

        $orgId = TenantContext::getOrganizationId();

        $employees = Employee::withoutTenant()
            ->where('organization_id', $orgId)
            ->working()
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'employee_code']);

        $leaveTypes = collect(LeaveType::cases())
            ->map(fn ($t) => ['value' => $t->value, 'text' => $t->label()])
            ->all();

        return view('leave::requests.create', compact('employees', 'leaveTypes'));
    }

    public function store(Request $request, StoreLeaveRequestAction $action): RedirectResponse
    {
        $this->authorize('create', LeaveRequest::class);

        try {
            $data = StoreLeaveRequestData::validateAndCreate($request->all());
            $leaveRequest = $action->handle($data);

            return redirect()->route('backend.leave.requests.show', $leaveRequest)
                ->with('success', 'Đơn nghỉ phép đã được gửi thành công.');
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['balance' => $e->getMessage()]);
        }
    }

    public function show(LeaveRequest $request)
    {
        $this->authorize('view', $request);
        $request->load(['employee.department', 'balance.policy', 'approvedBy', 'createdBy']);

        return view('leave::requests.show', ['leaveRequest' => $request]);
    }

    public function pending(Request $request)
    {
        $this->authorize('approve', new LeaveRequest);

        $user = auth()->user();
        $orgId = TenantContext::getOrganizationId();

        // Find the employee record for the current user to use as manager_id
        $managerEmployee = Employee::withoutTenant()
            ->where('organization_id', $orgId)
            ->where('user_id', $user->id)
            ->first();

        if (!$managerEmployee) {
            $requests = collect();
        } else {
            $query = new ListPendingApprovalQuery($managerEmployee->id);
            $requests = (new ListPendingApprovalHandler)->handle($query)->get();
        }

        return view('leave::requests.pending', compact('requests'));
    }

    public function approve(Request $request, LeaveRequest $leaveRequest, ApproveLeaveAction $action): RedirectResponse
    {
        $this->authorize('approve', $leaveRequest);

        $user = auth()->user();
        $orgId = TenantContext::getOrganizationId();

        $approver = Employee::withoutTenant()
            ->where('organization_id', $orgId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        try {
            $action->handle($leaveRequest, $approver);
            return back()->with('success', 'Đơn nghỉ phép đã được duyệt.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['action' => $e->getMessage()]);
        }
    }

    public function reject(Request $request, LeaveRequest $leaveRequest, RejectLeaveAction $action): RedirectResponse
    {
        $this->authorize('reject', $leaveRequest);

        $request->validate(['rejected_reason' => 'required|string|max:500']);

        try {
            $action->handle($leaveRequest, $request->input('rejected_reason'));
            return back()->with('success', 'Đã từ chối đơn nghỉ phép.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['action' => $e->getMessage()]);
        }
    }

    public function cancel(LeaveRequest $leaveRequest, CancelLeaveRequestAction $action): RedirectResponse
    {
        $this->authorize('cancel', $leaveRequest);

        try {
            $action->handle($leaveRequest);
            return back()->with('success', 'Đã hủy đơn nghỉ phép.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['action' => $e->getMessage()]);
        }
    }
}
