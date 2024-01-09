<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class MissionListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    
    public function toArray($request)
    {
        $user_id            = auth()->user()->id;
        $mission_status     = $this->user->contains($user_id) ? true : false;
        $data               = $this->user->where('id', $user_id)->first();
        if($data) {
            $mission_finish     = $data->pivot->mission_status;
            if($mission_finish == '1') {
                $mission_finish = true;
            } else {
                $mission_finish = false;
            }
        } else {
            $mission_finish     = false;
        }
        // return parent::toArray($request);
        $remain_time  = (strtotime($this->end_date) - strtotime($this->start_date)) / (60 * 60 * 24);
        $check_date = Carbon::now()->format('Y-m-d');
        $mission_start = null;
        if($check_date >= $this->start_date) {
            $mission_start = true;
        } else {
            $mission_start = false;
        }
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'description'       => $this->description,
            'reward'            => $this->reward,
            'start_date'        => $this->start_date,
            'end_date'          => $this->end_date,
            'remain_time'       => $remain_time,
            'is_start'          => $mission_status,
            'is_finish'         => $mission_finish,
            'available_start'   => $mission_start
        ];
    }
}
