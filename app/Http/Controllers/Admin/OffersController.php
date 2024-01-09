<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Location;
use App\Product;
use App\Penawaran;
use App\PenawaranItem;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use Intervention\Image\ImageManagerStatic as Image;

class OffersController extends Controller
{
    protected $penawaran;

    public function __construct(Penawaran $penawaran, PenawaranItem $penawaranItem, Product $product)
    {
        $this->penawaran = $penawaran;
        $this->penawaranItem = $penawaranItem;
        $this->product = $product;
       
    }

    public function index()
    {
        $location = Location::where('status', '1')
                    ->get();
        $offers = Penawaran::all();

         return view('admin/pages/product-offers', compact('location','offers'));
    }

    public function store(Request $request)
    {       
            if ($request->input('location')!='') {
              $location = implode(',',$request->input('location'));
            }else{
                $location='';
            }

            $penawaran = $this->penawaran;
            $penawaran->title = $request->input('title');
            $penawaran->description = $request->input('description');
            $penawaran->location = $location;
            $penawaran->day_start = $request->input('start_at')." ".$request->input('time_start_at');
            $penawaran->day_end = $request->input('end_at')." ".$request->input('time_end_at');
            $penawaran->status = $request->input('status');

         if ($request->hasFile('icon')) {

                $file = $request->file('icon');
                $ext = $file->getClientOriginalExtension();

                $newName = "offers-" . date('Y-m-d-His-') . "." . $ext;

                if (file_exists($penawaran->icon)) {
                    unlink($penawaran->icon); //menghapus file lama
                }

                $image_resize = Image::make($file->getRealPath());
                $image_resize->save(public_path('images/' . $newName));
                $penawaran->icon = 'images/' . $newName;
        } 
        
        $penawaran->save();

         return $this->detail_offers($penawaran->id)
                ->with('status', 1)
                ->with('message', "Data Berhasil Di Simpan!");
    }

    public function update(Request $request)
    {
        $penawaran = Penawaran::find($request->input('data-id'));

        if ($request->input('location')!='') {
              $location = implode(',',$request->input('location'));
            }else{
                $location='';
            }

         if ($penawaran) {
            $penawaran->title = $request->input('title');
            $penawaran->description = $request->input('description');
            $penawaran->location = $location;
            $penawaran->day_start = $request->input('start_at')." ".$request->input('time_start_at');
            $penawaran->day_end = $request->input('end_at')." ".$request->input('time_end_at');
            $penawaran->status = $request->input('status');

         if ($request->hasFile('icon')) {

                $file = $request->file('icon');
                $ext = $file->getClientOriginalExtension();

                $newName = "offers-" . date('Y-m-d-His-') . "." . $ext;

                if (file_exists($penawaran->icon)) {
                    unlink($penawaran->icon); //menghapus file lama
                }

                $image_resize = Image::make($file->getRealPath());
                $image_resize->save(public_path('images/' . $newName));
                $penawaran->icon = 'images/' . $newName;
        } 
        
            $penawaran->save();

            return $this->detail_offers($penawaran->id)
                    ->with('status', 1)
                    ->with('message', "Data Berhasil Di Ubah!");
        }else{
             return redirect($request->input('url'))
                ->with('status', 1)
                ->with('message', "Data Error !");
        }
    }

    public function destroy($id)
    {

        $data = $this->penawaran->find($id);
        $data->Delete();

         return redirect('/admin/product-offers')
                ->with('status', 1)
                ->with('message', "Data Berhasil Di Hapus!");
    }

    public function detail_offers($id){
        $penawaran = Penawaran::find($id);
        $location = Location::where('status', '1')
                    ->get();

        return view('admin/pages/offers_detail', compact('penawaran','location'));

    }

    public function storeOffers(Request $request){

        $penawaranItem = $this->penawaranItem;
        $penawaranItem->product_offer_id = $request->offer_id;
        $penawaranItem->product_id = $request->product_id;
        $penawaranItem->views = $request->views;
        $penawaranItem->stock = $request->stock;
        $penawaranItem->sold = $request->sold;
        $penawaranItem->description= $request->description;
        $penawaranItem->save();

        $status = 1;
        $msg = 'save data success';
        $data = $penawaranItem;
        return response()->json(compact('status', 'msg', 'data'), 200);
    }

     function listOffers($id){
        $data = $this->penawaranItem->where('product_offer_id',$id)
                ->with('product')
                ->get();

        $status = 1;
        $msg = 'get data success';
        $data = $data;
        return response()->json(compact('status', 'msg', 'data'), 200);
    }

    function removeOffersItem(Request $request){
        $data = $this->penawaranItem->find($request->id);
        $data->forceDelete();

        $status = 1;
        $msg = 'delete data success';
        $data = $data;
        return response()->json(compact('status', 'msg', 'data'), 200);
    }
}
