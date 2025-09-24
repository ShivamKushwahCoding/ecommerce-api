<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminDashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'total_users'    => $this->total_users,
            'active_users'   => $this->active_users,
            'inactive_users' => $this->inactive_users,
            'roles'          => $this->roles,
            'permissions'    => $this->permissions,
            'files_uploaded' => $this->files_uploaded,
            'mappings'       => $this->mappings,
        ];
    }
}
