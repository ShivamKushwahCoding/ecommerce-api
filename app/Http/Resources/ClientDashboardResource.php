<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientDashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'sales_total'                   => $this->sales_total,
            'returns_total'                 => $this->returns_total,
            'returns_cancelled_total'       => $this->returns_cancelled_total,
            'cancellations_total'           => $this->cancellations_total,
            'sales_total_count'             => $this->sales_total_count,
            'returns_total_count'           => $this->returns_total_count,
            'returns_cancelled_count'       => $this->returns_cancelled_count,
            'cancellations_count'           => $this->cancellations_count,
            'sales_total_order_count'       => $this->sales_total_order_count,
            'returns_total_order_count'     => $this->returns_total_order_count,
            'returns_cancelled_order_count' => $this->returns_cancelled_order_count,
            'cancellations_order_count'     => $this->cancellations_order_count,
        ];
    }
}
