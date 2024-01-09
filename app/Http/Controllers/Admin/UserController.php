<?php

namespace App\Http\Controllers\Admin;

use App\About;
use App\Album;
use App\AlbumGallery;
use App\Article;
use App\ArticleCategory;
use App\ArticleGallery;
use App\Banner;
use App\Contact;
use App\ContactOffice;
use App\Product;
use App\ProductCategory;
use App\ProductFeature;
use App\ProductGallery;
use App\SocialMedia;
use App\User;
use App\MappingSite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Intervention\Image\ImageManagerStatic as Image;

class UserController extends Controller
{
    function pageCustomer(Request $request)
    {
        $customer = User::where('account_type','4')
                    ->paginate(20);

        return view('admin.pages.customers-old')
            ->with('customers', $customer);
    }

    function pageCustomerDetail($id)
    {
        $mappingSites = MappingSite::get();

        $customer = User::where('id',$id)
                    ->first();

        if($customer){
            return view('admin.pages.customer-detail')
                ->with('user', $customer)
                ->with('mappingSites', $mappingSites);
        }else{
            return redirect(url('admin/customers'));
        }

    }

    function storeUser(Request $request)
    {
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'account_status' => 1,
            'account_role' => "superadmin",
            'account_type' => 1
        ]);
        

        if ($user) {
            return redirect($request->input('url'))
                ->with('status', 1)
                ->with('message', "Data Saved!");
        }

    }

    function updateUser(Request $request)
    {

        $user = User::find($request->input('id'));

        if ($user) {
            $user->name = $request->input('name');
            $user->email = $request->input('email');

            if ($request->input('password') !== null) {
                $user->password = Hash::make($request->input('password'));
            };

            $user->save();
        }

        if ($user) {
            return redirect($request->input('url'))
                ->with('status', 1)
                ->with('message', "Data Saved!");
        }

    }

    function deleteUser($id)
    {

        $farm = User::find($id);
        if (isset($farm)) {
            $farm->delete();
        }

        return redirect('admin/users')
            ->with('status', 2)
            ->with('message', "Data Deleted!");

    }

    public function changeStatus(Request $request)
    {
        $user = User::find($request->user_id);
        $user->account_status = $request->input('account_status');
            
        $user->save();
        return response()->json(['success'=>'1']);
    }
}