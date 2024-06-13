<?php

namespace App\Http\Resources;

use App\Enumeration\CustomerTypeText;
use App\Enumeration\CustomerUrlTypeText;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'name' => $this->first_name .' '. $this->last_name,
            'username' => $this->username,
            'email' => $this->email,
            'pro_pic' => $this->customer ? Storage::url($this->customer->pro_pic) : '',
            'dob' => $this->birth_date,
            'address' => new AddressResource($this->whenLoaded('address')),
            'status' => $this->status,
            'typeVal' => $this->customer ? $this->customer->type : null,
            'type' => $this->customer ? CustomerTypeText::$TYPE[$this->customer->type] : null,
            'url' => $this->customer ? CustomerUrlTypeText::$TYPE[$this->customer->type] . '/'. $this->username : null,
            'last_login' => $this->last_login ? $this->last_login->format('Y-m-d h:i:s A') : '',
            'created_at' => $this->created_at->format('Y-m-d h:i:s A'),
            'permissions'=> $this->permissions ? $this->permissions : null,
        ];
    }
}
