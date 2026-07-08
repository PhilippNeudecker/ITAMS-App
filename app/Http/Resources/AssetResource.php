<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'asset_label'          => $this->asset_label,
            'name'                 => $this->name,
            'description'          => $this->description,

            'category'             => $this->whenLoaded('category', fn() => [
                'id'     => $this->category->id,
                'name'   => $this->category->name,
                'color'  => $this->category->color,
            ]),

            'status'               => $this->whenLoaded('status', fn() => [
                'id'   => $this->status->id,
                'name' => $this->status->name,
            ]),

            'manufacturer'         => $this->whenLoaded('manufacturer', fn() => [
                'id'   => $this->manufacturer->id,
                'name' => $this->manufacturer->name,
            ]),

            'location'             => $this->whenLoaded('location', fn() => [
                'id'           => $this->location->id,
                'name'         => $this->location->name,
                'full_address' => $this->location->full_address,
            ]),

            'tags'                 => $this->whenLoaded('tags',
                fn() => $this->tags->map(fn($t) => [
                    'id' => $t->id, 'name' => $t->name, 'color' => $t->color,
                ])
            ),

            'property_values'      => $this->whenLoaded('propertyValues',
                fn() => $this->propertyValues->map(fn($pv) => [
                    'id'                     => $pv->id,
                    'property_definition_id' => $pv->property_definition_id,
                    'name'                   => $pv->definition?->name,
                    'data_type'              => $pv->definition?->data_type,
                    'unit'                   => $pv->definition?->unit,
                    'typed_value'            => $pv->typed_value,
                ])
            ),

            'active_assignment'    => $this->whenLoaded('activeAssignment', fn() =>
                $this->activeAssignment ? [
                    'id'                    => $this->activeAssignment->id,
                    'assignment_type'       => $this->activeAssignment->assignment_type,
                    'assigned_from'         => $this->activeAssignment->assigned_from,
                    'employee_display_name' => $this->activeAssignment->employee_display_name_snapshot,
                    'cost_center_name'      => $this->activeAssignment->cost_center_name_snapshot,
                ] : null
            ),

            // Supplier Snapshot
            'supplier' => [
                'number'  => $this->supplier_number,
                'name'    => $this->supplier_name,
                'address' => $this->supplier_address,
                'city'    => $this->supplier_city,
                'country' => $this->supplier_country,
            ],

            // Financials
            'purchase_value'       => $this->purchase_value,
            'purchase_date'        => $this->purchase_date?->toDateString(),

            // Warranty
            'warranty_start_date'  => $this->warranty_start_date?->toDateString(),
            'warranty_end_date'    => $this->warranty_end_date?->toDateString(),
            'warranty_active'      => $this->isWarrantyActive(),
            'warranty_expires_in_days' => $this->warrantyExpiresInDays(),

            'created_at'           => $this->created_at,
            'updated_at'           => $this->updated_at,
        ];
    }
}
