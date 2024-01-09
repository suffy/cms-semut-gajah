<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Mission;
use App\MissionTask;
use App\MissionUser;
use App\Product;
use App\User;
use App\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class MissionController extends Controller
{
    protected $mission, $missionTask, $product, $log, $user, $missionUser;
    public function __construct(Mission $mission, MissionTask $missionTask, Product $product, Log $log, User $user, MissionUser $missionUser)
    {
        $this->mission      = $mission;
        $this->missionTask  = $missionTask;
        $this->product      = $product;
        $this->logs         = $log;
        $this->user         = $user;
        $this->missionUser  = $missionUser;
    }

    public function index()
    {
        $mission     = $this->mission->orderBy('id')->get();
        $user        = $this->user->whereNotNull('fcm_token')->count();
        $partisipasi = $this->missionUser->count();
        $complete    = $this->logs->where('activity', 'user finish mission')->count();
        $partisipasi = $partisipasi - $complete;
        $user        = $user - $partisipasi - $complete;
        return view('admin.pages.mission', compact('mission', 'user', 'partisipasi', 'complete'));
    }

    public function create()
    {
        // $mission = $this->mission->get();
        // $product = $this->product->wherenotnull('name')->get();
        // // $group = $this->product
        // //     ->wherenotnull('nama_group')
        // //     ->select('nama_group')
        // //     ->groupBy('nama_group')
        // //     ->get();
        // // $brand = $this->product->get()
        // //     ->where('brand_id', '!=', 'BSP')
        // //     ->unique('brand_id')
        // //     ->sortBy('brand_id')
        // //     ->pluck('brand_id', 'brand')
        // //     ->except('BSP', NULL);
        // return view('admin.pages.mission-create', compact('mission', 'product', 'group', 'brand'));
        return view('admin.pages.mission-create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'       => 'required',
            'description' => 'required',
            'start_date' => 'required',
            'end_date'   => 'required',
            'reward'     => 'required',
            'limit'      => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withInput()
                ->with('status', 2)
                ->with('errors', $validator->errors())
                ->with('message', $validator->errors()->first());
        }


        $mission                = $this->mission;
        $mission->name          = $request->name;
        $mission->description   = $request->description;
        $mission->start_date    = $request->start_date;
        $mission->end_date      = $request->end_date;
        $mission->reward        = $request->reward;
        // $mission->limit         = $request->limit;
        $mission->status        = 1;
        $save                   = $mission->save();

        $missionTask = $this->missionTask;
        foreach ($request->id as $index => $key) {
            $data = array(
                'mission_id'    => $mission->id,
                'product_id'    => isset($request->product_id[$index]) ? $request->product_id[$index] : null,
                'group_id'      => isset($request->nama_group[$index]) ? $request->nama_group[$index] : null,
                'qty'           => isset($request->qty[$index]) ? $request->qty[$index] : null,
                'type'          => isset($request->type[$index]) ? $request->type[$index] : null,
                'name'         => isset($request->names[$index]) ? $request->names[$index] : null,
                'login_at'      => isset($request->login_at[$index]) ? $request->login_at[$index] : null,
            );

            $missionTask->create($data);
        }

        return redirect(url('/manager/missions'));
    }

    public function delete(Request $request)
    {
        try {
            $mission = $this->mission->find($request->id);
            $mission->delete();
            $mission->mission_id = $this->missionTask->where('mission_id', $mission->id)->delete();


            return response()->json([
                'status'    => true,
                'message'   => 'data berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'    => false,
                'message'   => $e->getMessage()
            ]);
        }
    }

    public function edit($id)
    {
        $mission = $this->mission->with('tasks.product')->find($id);
        $product = $this->product->wherenotnull('name')->get();
        $group = $this->product
            ->wherenotnull('nama_group')
            ->select('nama_group')
            ->groupBy('nama_group')
            ->get();
        return view('admin/pages/mission-update', compact('mission', 'group', 'product'));
    }
    public function update(Request $request)
    {
        // dd($request);
        try {
            $this->validate($request, [
                'name'          => 'required',
                'description'   => 'required',
                'start_date'    => 'required',
                'end_date'      => 'required',
                'reward'        => 'required',
            ]);
            $mission                 = $this->mission->find($request->id);
            $mission->update([
                'id'            => $request->id,
                'name'          => $request->name,
                'description'   => $request->description,
                'start_date'    => $request->start_date,
                'end_date'      => $request->end_date,
                'reward'        => $request->reward,
            ]);
            // $save=$mission->save();

            $this->missionTask->where('mission_id', $mission->id)->delete();

            $missionTask = $this->missionTask;

            $mission = $this->mission->find($request->id);

            foreach ($request->qty as $index => $key) {
                $data = array(
                    'mission_id'    => $mission->id,
                    'product_id'    => isset($request->product_id[$index]) ? $request->product_id[$index] : null,
                    'group_id'      => isset($request->nama_group[$index]) ? $request->nama_group[$index] : null,
                    'qty'           => isset($request->qty[$index]) ? $request->qty[$index] : null,
                    'type'          => isset($request->type[$index]) ? $request->type[$index] : null,
                    'name'          => isset($request->names[$index]) ? $request->names[$index] : null,
                    'login_at'      => isset($request->login_at[$index]) ? $request->login_at[$index] : null,
                );
                $missionTask->create($data);
            }

            // logs




            return redirect(url('/manager/missions'))
                ->with('status', 1)
                ->with('message', "Misi berhasil diubah");
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('status', 2)
                ->with('message', $e->getMessage());
            //     ->with('message', $e->getMessage());
        }
    }

    public function deletetask(Request $request)
    {
        try {
            $missiontask = $this->missionTask->find($request->id);
            $missiontask->delete();

            return response()->json([
                'status'    => true,
                'message'   => 'data berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'    => false,
                'message'   => $e->getMessage()
            ]);
        }
    }
    public function updateStatus($id)
    {
        $mission = $this->mission::find($id);

        if ($mission) {
            if ($mission->status == "1") {
                $mission->status = "0";
                $this->missionTask->where('mission_id', $id)->update(['mission_id' => NULL]);

                $logs = $this->logs;
                $logs->log_time     = Carbon::now();
                $logs->activity     = "change status misi to nonaktif";
                $logs->table_id     = $id;
                $logs->table_name   = 'missions';
                $logs->from_user    = auth()->user()->id;
                $logs->to_user      = null;
                $logs->platform     = "web";
                $logs->save();
            } else {
                $mission->status = "1";

                $logs = $this->logs;
                $logs->log_time     = Carbon::now();
                $logs->activity     = "change status misi to aktif";
                $logs->table_id     = $id;
                $logs->table_name   = 'missions';
                $logs->from_user    = auth()->user()->id;
                $logs->to_user      = null;
                $logs->platform     = "web";
                $logs->save();
            }
            $mission->save();

            $status = 1;
            $msg = 'Update sukses ' . $mission->status;
            return response()->json(compact('status', 'msg'), 200);
        } else {
            $status = 0;
            $msg = 'Update Gagal';
            return response()->json(compact('status', 'msg'), 200);
        }
    }

    public function statistik(Request $request)
    {
        $partisipasi = $this->missionUser->where('mission_id', $request->id)->count();
        $complete    = $this->logs->where('activity', 'user finish mission')->where('to_user', $request->id)->count();
        $partisipasi = $partisipasi - $complete;

        return response()->json(compact('partisipasi', 'complete'), 200);
    }
}
