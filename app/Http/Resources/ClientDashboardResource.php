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
            'cancellations_total'           => $this->cancellations_total,
            'sales_total_count'             => $this->sales_total_count,
            'cancellations_count'           => $this->cancellations_count,
            'sales_total_order_count'       => $this->sales_total_order_count,
            'courier_return_total_count' => $this->courier_return_total_count,
            'courier_return_total' => $this->courier_return_total,
            'courier_return_total_qty' => $this->courier_return_total_qty,
            'customer_return_total_count' => $this->customer_return_total_count,
            'customer_return_total' => $this->customer_return_total,
            'customer_return_total_qty' => $this->customer_return_total_qty,
            'unknown_return_total_count' => $this->unknown_return_total_count,
            'unknown_return_total' => $this->unknown_return_total,
            'unknown_return_total_qty' => $this->unknown_return_total_qty,
            'cancellations_order_count'     => $this->cancellations_order_count,
            'net_sales_amount'                    => $this->sales_total - $this->cancellations_total - $this->courier_return_total - $this->customer_return_total - $this->unknown_return_total,
            'net_sales_count'                    => $this->sales_total_count - $this->cancellations_count - $this->courier_return_total_count - $this->customer_return_total_count - $this->unknown_return_total_count,
            'net_sales_qty'                    => $this->sales_total_count - $this->cancellations_count - $this->courier_return_total_qty - $this->customer_return_total_qty - $this->unknown_return_total_qty,
        ];
    }
}