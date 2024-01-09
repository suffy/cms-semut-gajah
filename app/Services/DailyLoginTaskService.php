<?php 

namespace App\Services;

use Illuminate\Support\Carbon;
use App\Services\DailyLoginTaskService;
use App\Log;

class DailyLoginTaskService {

    public function check($missions, $customer_id)
    {
        foreach($missions as $mission) {
            
            foreach($mission->tasks as $key => $task) {
                // get data task previous
                $task_prev = ($key > 0) ? $mission->tasks[$key - 1] : null;
                // check already accomplish this task before
                $exists = $task->user->contains($customer_id);
                if(!$exists) {
                    // if first loop
                    if($task->login_at == Carbon::now()->format('Y-m-d')) {
                        // check if first loop
                        if(!$task_prev) {
                            $task->user()->attach($customer_id, ['finish_at' => Carbon::now()]);

                            Log::create([
                                        'log_time'      => Carbon::now(),
                                        'activity'      => 'User ' . $customer_id . ' already finish the mission with id ' . $mission->id . ' and task id ' . $task->id,
                                        'table_id'      => $mission->id,
                                        'data_content'  => null,
                                        'table_name'    => 'missions, mission_tasks, mission_task_users',
                                        'column_name'   => 'mission_task_users.mission_id, mission_task_users.user_id, mission_task_users.finish_at',
                                        'from_user'     => null,
                                        'to_user'       => $customer_id,
                                        'platform'      => 'apps',
                                    ]);
                        } else {
                            // check if prev task is accomplish 
                            if($task_prev->user->contains($customer_id)) {
                                $task->user()->attach($customer_id, ['finish_at' => Carbon::now()]);

                                Log::create([
                                    'log_time'      => Carbon::now(),
                                    'activity'      => 'User ' . $customer_id . ' already finish the mission with id ' . $mission->id . ' and task id ' . $task->id,
                                    'table_id'      => $mission->id,
                                    'data_content'  => null,
                                    'table_name'    => 'missions, mission_tasks, mission_task_users',
                                    'column_name'   => 'mission_task_users.mission_id, mission_task_users.user_id, mission_task_users.finish_at',
                                    'from_user'     => null,
                                    'to_user'       => $customer_id,
                                    'platform'      => 'apps',
                                ]);
                            } else {
                                return 'Task Sebelumnya tidak selesai';
                            }
                        }
                    } else {
                        return 'login_at and current date not match';
                    }
                } else {
                    return 'task sudah selesai';
                }
            };
        }
    }

}