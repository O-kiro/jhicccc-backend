<?php

namespace App\Http\Resources\V1;

use App\Models\AlumniAccount;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AlumniAccount
 */
class AlumniResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'graduation_year' => $this->graduation_year,
            'occupation' => $this->occupation,
            'angkatan' => $this->labelAngkatan(),
        ];
    }
}
