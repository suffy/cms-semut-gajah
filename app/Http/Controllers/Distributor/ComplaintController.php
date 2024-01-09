<?php

namespace App\Http\Controllers\Distributor;

use App\Complaint;
use App\ComplaintDetail;
use App\ComplaintFile;
use App\Log;
use App\Order;
use App\Product;
use App\MappingSite;
use App\OrderDetail;
use App\Credit;
use App\UserAddress;
use App\User;
use App\CreditHistory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Intervention\Image\ImageManagerStatic as InterImage;

class ComplaintController extends Controller
{
    protected $complaints, $complaintDetails, $complaintFile, $log, $orderDetail, $credit, $user, $orders, $product, $mappingSite;
    
    public function __construct(Complaint $complaint, ComplaintDetail $complaintDetail, ComplaintFile $complaintFile, Log $log, OrderDetail $orderDetail, Credit $credit, User $user, UserAddress $userAddress, MappingSite $mappingSite ,CreditHistory $creditHistory, Order $orders, Product $product)
    {
        $this->complaints       = $complaint;
        $this->complaintDetails = $complaintDetail;
        $this->complaintFile    = $complaintFile;
        $this->logs             = $log;
        $this->orderDetail      = $orderDetail;
        $this->orders           = $orders;
        $this->product          = $product;
        $this->credit           = $credit;
        $this->user             = $user;
        $this->userAddress      = $userAddress;
        $this->mappingSite      = $mappingSite;
        $this->creditHistory    = $creditHistory;
    }

    public function index(Request $request)
    {
        $complaints = $this->complaints->query();
        if(Auth::user()->account_role == 'distributor') {
            $user = $this->user->find(Auth::user()->id);
            
            if ($request->status == '1') {
                $complaints = $complaints
                            ->with(['order', 'order_details' => function ($query) {
                                $query->whereNotNull('product_id');
                            }, 'product', 'user'])
                            ->whereHas('user', function($query) use ($user) {
                                $query->where('site_code', $user->site_code);
                            })
                            ->where('status', '1');
            }
    
            if ($request->status == 'confirmed') {
                $complaints = $complaints
                            ->with(['order', 'order_details' => function ($query) {
                                $query->whereNotNull('product_id');
                            }, 'product', 'user'])
                            ->whereHas('user', function($query) use ($user) {
                                $query->where('site_code', $user->site_code);
                            })
                            ->where('status', 'confirmed');
            }
    
            if ($request->status == 'rejected') {
                $complaints = $complaints
                            ->with(['order', 'order_details' => function ($query) {
                                $query->whereNotNull('product_id');
                            }, 'product', 'user'])
                            ->whereHas('user', function($query) use ($user) {
                                $query->where('site_code', $user->site_code);
                            })
                            ->where('status', 'rejected');
            }
    
            if ($request->search) {
                $complaints = $complaints
                            ->with(['order', 'order_details' => function ($query) {
                                $query->whereNotNull('product_id');
                            }, 'product', 'user'])
                            ->whereHas('user', function($query) use ($request, $user)
                            {
                                return $query->where('site_code', $user->site_code)->where('name', 'like', '%' . $request->search . '%');
                            });
            }
    
            if ($request->status || $request->search) {
                $complaints = $complaints
                            ->orderBy('created_at', 'DESC')
                            ->paginate(10);
    
            } else {
                // get all
                $complaints = $complaints
                            ->with(['order', 'order_details' => function ($query) {
                                $query->whereNotNull('product_id');
                            }, 'user'])
                            ->whereHas('user', function($query) use ($user) {
                                $query->where('site_code', $user->site_code);
                            })
                            ->orderBy('created_at', 'DESC')
                            // ->where('status', null)
                            ->paginate(10);
            }
        } else if(Auth::user()->account_role == 'distributor_ho') {
            $sites = $this->mappingSite->where('kode', Auth::user()->site_code)->with(['ho_child' => function($q) {
                $q->select('kode', 'sub');
            }])->first();
            
            $array_child = [];
            foreach($sites->ho_child as $child) {
                array_push($array_child, $child->kode);
            }
            
            if ($request->status == '1') {
                $complaints = $complaints
                            ->with(['order', 'order_details' => function ($query) {
                                $query->whereNotNull('product_id');
                            }, 'product', 'user'])
                            ->whereHas('user', function($query) use ($user) {
                                $query->whereIn('site_code', $array_child);
                            })
                            ->where('status', '1');
            }
    
            if ($request->status == 'confirmed') {
                $complaints = $complaints
                            ->with(['order', 'order_details' => function ($query) {
                                $query->whereNotNull('product_id');
                            }, 'product', 'user'])
                            ->whereHas('user', function($query) use ($user) {
                                $query->whereIn('site_code', $array_child);
                            })
                            ->where('status', 'confirmed');
            }
    
            if ($request->status == 'rejected') {
                $complaints = $complaints
                            ->with(['order', 'order_details' => function ($query) {
                                $query->whereNotNull('product_id');
                            }, 'product', 'user'])
                            ->whereHas('user', function($query) use ($user) {
                                $query->whereIn('site_code', $array_child);
                            })
                            ->where('status', 'rejected');
            }
    
            if ($request->search) {
                $complaints = $complaints
                            ->with(['order', 'order_details' => function ($query) {
                                $query->whereNotNull('product_id');
                            }, 'product', 'user'])
                            ->whereHas('user', function($query) use ($request, $user)
                            {
                                return $query->whereIn('site_code', $array_child)->where('name', 'like', '%' . $request->search . '%');
                            });
            }
    
            if ($request->status || $request->search) {
                $complaints = $complaints
                            ->orderBy('created_at', 'DESC')
                            ->paginate(10);
    
            } else {
                // get all
                $complaints = $complaints
                            ->with(['order', 'order_details' => function ($query) {
                                $query->whereNotNull('product_id');
                            }, 'user'])
                            ->whereHas('user', function($query) use ($array_child) {
                                $query->whereIn('site_code', $array_child);
                            })
                            ->orderBy('created_at', 'DESC')
                            // ->where('status', null)
                            ->paginate(10);
            }
        }

        return view('admin.pages.complaint', compact('complaints'));
    }

