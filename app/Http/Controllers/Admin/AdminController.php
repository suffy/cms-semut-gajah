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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\ImageManagerStatic as Image;

class AdminController extends Controller
{

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


}