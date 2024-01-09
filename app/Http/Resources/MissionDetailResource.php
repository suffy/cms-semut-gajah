<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\MissionTaskResource;

class MissionDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */    
    // public function with($request){
    //     return [
    //         'success' => true,
    //         'message' => 'Get mission detail successfully',
    //     ];
    // }

    public function toArray($request)
    {
        $remain_time    = (strtotime($this->end_date) - strtotime($this->start_date)) / (60 * 60 * 24);
        $tasks          = $this->tasks;

        $finish_count = 0;
        foreach($tasks as $task) {
            $user_id    = auth()->user()->id;
            if($task->user->contains($user_id)) {
                $finish_count += 1;
            }
        }

        $task_count     = count($tasks);

        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'description'   => $this->description,
            'reward'        => $this->reward,
            'start_date'    => $this->start_date,
            'end_date'    => $this->end_date,
            'remain_time'   => $remain_time,
            'finish_task'   => $finish_count,
            'total_task'    => $task_count,
            'tasks'         => MissionTaskResource::collection($this->whenLoaded('tasks'))
        ];
    }
}
