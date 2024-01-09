<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\MappingSite;
use App\Log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class MappingSiteController extends Controller
{
    protected $mappingSites;
    protected $logs;

    public function __construct(MappingSite $mappingSite, Log $log)
    {
        $this->mappingSites = $mappingSite;
        $this->logs = $log;
    }

    public function index(Request $request)
    {
        $mappingSites = $this->mappingSites->query();

        if ($request->search) {
            $mappingSites   = $mappingSites
                ->where('kode', 'like', '%' . $request->search . '%')
                ->orWhere('branch_name', 'like', '%' . $request->search . '%')
                ->orWhere('nama_comp', 'like', '%' . $request->search . '%')
                ->orWhere('kode_comp', 'like', '%' . $request->search . '%')
                ->orWhere('sub', 'like', '%' . $request->search . '%')
                ->orWhere('status_ho', 'like', '%' . $request->search . '%')
                ->orWhere('telp_wa', 'like', '%' . $request->search . '%');
        }

        $mappingSites = $mappingSites->paginate(10);

        return view('admin.pages.mapping-site', compact('mappingSites'));
    }

    function fetch_data(Request $request)
    {
        $search = $request->search;
        if ($request->ajax()) {
            $mappingSites = $this->mappingSites
                ->where('kode', 'like', '%' . $search . '%')
                ->orWhere('branch_name', 'like', '%' . $search . '%')
                ->orWhere('nama_comp', 'like', '%' . $search . '%')
                ->orWhere('kode_comp', 'like', '%' . $search . '%')
                ->orWhere('sub', 'like', '%' . $search . '%')
                ->orWhere('status_ho', 'like', '%' . $request->search . '%')
                ->orWhere('telp_wa', 'like', '%' . $request->search . '%')
                ->orderBy('branch_name', 'asc')
                ->paginate(10);
            return view('admin.pages.pagination_data', compact('mappingSites'))->render();
        }
    }

    public function store(Request $request)
    {
        // insert into mapping_site table
        $mappingSite = $this->mappingSites;

        $mappingSite->site_id   = $request->site_id;
        $mappingSite->site_name = $request->site_name;
        $mappingSite->name      = $request->name;

        $mappingSite->save();

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Create new site id and site name";
        $logs->table_id     = $mappingSite->id;
        $logs->data_content = $mappingSite;
        $logs->table_name   = 'mapping_site';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = 'web';

        $logs->save();

        return redirect($request->input('url'))
            ->with('status', 1)
            ->with('message', "Data Saved!");
    }

    public function update(Request $request, $id)
    {
        // update
        $mappingSite = $this->mappingSites->find($request->input('id'));

        $mappingSite->site_id   = $request->site_id;
        $mappingSite->site_name = $request->site_name;
        $mappingSite->name      = $request->name;

        $mappingSite->save();

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Update site id/site name with id : " . $id;
        $logs->table_id     = $id;
        $logs->data_content = $mappingSite;
        $logs->table_name   = 'mapping_site';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = 'web';

        $logs->save();

        return redirect($request->input('url'))
            ->with('status', 1)
            ->with('message', "Data Updated!");
    }

    public function destroy(Request $request, $id)
    {
        // delete
        $mappingSite = $this->mappingSites->destroy($id);

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Delete site id and site name with id : " . $id;
        $logs->table_id     = $id;
        $logs->data_content = $mappingSite;
        $logs->table_name   = 'users';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = 'web';

        $logs->save();

        return redirect($request->input('url'))
            ->with('status', 1)
            ->with('message', "Data Deleted!");
    }

    public function ajaxFetch(Request $request)
    {
        $id = $request->id;
        $site = $this->mappingSites->find($id);
        return $site;
    }

    public function siteUpdate(Request $request)
    {
        $this->validate($request, [
            'site_name'       => 'required',
            'site_id' => 'required',
            'id' => 'required',
            'min_transaction'   => 'required',
        ]);

        $this->mappingSites->whereId($request->id)->update([
            'min_transaction' => str_replace(',', '', $request->min_transaction)
        ]);

        // update data to erp
        $cek = Http::put('http://site.muliaputramandiri.com/restapi/api/master_data/site', [
            'X-API-KEY'         => config('erp.x_api_key'),
            'token'             => config('erp.token_api'),
            'site_code'         => $request->site_id,
            'min_transaksi'     => str_replace(',', '', $request->min_transaction),
        ]);

        return redirect('/manager/mapping-site')->with('status', 1)
            ->with('message', "Data Updated!");
    }
}
