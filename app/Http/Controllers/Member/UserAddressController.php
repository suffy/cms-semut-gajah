<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\UserAddress;
use Alert;

class UserAddressController extends Controller
{
    protected $user;
    protected $user_address;

    public function __construct(User $user, UserAddress $user_address){
        $this->user = $user;
        $this->user_address = $user_address;
    }

    public function index(){
        $id = Auth::user()->id;
        $user = $this->user::findOrFail($id);
        $user_address = $this->user_address->where('user_id', $id)->orderBy('id', 'desc')->get();

        return view('public.member.user-address', compact('user', 'user_address'));
    }

    public function createAddress(Request $request)
    {
        $userid = $request->input('user_id');
        $data_user = User::where('id', $userid)
            ->first();

        $name = $request->get('name');
        $address_name = $request->get('address_name');
        $address_phone = $request->get('address_phone');
        $address = $request->get('address');
        $kecamatan = $request->get('kecamatan');
        $kota = $request->get('kota');
        $provinsi = $request->get('provinsi');
        $kode_pos = $request->get('kode_pos');

        if ($data_user) {

            $data = $this->user_address->create([
                'user_id' => $userid,
                'name' => $name,
                'address_name' => $address_name,
                'address_phone' => $address_phone,
                'address' => preg_replace('/[^A-Za-z0-9 !@#$%^&*().]/u','', strip_tags($address)),
                'kecamatan' => $kecamatan,
                'kota' => $kota,
                'provinsi' => $provinsi,
                'kode_pos' => $kode_pos,
                'status' => 1
            ]);

            $status = 201;
            $msg = 'create user address success';
            // return response()->json(compact('status', 'msg', 'data'), 201);
            alert()->success('Success!','Address has successfully added!');
            return redirect('member/address');
        } else {
            $status = 404;
            $msg = 'id user not found';
            // return response()->json(compact('status', 'msg'), 200);
            alert()->error('Error!','Address has not added!');
            return redirect('member/address');
        }
    }

    public function getAddressByUserId($userid)
    {
        $data = $this->user_address->orderBy('id', 'desc')
            ->where('user_id', $userid)
            ->get();

        if (!$data->isEmpty()) {
            $status = 200;
            $msg = 'show user address by user id success';
            return response()->json(compact('status', 'msg', 'data'), 200);
        } else {
            $status = 404;
            $data = [];
            $msg = 'id user not found';
            return response()->json(compact('status', 'msg', 'data'), 200);
        }
    }

    public function deleteAddressById($id)
    {
        $data = $this->user_address->find($id);
        $data->delete();

        $status = 200;
        $msg = 'delete successfully';
        return response()->json(compact('status', 'msg', 'data'), 200);
    }

    public function updateAddress(Request $request, $addressid)
    {
        $default_address = ($request->default_address)?1:0;

        $user_addresses = $this->user_address->where('user_id', $request->get('user_id'))->get();

        $data_address = $this->user_address->find($addressid);

        $data_address->user_id = $request->get('user_id');
        $data_address->name = $request->get('name');
        $data_address->address_name = $request->get('address_name');
        $data_address->address_phone = $request->get('address_phone');
        $data_address->address = preg_replace('/[^A-Za-z0-9 !@#$%^&*().]/u','', strip_tags($request->get('address')));
        $data_address->kecamatan = $request->get('kecamatan');
        $data_address->kota = $request->get('kota');
        $data_address->provinsi = $request->get('provinsi');
        $data_address->kode_pos = $request->get('kode_pos');
        $data_address->status = 1;

        if($default_address == 1) {
            foreach ($user_addresses as $key) {
                $key->default_address = 0;
                $key->save();
            }
        }
        $data_address->default_address = $default_address;

        $data_address->save();

        $data = $data_address;

        $status = 201;
        $msg = 'update user address success';
        // return response()->json(compact('status', 'msg', 'data'), 201);
        alert()->success('Success!','Address has successfully updated!');
        return redirect('member/address');

    }

    public function deleteAddress($id)
    {
        $user_address = $this->user_address->find($id)->delete();
        alert()->success('Success!','Address has successfully deleted!');
        return redirect('member/address');
    }

    public function checkoutNewAddress(Request $request)
    {
        $userid = $request->input('user_id');
        $data_user = User::where('id', $userid)
            ->first();

        $name = $request->get('name');
        $address_name = $request->get('address_name');
        $address_phone = $request->get('address_phone');
        $address = $request->get('address');
        $kecamatan = $request->get('kecamatan');
        $kota = $request->get('kota');
        $provinsi = $request->get('provinsi');
        $kode_pos = $request->get('kode_pos');

        if ($data_user) {

            $data = $this->user_address->create([
                'user_id' => $userid,
                'name' => $name,
                'address_name' => $address_name,
                'address_phone' => $address_phone,
                'address' => preg_replace('/[^A-Za-z0-9 !@#$%^&*().]/u','', strip_tags($address)),
                'kecamatan' => $kecamatan,
                'kota' => $kota,
                'provinsi' => $provinsi,
                'kode_pos' => $kode_pos,
                'status' => 1
            ]);

            alert()->success('Success!','Address has successfully added!');
            return redirect('checkout');
        } else {
            alert()->error('Error!','Address has not added!');
            return redirect('checkout');
        }
    }

}
