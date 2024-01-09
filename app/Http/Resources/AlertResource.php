<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AlertResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // check if user get alert or not
        $status     = $this->user->contains($this->user_id) ? false : true;
        // store to alert_status if user already get the alert
        if($status) {
            $this->user()->attach($this->user_id);
        }
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'description'   => $this->description,
            'type'          => $this->type,
            'parameter'     => $this->parameter,
            'is_show'       => $status
        ];
    }
}
