<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Salesman;
use App\User;
use App\MetaUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    protected $salesmen, $metas;

    public function __construct(Salesman $salesman, MetaUser $metas)
    {
        $this->salesmen = $salesman;
        $this->metas    = $metas;
    }

    public function index(Request $request)
    {
        // $salesmen = $this->salesmen->query();
        $salesmen = $this->metas->select('salesman_code', DB::raw('count(salesman_code) as total'))->groupBy('salesman_code', 'user_meta.site_code');

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
