<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssetRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'category_id'            => 'required|uuid|exists:categories,id',
            'status_definition_id'   => 'required|uuid|exists:status_definitions,id',
            'name'                   => 'required|string|max:255',
            'description'            => 'nullable|string',
            'manufacturer_id'        => 'nullable|uuid|exists:manufacturers,id',
            'current_location_id'    => 'nullable|uuid|exists:locations,id',
            'tag_ids'                => 'nullable|array',
            'tag_ids.*'              => 'uuid|exists:tags,id',

            // Supplier
            'supplier_number'        => 'nullable|string|max:50',
            'supplier_name'          => 'nullable|string|max:255',
            'supplier_address'       => 'nullable|string',
            'supplier_post_code'     => 'nullable|string|max:10',
            'supplier_city'          => 'nullable|string|max:100',
            'supplier_country'       => 'nullable|string|max:100',

            // Financials
            'purchase_value'         => 'nullable|numeric|min:0',
            'purchase_date'          => 'nullable|date',

            // Warranty
            'warranty_start_date'    => 'nullable|date',
            'warranty_end_date'      => 'nullable|date|after_or_equal:warranty_start_date',
            'warranty_notify_days_before' => 'nullable|integer|min:0',

            // Dynamic Properties
            'properties'                             => 'nullable|array',
            'properties.*.property_definition_id'   => 'required|uuid|exists:property_definitions,id',
            'properties.*.value_text'               => 'nullable|string',
            'properties.*.value_number'             => 'nullable|numeric',
            'properties.*.value_date'               => 'nullable|date',
            'properties.*.value_bool'               => 'nullable|boolean',
            'properties.*.property_option_id'       => 'nullable|uuid|exists:property_options,id',
        ];
    }
}