    public function show($id)
    {
        $complaints = $this->complaintDetails
                    ->with(['complaint.order_details.product', 'complaint_file'])
                    ->whereHas('complaint.order_details', function ($query) {
                        $query->where('product_id', '!=', null);
                    })
                    ->where('complaint_id', $id)
                    ->paginate(10);
        
        $product_complaint = $this->complaints->find($id)->where('id', $id)->with('product')->first();

        return view('admin.pages.complaint-detail', compact(['complaints', 'product_complaint']));
    }
    
    public function store(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'title'     => 'required|string|max:255',
                'content'   => 'required|string',
                'file'      => 'mimes:jpeg,jpg,png,gif|max:1024'
            ]
        );

        if ($validator->fails()) {
            return redirect($request->url)
                ->with('status', 2)
                ->with('message', $validator->errors()->first());
        }

        // update status responded
        $complaint = $this->complaints->findOrFail($id);
        $user_id = $complaint->user_id;
        $complaint->responded = 'true';

        $complaint->save();

        // insert into complaint_details table
        $complaint = $this->complaintDetails;

        $complaint->complaint_id    = $id;
        $complaint->user_id         = Auth::user()->id;
        $complaint->title           = $request->title;
        $complaint->content         = $request->content;

        $complaint->save();

        // insert into complaint_files table
        $complaintFile = $this->complaintFile;

        $complaintFile->complaint_id        = $id;
        $complaintFile->complaint_detail_id = $complaint->id;

        if ($request->hasFile('file')) {
            $relPathImage = '/images/complaint/';
            if (!file_exists(public_path($relPathImage))) {
                mkdir(public_path($relPathImage), 0755, true);
            }
            
            $file = $request->file('file');
            $ext = $file->getClientOriginalExtension();

            $newName = "complaint" . date('YmdHis') . "." . $ext;

            $image_resize = InterImage::make($file->getRealPath());
            $image_resize->save(('images/complaint/' . $newName));
            $complaintFile->file_1 = '/images/complaint/'.$newName;
        }

        $complaintFile->save();

        $complaintsContent = $this->complaints
                        ->where('id', $id)
                        ->with(['complaint_detail', 'complaint_file', 'order.order_details.product'])
                        ->first();

        $logs   = $this->logs
                        ->create([
                            'log_time'      => Carbon::now(),
                            'activity'      => 'repply complaint',
                            'table_id'      => $id,
                            'data_content'  => $complaintsContent,
                            'table_name'    => 'complaints, complaint_details',
                            'column_name'   => 'complaint_details.user_id, complaint_details.complaint_id, copmlaint_details.title, copmlaint_details.content, complaint_files.complaint_id, complaint_files.complaint_detail_id, complaint_files.file_1, complaint.responded',
                            'from_user'     => auth()->user()->id,
                            'to_user'       => $user_id,
                            'platform'      => 'apps',
                        ]);

        $fcm_token  = $this->user->where('id', $user_id)->pluck('fcm_token')->all();

        $activity   = "Komplain Kamu Sudah dibalas Admin";

        $this->sendNotification($fcm_token, $activity);

        return redirect($request->url)
                ->with('status', 1)
                ->with('message', "Message sended!");
    }

    public function update($id)
    {
        // update seen
        $complaint = $this->complaintDetails
                        ->where('complaint_id', $id)->first();
        
        $complaint->seen    =   '1';
        $complaint->save();

        $complaintsContent = $this->complaints
                            ->where('id', $id)
                            ->with(['complaint_detail', 'complaint_file', 'order.order_details.product'])
                            ->first();

        $logs   = $this->logs
                    ->updateOrCreate([
                        'activity'      => 'seen complaint',
                        'table_id'      =>  $id
                    ],
                    [
                        'log_time'      => Carbon::now(),
                        'data_content'  => $complaintsContent,
                        'table_name'    => 'complaint_details',
                        'column_name'   => 'complaint_details.customer_id, orders.name, orders.phone, orders.provinsi, orders.kota, orders.kecamatan, orders.kelurahan, orders.address, orders.payment_method, orders.coupon_id, orders.status, orders.delivery_service, order_detail.product_id, order_detail.order_id, order_detail.price, order_detail.price, order_detail.large_qty, order_detail.large_unit, order_detail.medium_qty, order_detail.medium_unit, order_detail.small_qty, order_detail.small_unit, order_detail.total_price',
                        'from_user'     => auth()->user()->id,
                        'to_user'       => $complaint->user_id,
                        'platform'      => 'apps',
                    ]);

        return response()->json([
            'status' => true,
            'message' => 'successfully update seen',
            'data' => $complaint
        ]);
    }

    public function confirm($id)
    {
        // confirm complaint
        $complaint = $this->complaints->find($id);
        $user_id = $complaint->user_id;
        $brand_id   = $complaint->brand_id;

        if($complaint->option == "pengembalian dana") {
            // get order detail data
            $orderDetail = $this->orderDetail
                                        ->whereNotNull('product_id')
                                        ->where('order_id', $complaint->order_id)
                                        ->whereNotNull('product_id')
                                        ->where('product_id', $complaint->brand_id)
                                        ->with('order.user:id,customer_code')
                                        ->select('order_id', 'total_price', 'qty')
                                        ->first();

            // get price by orderDetail
            $price = $orderDetail->total_price / $orderDetail->qty;
            $price = $price * $complaint->qty;

            $credits = DB::table('credits')
                            ->where('customer_id', $orderDetail->order->user->id)
                            ->first();

            $credit_price = $price;

            if (!is_null($credits)) {
                $credit_price = $credits->credit += $price;
            }

            $credits     =   $this->credit
                            ->updateOrCreate(
                                ['customer_id'    => $orderDetail->order->user->id],
                                [
                                    'credit'        => round($credit_price)
                                ]);
            
            $creditHistory = $this->creditHistory
                                        ->create([
                                            'credit_id'     =>  $credits->id,
                                            'order_id'      =>  $complaint->order_id,
                                            'product_id'    =>  $complaint->brand_id,
                                            'deposit'       =>  round($price),
                                            'status'        =>  'pengembalian dana complaint'
                                        ]);

            $logs   = $this->logs
                                ->updateOrCreate([
                                    'activity'      => 'successfully sent credit',
                                    'table_id'      =>  $id
                                ],
                                [
                                    'log_time'      => Carbon::now(),
                                    'data_content'  => $credits,
                                    'table_name'    => 'complaints',
                                    'column_name'   => 'complaints.status, complaints.confirm_at',
                                    'from_user'     => auth()->user()->id,
                                    'to_user'       => $credits->customer_id,
                                    'platform'      => 'web',
                                ]);
            
            $fcm_token  = $this->user->where('id', $user_id)->pluck('fcm_token')->all();

            $complaint->status      =   'confirmed';
            $complaint->confirm_at  =   Carbon::now();
            $complaint->save();
    
            $complaintsContent = $this->complaints
                                    ->where('id', $id)
                                    ->with(['complaint_detail', 'complaint_file', 'order.order_details.product'])
                                    ->first();
                            
            $logs   = $this->logs
                            ->updateOrCreate([
                                'activity'      => 'confirmed complaint',
                                'table_id'      =>  $id
                            ],
                            [
                                'log_time'      => Carbon::now(),
                                'data_content'  => $complaintsContent,
                                'table_name'    => 'complaints',
                                'column_name'   => 'complaints.status, complaints.confirm_at',
                                'from_user'     => auth()->user()->id,
                                'to_user'       => $complaint->user_id,
                                'platform'      => 'apps',
                            ]);
                            
            $fcm_token  = $this->user->where('id', $user_id)->pluck('fcm_token')->all();

            $activity   = "Dana dengan order invoice " . $complaintsContent->order->invoice . " sudah dikembalikan ke kredit";
    
            $this->sendNotification($fcm_token, $activity);
        } else {
            $user   = $this->userAddress
                                    ->join('users', 'users.id', '=', 'user_address.user_id')
                                    ->where('users.id', $user_id)
                                    ->where('user_address.default_address', '1')
                                    ->first();

            $product = $this->product   
                            ->whereId($brand_id)
                            ->with('price')
                            ->first();

            $siteCode   = $this->mappingSite
                            ->find($user->mapping_site_id);

            $konversi_sedang_ke_kecil = isset($product->konversi_sedang_ke_kecil) ? $product->konversi_sedang_ke_kecil : 0;

            $totalPrice              = $complaint->qty * $product->price->harga_ritel_gt;
            $priceApps                = 0;
    
            if ($user->salur_code == 'WS' || $user->salur_code == 'SO' || $user->salur_code == 'SW') {
                if ($user->class == 'GROSIR') {
                    if ($product->brand_id == '001' || $product->brand_id == '005' && $product->herbana_status == null) {
                        $priceApps  = $product->price->harga_ritel_gt;
                        $totalPrice = ($product->price->harga_ritel_gt * $complaint->qty) - ($product->price->harga_ritel_gt * $complaint->qty * (4.5/100));
                        $disc_principal = 4.5;
                    }
                } elseif ($user->class == 'SEMI GROSIR') {
                    if ($product->brand_id == '001' || $product->brand_id == '005' && $product->herbana_status == null) {
                        $priceApps  = $product->price->harga_ritel_gt;
                        $totalPrice = ($product->price->harga_ritel_gt * $complaint->qty) - ($product->price->harga_ritel_gt * $complaint->qty * (3/100));
                        $disc_principal = 3;
                    }
                } else {
                    $priceApps  = $product->price->harga_grosir_mt;
                    $totalPrice = $product->price->harga_grosir_mt * $complaint->qty;
                    $disc_principal = 0;
                }
            } elseif ($user->salur_code == 'RT') {
                if ($user->class == 'RITEL') {
                    if ($product->brand_id == '001' && $product->herbana_status == null) {
                        $priceApps  = $product->price->harga_ritel_gt;
                        $totalPrice = ($product->price->harga_ritel_gt * $complaint->qty);
                        $disc_principal = 0;
                    }
                } else {
                    $priceApps  = $product->price->harga_ritel_gt;
                    $totalPrice = $product->price->harga_ritel_gt * $complaint->qty;
                    $disc_principal = 0;
                }
            } else {
                $priceApps  = $product->price->harga_ritel_gt;
                $totalPrice = $product->price->harga_ritel_gt * $complaint->qty;
                $disc_principal = 0;
            }
            
            $orders = $this->orders
                                ->create([
                                    'invoice'               => $user->customer_code . strtotime(Carbon::now()),
                                    'customer_id'           => $user_id,
                                    'name'                  => $user->name,
                                    'phone'                 => $user->phone,
                                    'provinsi'              => $user->provinsi,
                                    'kota'                  => $user->kota,
                                    'kecamatan'             => $user->kecamatan,
                                    'kelurahan'             => $user->kelurahan,
                                    'kode_pos'              => $user->kode_pos,
                                    'address'               => $user->address,
                                    'site_code'             => $user->site_code,
                                    'payment_method'        => 'cod',
                                    'payment_total'         => $totalPrice,
                                    'payment_discount'      => NULL,
                                    'payment_final'         => $totalPrice,
                                    'coupon_id'             => NULL,
                                    'payment_discount_code' => null,
                                    'order_time'            => Carbon::now()->format('Y-m-d H:i:s'),
                                    'status'                => '1',
                                    'status_faktur'         => 'R',
                                    'delivery_service'      => NULL
                                ]);
            
            // insert order into erp
            Http::post('http://site.muliaputramandiri.com/restapi/api/master_data/orders', [
                    'X-API-KEY'             => config('erp.x_api_key'),
                    'token'                 => config('erp.token_api'),
                    'order_id'              => $orders->id,
                    'invoice'               => $orders->invoice,
                    'customer_id'           => $id,
                    'subscribe_id'          => null,
                    'name'                  => $user->name,
                    'phone'                 => $user->phone,
                    'address'               => $user->address,
                    'location'              => null,
                    'id_provinsi'           => null,
                    'provinsi'              => $user->provinsi,
                    'id_kota'               => null,
                    'kota'                  => $user->kota,
                    'id_kelurahan'          => null,
                    'kelurahan'             => $user->kelurahan,
                    'id_kecamatan'          => null,
                    'kecamatan'             => $user->kecamatan,
                    'kode_pos'              => $user->kode_pos,
                    'latitude'              => null,
                    'longitude'             => null,
                    'payment_method'        => 'cod',
                    'payment_link'          => null,
                    'payment_date'          => null,
                    'payment_total'         => $totalPrice,
                    'coupon_id'             => null,
                    'payment_discount_code' => null,
                    'payment_discount'      => null,
                    'payment_code'          => null,
                    'order_weight'          => null,
                    'order_distance'        => null,
                    'delivery_status'       => null,
                    'delivery_fee'          => null,
                    'delivery_track'        => null,
                    'delivery_time'         => null,
                    'delivery_date'         => null,
                    'order_time'            => Carbon::now()->format('Y-m-d H:i:s'),
                    'confirmation_time'     => null,
                    'notes'                 => null,
                    'status'                => '1',
                    'status_faktur'         => 'R',        
                    'site_code'             => $siteCode->kode,
                    'created_at'            => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at'            => Carbon::now()->format('Y-m-d H:i:s'),
                    'deleted_at'            => null,
                    'payment_final'         => $totalPrice,
                    'photo'                 => null,
                    'courier'               => null,
                    'delivery_service'      => 'cod',
                    'status_update_erp'     => null,
                    'server'                => 'staging'
                ]);

            $orderDetail  = $this->orderDetail
                                        ->create([
                                            'product_id'    => $product->id,
                                            'order_id'      => $orders->id,
                                            'konversi_sedang_ke_kecil' => $konversi_sedang_ke_kecil,
                                            'qty_konversi'  => $complaint->qty * $konversi_sedang_ke_kecil,
                                            'large_unit'    => null,
                                            'medium_unit'   => null,
                                            'small_unit'    => null,
                                            'qty'           => $complaint->qty,
                                            'notes'         => 'retur complaint',
                                            'price_apps'    => $priceApps,
                                            'total_price'   => $totalPrice,
                                            'disc_principal'=> $disc_principal
                                        ]); 

            // insert order into erp
            Http::post('http://site.muliaputramandiri.com/restapi/api/master_data/orders_detail', [
                    'X-API-KEY'         => config('erp.x_api_key'),
                    'token'             => config('erp.token_api'),
                    // 'product_id'        => $request['product_id'],
                    'product_id'        => $product->kodeprod, // update 21-09-21
                    'order_id'          => $orders->id,
                    'invoice'           => $orders->invoice,
                    'large_price'       => null,
                    'large_qty'         => null,
                    'large_unit'        => null,
                    'medium_price'      => null,
                    'medium_qty'        => null,
                    'medium_unit'       => null,
                    'small_price'       => null,
                    'small_qty'         => $complaint->qty,
                    'small_unit'        => null,
                    'harga_product'     => null, // update 21 - 09 -21
                    'qty_konversi'      => $complaint->qty * $konversi_sedang_ke_kecil, // update 21 - 09 - 21
                    'item_disc'         => null, // update 08 - 10 - 21
                    'total_price'       => $totalPrice, 
                    'notes'             => 'retur complaint',
                    'created_at'        => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at'        => Carbon::now()->format('Y-m-d H:i:s'),
                    'deleted_at'        => null,
                    'product_review_id' => null,
                    'location_id'       => null,
                    'description'       => null,
                    'status'            => null,
                    'status_update_erp' => null,
                    'last_updated_erp'  => null,
                    'disc_principal'    => $disc_principal 
                ]);

            $orderResponse = $this->orders->where('id', $orders->id)->with('data_item.product')->first();

            $logs   = $this->logs
                                ->updateOrCreate([
                                    'activity'      => 'successfully ordered complaint with id ' . $id,
                                    'table_id'      =>  $id
                                ],
                                [
                                    'log_time'      => Carbon::now(),
                                    'data_content'  => $orderResponse,
                                    'table_name'    => 'complaints, orders, order_details',
                                    'column_name'   => 'orders.*, order_detail.*, complaints.status, complaints.confirm_at',
                                    'from_user'     => auth()->user()->id,
                                    'to_user'       => $complaint->user_id,
                                    'platform'      => 'web',
                                ]);

            $complaint->status      =   'confirmed';
            $complaint->confirm_at  =   Carbon::now();
            $complaint->save();
    
            $complaintsContent = $this->complaints
                                    ->where('id', $id)
                                    ->with(['complaint_detail', 'complaint_file', 'order.order_details.product'])
                                    ->first();
                            
            $logs   = $this->logs
                            ->updateOrCreate([
                                'activity'      => 'confirmed complaint',
                                'table_id'      =>  $id
                            ],
                            [
                                'log_time'      => Carbon::now(),
                                'data_content'  => $complaintsContent,
                                'table_name'    => 'complaints',
                                'column_name'   => 'complaints.status, complaints.confirm_at',
                                'from_user'     => auth()->user()->id,
                                'to_user'       => $complaint->user_id,
                                'platform'      => 'web',
                            ]);

            $fcm_token  = $this->user->where('id', $user_id)->pluck('fcm_token')->all();

            $activity   = "Komplain dengan invoice " . $complaintsContent->order->invoice . " sudah dikonfirmasi, silahkan menunggu barang dikirim";
    
            $this->sendNotification($fcm_token, $activity);
        }

        return response()->json([
            'status'    => true,
            'message'   => 'successfully complete complaint',
            'data'      => $complaint
        ]);
    }

    public function reject($id)
    {
        // confirm complaint
        $complaint = $this->complaints->find($id);
        $user_id = $complaint->user_id;

        $complaint->status      =   'rejected';
        $complaint->rejected_at  =   Carbon::now();
        $complaint->save();

        $complaintsContent = $this->complaints
                                ->where('id', $id)
                                ->with(['complaint_detail', 'complaint_file', 'order.order_details.product'])
                                ->first();
                        
        $logs   = $this->logs
                        ->updateOrCreate([
                            'activity'      => 'rejected complaint',
                            'table_id'      =>  $id
                        ],
                        [
                            'log_time'      => Carbon::now(),
                            'data_content'  => $complaintsContent,
                            'table_name'    => 'complaints',
                            'column_name'   => 'complaints.status, complaints.confirm_at',
                            'from_user'     => auth()->user()->id,
                            'to_user'       => $complaint->user_id,
                            'platform'      => 'apps',
                        ]);

        $fcm_token  = $this->user->where('id', $user_id)->pluck('fcm_token')->all();

        $activity   = "Komplain dengan invoice " . $complaintsContent->order->invoice . " ditolak oleh admin";

        $this->sendNotification($fcm_token, $activity);

        return response()->json([
            'status'    => true,
            'message'   => 'successfully reject complaint',
            'data'      => $complaint
        ]);
    }

    public function sendStuff($id)
    {
        // confirm complaint
        $complaint = $this->complaints->find($id);
        $user_id = $complaint->user_id;

        $complaint->status      =   'sended';
        $complaint->send_at  =   Carbon::now();
        $complaint->save();

        $complaintsContent = $this->complaints
                                ->where('id', $id)
                                ->with(['complaint_detail', 'complaint_file', 'order.order_details.product'])
                                ->first();
                        
        $logs   = $this->logs
                        ->updateOrCreate([
                            'activity'      => 'sended stuff complaint',
                            'table_id'      =>  $id
                        ],
                        [
                            'log_time'      => Carbon::now(),
                            'data_content'  => $complaintsContent,
                            'table_name'    => 'complaints',
                            'column_name'   => 'complaints.status, complaints.confirm_at',
                            'from_user'     => auth()->user()->id,
                            'to_user'       => $complaint->user_id,
                            'platform'      => 'apps',
                        ]);

        $fcm_token  = $this->user->where('id', $user_id)->pluck('fcm_token')->all();

        $activity   = "Barang komplain dengan invoice " . $complaintsContent->order->invoice . " sudah terkirim";

        $this->sendNotification($fcm_token, $activity);

        return response()->json([
            'status'    => true,
            'message'   => 'successfully reject complaint',
            'data'      => $complaint
        ]);
    }

    public function destroy($id)
    {
        $complaint = $this->complaints->destroy($id);

        $complaintsContent = $this->complaints
                                ->where('id', $id)
                                ->with(['complaint_detail', 'complaint_file', 'order.order_details.product'])
                                ->first();

        $logs   = $this->logs
                        ->create([
                            'log_time'      => Carbon::now(),
                            'activity'      => 'Complaint deleted',
                            'table_id'      => $id,
                            'data_content'  => $complaintsContent,
                            'table_name'    => 'complaints, complaint_details',
                            'column_name'   => 'complaint_details.user_id, complaint_details.complaint_id, copmlaint_details.title, copmlaint_details.content, complaint_files.complaint_id, complaint_files.complaint_detail_id, complaint_files.file_1, complaint.responded',
                            'from_user'     => auth()->user()->id,
                            'to_user'       => null,
                            'platform'      => 'apps',
                        ]);
        
        return redirect(url('manager/complaints'))
                ->with('status', 1)
                ->with('message', "Complaint deleted!");
    }

    public function sendNotification($fcm_token, $activity)
    {
        $SERVER_API_KEY = config('firebase.server_api_key');                        // get server_api_key from config

        $data = [
            "registration_ids" => $fcm_token,
            "notification" => [
                "title" => 'Status Complaint',
                "body" => $activity,  
            ]
        ];
        
        $dataString = json_encode($data);
    
        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];
    
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);
    }
}
