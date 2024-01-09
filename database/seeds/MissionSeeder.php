<?php

use Illuminate\Database\Seeder;
use App\Mission;
use App\MissionTask;

class MissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $mission = Mission::create([
            'name'          => 'Misi Pertama',
            'description'   => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Arcu vitae elementum curabitur vitae nunc sed.',
            'reward'        => '5000',
            'start_date'    => '2022-08-30',
            'end_date'      => '2022-09-30',
            'status'        => 1,
        ]);

        $missionTasks = [
            [
                'mission_id'    => $mission->id,
                'type'          => 1,
                'qty'           => 2,
                'product_id'    => '10001'
            ],
            [
                'mission_id'    => $mission->id,
                'type'          => 2,
                'qty'           => 20000,
                'product_id'    => '10001'
            ]
        ];

        MissionTask::insert($missionTasks);

        $mission2 = Mission::create([
            'name'          => 'Misi Kedua',
            'description'   => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Arcu vitae elementum curabitur vitae nunc sed.',
            'reward'        => '8000',
            'start_date'    => '2022-09-01',
            'end_date'      => '2022-09-30',
            'status'        => 1,
        ]);

        $missionTasks2 = [
            [
                'mission_id'    => $mission2->id,
                'type'          => 1,
                'qty'           => 2,
                'product_id'    => '10101'
            ],
            [
                'mission_id'    => $mission2->id,
                'type'          => 2,
                'qty'           => 20000,
                'product_id'    => '10101'
            ]
        ];

        MissionTask::insert($missionTasks2);

        // $mission = Mission::create([
        //     'name'          => 'Misi Login',
        //     'description'   => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Arcu vitae elementum curabitur vitae nunc sed.',
        //     'reward'        => '500',
        //     'start_date'    => '2022-09-06',
        //     'end_date'      => '2022-09-08',
        //     'status'        => 1,
        // ]);

        // $missionTasks = [
        //     [
        //         'mission_id'    => $mission->id,
        //         'type'          => 3,
        //         'login_at'      => '2022-09-07'
        //     ],
        //     [
        //         'mission_id'    => $mission->id,
        //         'type'          => 3,
        //         'login_at'      => '2022-09-08'
        //     ]
        // ];

        // MissionTask::insert($missionTasks);
    }
}
