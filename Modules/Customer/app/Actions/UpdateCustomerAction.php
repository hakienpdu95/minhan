<?php
namespace Modules\Customer\Actions;

use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Customer\Data\Requests\UpdateCustomerData;
use Modules\Customer\Events\CustomerUpdated;
use Modules\Customer\Models\Customer;

class UpdateCustomerAction
{
    use AsAction;

    public function handle(Customer $customer, UpdateCustomerData $data): Customer
    {
        $customer->update([
            'customer_type'        => $data->customer_type,
            'display_name'         => $data->display_name,
            'primary_email'        => $data->primary_email,
            'primary_phone'        => $data->primary_phone,
            'lifecycle_stage'      => $data->lifecycle_stage ?? $customer->lifecycle_stage->value,
            'source_id'            => $data->source_id,
            'assigned_to'          => $data->assigned_to,
            'first_name'           => $data->first_name,
            'last_name'            => $data->last_name,
            'gender'               => $data->gender,
            'date_of_birth'        => $data->date_of_birth,
            'company_name'         => $data->company_name,
            'tax_code'             => $data->tax_code,
            'industry'             => $data->industry,
            'company_size'         => $data->company_size,
            'representative_name'  => $data->representative_name,
            'representative_title' => $data->representative_title,
            'province_code'        => $data->province_code,
            'full_address'         => $data->full_address,
            'website'              => $data->website,
            'description'          => $data->description,
            'updated_by'           => Auth::id(),
        ]);

        SyncCustomerTagsAction::run($customer, $data->tag_ids ?? []);

        if ($data->meta !== null) {
            SyncCustomerMetaAction::run($customer, $data->meta);
        }

        event(new CustomerUpdated($customer));

        return $customer;
    }
}
