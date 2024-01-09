<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MissionTaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $user_id    = auth()->user()->id;
        $status     = $this->user->contains($user_id) ? true : false;

        return [
            'id'            => $this->id,
            'mission_id'    => $this->mission_id,
            'name'          => $this->name,
            'type'          => $this->type,
            'qty'           => $this->qty,
            'product_id'    => $this->product_id,
            'is_finish'     => $status
        ];
    }
}
