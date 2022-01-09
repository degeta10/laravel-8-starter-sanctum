<?php

namespace App\Http\Resources\Auth;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'message'           => 'Success',
            'token_type'        =>  'Bearer',
            'access_token'      =>  $this['access_token'],
            'user'              =>  new UserDetailsResponse(auth()->user())
        ];
    }
}
