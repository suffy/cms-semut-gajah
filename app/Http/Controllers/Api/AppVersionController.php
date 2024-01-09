<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use App\Services\DailyLoginTaskService;
use App\Http\Resources\AlertResource;
use App\AppVersion;
use App\Mission;
use App\Alert;
use App\User;

class AppVersionController extends Controller
{
    protected $appVersions;

    public function __construct(AppVersion $appVersions)
    {
        $this->appVersions             = $appVersions;
    }

    public function index(Request $request)
    {
        try {
            $app_version = $request->version;

            $cek_version = $this->appVersions
                ->select(['version', 'require_update', 'optional_update', 'maintenance'])
                // ->orderBy('version', 'DESC')
                ->where('version', $app_version)
                ->first();
            // ->get();

            // $cek_version = $versions->where('version', $app_version)->first();

            if ($cek_version != NULL) {
                return response()->json([
                    'success' => true,
                    'message' => 'Get app version success',
                    'data'    => $cek_version
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Get app version success',
                    'data'    => 'version not registered yet!'
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get app version failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function restart(Request $request, DailyLoginTaskService $loginTask)
    {
        try {
            $version = $request->version;
            $alert      = null;

            if ($request->customer_code) {
                $user = User::where('customer_code', $request->customer_code)->first();
                $user->update([
                    'last_login' => Carbon::now()
                ]);

                $missions = Mission::where('status', 1)->whereHas('tasks', function ($query) {
                    $query->where('type', 3);
                })
                    ->with('tasks.user')
                    ->get();

                if ($missions) {
                    $response   = $loginTask->check($missions, $user->id);
                }

                /* info = tidak ada, promo = promo_id, top-spender = top_spender_id, link = link */
                $now            = Carbon::now()->format('Y-m-d');
                $alert          = Alert::where('start', '<=', $now)
                    ->where('end', '>=', $now)
                    ->with('user')->first();

                // add user_id to collection
                if ($alert) {
                    $alert->user_id = $user->id;
                }
            }

            $lastVersion = $this->appVersions
                ->select(['version'])
                ->orderBy('id', 'DESC')
                ->first();

            if ($version != $lastVersion->version) {
                return response()->json([
                    'restart' => true,
                    'alert'     => isset($alert) ? new AlertResource($alert) : null
                ], 200);
            } else {
                return response()->json([
                    'restart' => false,
                    'alert'     => isset($alert) ? new AlertResource($alert) : null
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Check restart app version failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }
}
