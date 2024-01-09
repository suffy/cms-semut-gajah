<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AppVersion;
use App\Log;
use Carbon\Carbon;

class AppVersionController extends Controller
{
    protected $appVersions, $logs;

    public function __construct(AppVersion $appVersion, Log $log)
    {
        $this->appVersions = $appVersion;
        $this->logs        = $log;
    }

    public function index()
    {
        $appVersions = $this->appVersions->paginate(10);
        return view('admin.pages.app-version', compact('appVersions'));
    }

    public function requireUpdate($id)
    {
        $appVersion = $this->appVersions::find($id);

        if ($appVersion) {
            if ($appVersion->require_update == "true") {
                $appVersion->require_update = "false";
            } else {
                $appVersion->require_update = "true";
            }
            $appVersion->save();

            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "change app version " . $appVersion->version . " require update to " . $appVersion->require_update;
            $logs->table_id     = $appVersion->id;
            $logs->data_content = "app version : " . $appVersion;
            $logs->table_name   = 'app_versions';
            $logs->column_name  = 'version, require_update';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = 'web';

            $logs->save();            

            $status = 1;
            $msg = 'Update sukses ' . $appVersion->require_update;
            return response()->json(compact('status', 'msg'), 200);
        } else {
            $status = 0;
            $msg = 'Update Gagal';
            return response()->json(compact('status', 'msg'), 200);
        }
    }

    public function optionalUpdate($id)
    {
        $appVersion = $this->appVersions::find($id);

        if ($appVersion) {
            if ($appVersion->optional_update == "true") {
                $appVersion->optional_update = "false";
            } else {
                $appVersion->optional_update = "true";
            }
            $appVersion->save();

            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "change app version " . $appVersion->version . " optional update to " . $appVersion->require_update;
            $logs->table_id     = $appVersion->id;
            $logs->data_content = "app version : " . $appVersion;
            $logs->table_name   = 'app_versions';
            $logs->column_name  = 'version, optional_update';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = 'web';

            $logs->save();         

            $status = 1;
            $msg = 'Update sukses ' . $appVersion->optional_update;
            return response()->json(compact('status', 'msg'), 200);
        } else {
            $status = 0;
            $msg = 'Update Gagal';
            return response()->json(compact('status', 'msg'), 200);
        }
    }

    public function maintenance($id)
    {
        $appVersion = $this->appVersions::find($id);

        if ($appVersion) {
            if ($appVersion->maintenance == "true") {
                $appVersion->maintenance = "false";
            } else {
                $appVersion->maintenance = "true";
            }
            $appVersion->save();

            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "change app version " . $appVersion->version . " maintenance to " . $appVersion->maintenance;
            $logs->table_id     = $appVersion->id;
            $logs->data_content = "app version : " . $appVersion;
            $logs->table_name   = 'app_versions';
            $logs->column_name  = 'version, maintenance';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = 'web';

            $logs->save();         

            $status = 1;
            $msg = 'Update sukses ' . $appVersion->maintenance;
            return response()->json(compact('status', 'msg'), 200);
        } else {
            $status = 0;
            $msg = 'Update Gagal';
            return response()->json(compact('status', 'msg'), 200);
        }
    }

    public function store(Request $request)
    {
        try{
            // create category
            $appVersion = AppVersion::create([
                'version' => $request->version,
                'description' => $request->description,
                'require_update' => 'false',
                'optional_update' => 'false',
                'deleted_at' => null,
            ]);

            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "Create new app version";
            $logs->data_content = "app version : " . $request->version;
            $logs->table_name   = 'app_versions';
            $logs->table_id     = $appVersion->id;
            $logs->column_name  = 'version, require_update, optional_update';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = 'web';

            $logs->save();

            return redirect(url('superadmin/app-version'))
                                            ->with('status', 1)
                                            ->with('message', "Data Tersimpan!");
        } catch(\Exception $e) {
            return redirect()->back()
                                    ->with('status', 2)
                                    ->with('message', $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        try{
            // create category
            $appVersion                 = $this->appVersions->find($request->edit_app_version_id);
            $appVersion->version        = $request->edit_version;
            $appVersion->description    = $request->edit_description;
            $appVersion->save();

            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "Update app version";
            $logs->data_content = "app version : " . $appVersion;
            $logs->table_id     = $request->edit_app_version_id;
            $logs->table_name   = 'app_versions';
            $logs->column_name  = 'version, description';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = 'web';

            $logs->save();

            return redirect(url('superadmin/app-version'))
                                            ->with('status', 1)
                                            ->with('message', "Data Terupdate!");
        } catch(\Exception $e) {
            return redirect()->back()
                                    ->with('status', 2)
                                    ->with('message', $e->getMessage());
        }
    }

    public function delete(Request $request)
    {
        try{
            // create category
            $appVersion = $this->appVersions->find($request->id);
            $appVersion->delete();

            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "Delete app version";
            $logs->data_content = "app version : " . $request->version;
            $logs->table_id     = $request->id;
            $logs->table_name   = 'app_versions';
            $logs->column_name  = 'version';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = 'web';

            $logs->save();

            return redirect(url('superadmin/app-version'))
                                            ->with('status', 1)
                                            ->with('message', "Data Terhapus!");
        } catch(\Exception $e) {
            return redirect()->back()
                                    ->with('status', 2)
                                    ->with('message', $e->getMessage());
        }
    }
}
