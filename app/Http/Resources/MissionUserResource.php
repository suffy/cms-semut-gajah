<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MissionUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */    
    public function with($request){
        return [
            'success' => true,
            'message' => 'Get mission user successfully',
        ];
    }

    public function toArray($request)
    {
        $user_id    = auth()->user()->id;
        // $mission_status     = $this->user->contains($user_id) ? true : false;

        return [
            'id'            => $this->id,
            'user_id'            => $user_id,
            'mission_id'    => $this->mission_id,
            'mission_status'   => $this->mission_status,
        ];
    }
}
