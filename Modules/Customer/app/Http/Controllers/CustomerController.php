<?php
namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Customer\Actions\CreateCustomerAction;
use Modules\Customer\Actions\DeleteCustomerAction;
use Modules\Customer\Actions\UpdateCustomerAction;
use Modules\Customer\Data\Requests\StoreCustomerData;
use Modules\Customer\Data\Requests\UpdateCustomerData;
use Modules\Customer\Enums\CompanySize;
use Modules\Customer\Enums\CustomerLifecycleStage;
use Modules\Customer\Enums\CustomerType;
use Modules\Customer\Models\Customer;
use Modules\Customer\Models\CustomerFieldDefinition;
use Modules\Customer\Models\CustomerTag;
use Modules\Customer\Queries\GetCustomerHandler;
use Modules\Customer\Queries\GetCustomerQuery;
use App\Models\Province;
use Modules\LeadSource\Models\LeadSource;

class CustomerController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Customer::class);

        $orgId   = TenantContext::getOrganizationId();
        $sources = LeadSource::where('is_active', true)->orderBy('sort_order')->get(['id', 'label']);
        $tags    = CustomerTag::where('organization_id', $orgId)->orderBy('name')->get(['id', 'name', 'color']);
        $stages  = collect(CustomerLifecycleStage::cases())->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()]);
        $types   = collect(CustomerType::cases())->map(fn ($t) => ['value' => $t->value, 'label' => $t->label()]);

        return view('customer::index', compact('sources', 'tags', 'stages', 'types'));
    }

    public function create(): View
    {
        $this->authorize('create', Customer::class);

        $orgId     = TenantContext::getOrganizationId();
        $sources   = LeadSource::where('is_active', true)->orderBy('sort_order')->get(['id', 'label']);
        $tags      = CustomerTag::where('organization_id', $orgId)->orderBy('name')->get(['id', 'name', 'color']);
        $sizes     = collect(CompanySize::cases())->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()]);
        $stages    = collect(CustomerLifecycleStage::cases())->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()]);
        $provinces = Province::where('is_active', true)->orderBy('name')->get(['province_code', 'name']);
        $fieldDefs = CustomerFieldDefinition::where('organization_id', $orgId)
            ->active()->orderBy('sort_order')->get();
        $customer  = null;

        return view('customer::_form', compact('customer', 'sources', 'tags', 'sizes', 'stages', 'provinces', 'fieldDefs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Customer::class);

        $data     = StoreCustomerData::validateAndCreate($request->all());
        $customer = CreateCustomerAction::run($data, TenantContext::getOrganizationId());

        return redirect()->route('customer.show', $customer)
            ->with('success', 'Đã tạo khách hàng thành công.');
    }

    public function show(Customer $customer): View
    {
        $this->authorize('view', $customer);

        $customer  = app(GetCustomerHandler::class)->handle(new GetCustomerQuery($customer));
        $fieldDefs = CustomerFieldDefinition::where('organization_id', $customer->organization_id)
            ->active()->orderBy('sort_order')->get();

        return view('customer::show', compact('customer', 'fieldDefs'));
    }

    public function edit(Customer $customer): View
    {
        $this->authorize('update', $customer);

        $orgId     = TenantContext::getOrganizationId();
        $customer->load(['tags', 'meta.definition']);
        $sources   = LeadSource::where('is_active', true)->orderBy('sort_order')->get(['id', 'label']);
        $tags      = CustomerTag::where('organization_id', $orgId)->orderBy('name')->get(['id', 'name', 'color']);
        $sizes     = collect(CompanySize::cases())->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()]);
        $stages    = collect(CustomerLifecycleStage::cases())->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()]);
        $provinces = Province::where('is_active', true)->orderBy('name')->get(['province_code', 'name']);
        $fieldDefs = CustomerFieldDefinition::where('organization_id', $orgId)
            ->active()->orderBy('sort_order')->get();

        return view('customer::_form', compact('customer', 'sources', 'tags', 'sizes', 'stages', 'provinces', 'fieldDefs'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorize('update', $customer);

        $data = UpdateCustomerData::validateAndCreate($request->all());
        UpdateCustomerAction::run($customer, $data);

        return redirect()->route('customer.show', $customer)
            ->with('success', 'Đã cập nhật khách hàng.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorize('delete', $customer);

        DeleteCustomerAction::run($customer);

        return redirect()->route('customer.index')
            ->with('success', 'Đã xóa khách hàng.');
    }
}
