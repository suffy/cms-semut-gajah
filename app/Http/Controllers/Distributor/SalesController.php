<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Salesman;
use App\User;
use App\MetaUser;
use App\MappingSite;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    protected $user, $salesmen, $metas, $mappingSite;

    public function __construct(User $user, Salesman $salesman, MetaUser $metas, MappingSite $mappingSite)
    {
        $this->users = $user;
        $this->salesmen = $salesman;
        $this->metas = $metas;
        $this->mappingSite = $mappingSite;
    }

    public function index(Request $request)
    {
        try {
            if(Auth::user()->account_role == 'distributor') {
                $salesmen = $this->metas->select('salesman_code', DB::raw('count(salesman_code) as total'))->where('user_meta.site_code', Auth::user()->site_code)->groupBy('salesman_code', 'user_meta.site_code');
            } else if(Auth::user()->account_role == 'distributor_ho') {
                $sites = $this->mappingSite->where('kode', Auth::user()->site_code)->with(['ho_child' => function($q) {
                    $q->select('kode', 'sub');
                }])->first();
                
                $array_child = [];
                foreach($sites->ho_child as $child) {
                    array_push($array_child, $child->kode);
                }
                
                $salesmen = $this->metas->select('salesman_code', DB::raw('count(salesman_code) as total'))->whereIn('user_meta.site_code', $array_child)->groupBy('salesman_code', 'user_meta.site_code');
            }
        
            $search = $request->search;
            if ($search) {
                $salesmen   = $salesmen->whereHas('salesman', function($q) use ($search) {
                                        return $q->where('kodesales', 'like', '%'.$search.'%')
                                                ->orWhere('kodesales_erp', 'like', '%'.$search.'%')
                                                ->orWhere('namasales', 'like', '%'.$search.'%')
                                                ->orWhere('kode', 'like', '%'.$search.'%');
                });
            } else {
                $salesmen   = $salesmen->with('salesman');
            }
        
            $salesmen = $salesmen->orderBy('site_code', 'asc')->paginate(10);
    
            return view('admin.pages.salesman', compact('salesmen'));
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    function fetch_data(Request $request)
    {
        $search = $request->search;
        if($request->ajax()){
            $salesmen = $this->salesmen
                        ->where('kodesales', 'like', '%' . $request->search . '%')
                        ->orWhere('kodesales_erp', 'like', '%' . $request->search . '%')
                        ->orWhere('namasales', 'like', '%' . $request->search . '%')
                        ->orWhere('kode', 'like', '%' . $request->search . '%')
                        ->orderBy('kodesales', 'asc')
                        ->paginate(10);
            return view('admin.pages.pagination_data_salesman', compact('salesmen'))->render();
        }
    }
}
