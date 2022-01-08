<?php

namespace App\Http\Resources\Auth;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class RefreshTokenResource extends JsonResource
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
            'token_type'        =>  "Bearer",
            'access_token'      =>  $this['access_token'],
            'refresh_token'     =>  $this['refresh_token'],
            'expires_at'        =>  Carbon::now()->addSeconds($this['expires_in'])->toDateTimeString()
        ];
    }
}
