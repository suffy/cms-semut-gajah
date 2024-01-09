<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Jobs\SendNotificationPrice;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use App\User;

class ProductNotifPriceController extends Controller
{
    protected $user;
    public function __construct(User $user)
    {
        $this->user     = $user;
    }

    public function index()
    {
        return view('admin.pages.products-notif-price');
    }

    public function send(Request $request)
    {
        try {
            $message    = $request->message;
            $productId  = $request->id_product;
            $users      = $this->user->select('id', 'fcm_token', 'salur_code', 'site_code')
                                ->whereNotNull('fcm_token')
                                ->get();

            if($users->count() > 499) {
                $chunks  = array_chunk($users->toArray(), 500);
                $minute  = 0;
                foreach($chunks as $row) {
                    $minute += 1;
                    $on = Carbon::now()->addMinutes($minute);
                    dispatch(new SendNotificationPrice($message, $row, $productId))->delay($on);
                }
            } else {
                $row  = $users->toArray();
                dispatch(new SendNotificationPrice($message, $row, $productId));
            }
            return response()->json(['success' => true], 200);
            
        } catch(\Exception $e) {
            dd($e->getMessage());
        }
    }
}
