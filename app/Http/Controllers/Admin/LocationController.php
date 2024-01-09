<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Location;
use App\Log;

class LocationController extends Controller
{

    protected $location;
    protected $logs;
    public function __construct(Location $location, Log $log)
    {
        $this->location = $location;
        $this->logs = $log;
    }
    public function index(Request $request)
    {
        $valsearch = preg_replace('/[^A-Za-z0-9 ]/', '', $request->input('search'));

        if ($valsearch == "" || $valsearch == "0") {
            $q_search = "";
        } else {
            $q_search = " AND name like '%" . $valsearch . "%'";
        }
        $location = Location::whereRaw('1 ' . $q_search)
            ->orderBy('name', 'asc')
            ->paginate(10);
        return view('admin/pages/location', compact('location'));
    }


    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        $location = new $this->location;
        $location->name      = $request->name;
        $location->longitude      = $request->longitude;
        $location->latitude     = $request->latitude;
        $location->parent_id             = $request->parent_id;
        $location->icon         = null;
        $location->status           = '1';

        $location->save();
        
        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Create new location";
        $logs->data_content = $location;
        $logs->table_name   = 'locations';
        $logs->column_name  = 'slug, name, parent_id, icon, longitude, latitude, status, ordering,';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = "web";

        $logs->save();

        return redirect('admin/locations')
            ->with('status', 1)
            ->with('message', "Data Created!");
    }

    public function show(Location $location)
    {
        return view('admin.pages.location-detail', [

            'location' => $location
        ]);
    }

    public function edit($id)
    {
        //
    }
    public function update(Request $request, $id)
    {   
        $location = $this->location->find($id);

        if ($location) {
            $location->longitude       = $request->input('longitude');
            $location->parent_id              = $request->input('parent_id');
            $location->name       = $request->input('name');
            $location->latitude      = $request->input('latitude');
            $location->icon          = $request->input('icon');
            $location->status            = $request->input('status');

            $location->save();
        
            // logs
            $logs = $this->logs;
    
            $logs->log_time     = Carbon::now();
            $logs->activity     = "Update location with id : " . $id;
            $logs->data_content = $location;
            $logs->table_name   = 'locations';
            $logs->column_name  = 'slug, name, parent_id, icon, longitude, latitude, status, ordering,';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";
    

            return redirect($request->input('url'))
                ->with('status', 1)
                ->with('message', "Data Saved!");
        }
    }


    public function destroy($id)
    {
        $location = $this->location->find($id);
        if (isset($location)) {
            \AboutImage::deleteImage($location->icon);
            $location->delete();

            // logs
            $logs = $this->logs;
    
            $logs->log_time     = Carbon::now();
            $logs->activity     = "Delete location with id : " . $id;
            $logs->table_name   = 'locations';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";
    
            $logs->save();
        }

        return redirect('admin/locations')
            ->with('status', 2)
            ->with('message', "Data Deleted!");
    }
}
