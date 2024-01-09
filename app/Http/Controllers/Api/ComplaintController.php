<?php

namespace App\Http\Controllers\Api;

use App\Complaint;
use App\ComplaintDetail;
use App\ComplaintFile;
use App\Http\Controllers\Controller;
use App\Order;
use App\Log;
use App\Credit;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use Intervention\Image\ImageManagerStatic as InterImage;


class ComplaintController extends Controller
{
    protected $complaints, $complaintDetails, $complaintFiles, $order;

    public function __construct(Complaint $complaint, ComplaintDetail $complaintDetail, ComplaintFile $complaintFile, Order $order, Log $log, Credit $credit)
    {
        $this->complaints       = $complaint;
        $this->complaintDetails = $complaintDetail;
        $this->complaintFiles   = $complaintFile;
        $this->order            = $order;
        $this->logs             = $log;
        $this->credit           = $credit;
    }

    // array for select product
    private function arraySelectComplaint()
    {
        return ['complaint_details.*', 'complaint_files.file_1', 'complaint_files.file_2', 'complaint_files.file_3', 'complaint_files.file_4'];
    }

    // array for select product
    private function arraySelectProduct()
    {
        return ['id', 'kodeprod', 'name','description', 'image', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_renceng', 'status_promosi_coret', 'status_herbana', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];
    }
    
    // array for select product
    private function arraySelectProductOld()
    {
        return ['id', 'kodeprod', 'name','description', 'image_backup as image', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];
    }

    public function get(Request $request)
    {
        try {
            if (! JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data'    => null
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data'    => null
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data'    => null
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data'    => null
            ], 400);
        }

        // check user login
        $id          = Auth::user()->id;
        $app_version = Auth::user()->app_version;
        $array       = $this->arraySelectComplaint();             
        $arrayOrder  = ['id', 'invoice', 'customer_id', 'subscribe_id', 'name', 'phone', 'address', 'kelurahan', 'kecamatan', 'kota', 'provinsi', 'payment_method', 'order_time', 'status', 'payment_total', 'payment_final', 'status_faktur', 'site_code', 'created_at', 'updated_at', 'delivery_service'];
        $arrayOrderDetail   = ['id', 'product_id', 'order_id', 'small_unit', 'konversi_sedang_ke_kecil', 'half', 'qty_konversi', 'qty', 'price_apps', 'total_price', 'product_review_id', 'promo_id', 'disc_cabang', 'rp_cabang', 'disc_principal', 'rp_principal', 'point_principal', 'bonus', 'bonus_qty', 'bonus_name', 'bonus_konversi', 'point'];
        if($app_version == '1.1.1') {
            $arrayProduct      = $this->arraySelectProductOld();             
        } else {
            $arrayProduct      = $this->arraySelectProduct();             
        }

        try {
            if($app_version =='1.1') { 
                    $complaints         = $this->complaints->query();
                // $complaintDetails   = $this->complaintDetails->query();

                // search complaints
                if ($request->invoice) {
                    $complaints = $complaints
                                ->with(['complaint_detail', 'complaint_file', 'order.order_details.product'])
                                ->whereHas('order', function($query) use ($request)
                                {
                                    return $query->where('invoice', 'like', '%' . $request->invoice . '%');
                                })
                                ->where('user_id', $id);
                }

                // status no response yet
                if ($request->status == 'no response yet') {
                    // get user complaint no respond
                    $checkComplaintsNoResponse    = $complaints
                                                    ->where('user_id', $id)
                                                    ->where('responded', null)
                                                    ->count();
                }

                // status no response yet
                if ($request->status == 'response') {
                    // get user complaint no respond
                    $checkComplaintsResponse    = $complaints
                                                    ->where('user_id', $id)
                                                    ->where('responded', "true")
                                                    ->count();
                }            

                // filter complain confirmed by admiin
                if ($request->status == 'completed') {
                    $complaints = $complaints
                                ->with(['complaint_detail', 'complaint_file', 'order.order_details.product'])
                                ->where('user_id', $id)
                                ->where('status', 'confirmed')
                                ->orderBy('id', 'DESC');
                }

                // filter complain rejected by admiin
                if($request->status == 'rejected'){
                    $complaints = $complaints
                                    ->with(['complaint_detail', 'complaint_file', 'order.order_details.product'])
                                    ->where('user_id', $id)
                                    ->where('status', 'rejected')
                                    ->orderBy('id', 'DESC');
                }

                if ($request->invoice) {
                    // get search invoice
                    $complaints = $complaints->paginate(10);
                } elseif ($request->status == 'no response yet') {
                    // get status no response yet
                    if ($checkComplaintsNoResponse > 0) {
                        $complaints = $this->complaints
                                    ->with(['complaint_detail', 'complaint_file', 'order.order_details.product'])
                                    ->where('status', null)
                                    ->where('user_id', $id)
                                    ->where('responded', null)
                                    ->orderBy('id', 'DESC')
                                    ->paginate(10);

                    } else {
                        $complaints = $this->complaints
                                    ->with(['complaint_detail', 'complaint_file', 'order.order_details.product'])
                                    ->where('status', null)
                                    ->where('user_id', $id)
                                    ->where('responded', null)
                                    ->paginate(10);
                    }
                } elseif ($request->status == 'response') {
                    // get status no response yet
                    if ($checkComplaintsResponse > 0) {
                        $complaints = $this->complaints
                                    ->with(['complaint_detail', 'complaint_file', 'order.order_details.product'])
                                    ->where('status', null)
                                    ->where('user_id', $id)
                                    ->where('responded', "true")
                                    ->orderBy('id', 'DESC')
                                    ->paginate(10);
                    } else {
                        $complaints = $this->complaints
                                    ->with(['complaint_detail', 'complaint_file', 'order.order_details.product'])
                                    ->where('status', null)
                                    ->where('user_id', $id)
                                    ->where('responded', "true")
                                    ->orderBy('id', 'DESC')
                                    ->paginate(10);
                    }
                } elseif ($request->status == 'completed') {
                    // get status completed
                    $complaints = $complaints->paginate(10);
                } elseif ($request->status == 'rejected') {
                    // get status completed
                    $complaints = $complaints->paginate(10);
                } elseif ($request->status == 'havent complained') {
                    // get status haven't complained
                    $complaints = $this->order
                                            ->with(['data_item' => function($query) {
                                                $query->where('product_id', '!=', null)
                                                    ->where('product_review_id', null)
                                                    ->with('product');
                                            }])
                                            ->where(function($query) {
                                                $query->whereHas('data_unreview', function($q){$q;}, '>=', 1);
                                            })
                                            ->where('customer_id', $id)
                                            ->where('status', '4')
                                            ->where('complaint_id', null)
                                            ->orderBy('id', 'DESC')
                                            ->paginate(10);
                } else {
                    // get all complaints
                    $complaints = $this->complaints
                                            ->with(['complaint_detail', 'complaint_file', 'order.order_details.product'])
                                            ->where('user_id', $id)
                                            ->orderBy('id', 'DESC')
                                            ->paginate(10);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Get complaints successfully',
                    'data'    => $complaints
                ], 200);

            } else {
                $complaints         = $this->complaints->query();
                // $complaintDetails   = $this->complaintDetails->query();
    
                // search complaints
                if ($request->invoice) {
                    $complaints = $complaints
                                ->with(['complaint_detail' => function($query) use($array) {
                                    $query->leftjoin('complaint_files', 'complaint_files.complaint_detail_id', '=', 'complaint_details.id')
                                    ->select($array);
                                }, 'order.order_details.product'])
                                ->whereHas('order', function($query) use ($request)
                                {
                                    return $query->where('invoice', 'like', '%' . strtoupper($request->invoice) . '%');
                                })
                                ->where('user_id', $id);
                }
    
                // status no response yet
                if ($request->status == 'no response yet') {
                    // get user complaint no respond
                    $checkComplaintsNoResponse    = $complaints
                                                        ->where('user_id', $id)
                                                        ->where('responded', 'false')
                                                        ->count();
                }
    
                // status no response yet
                if ($request->status == 'response') {
                    // get user complaint no respond
                    $checkComplaintsResponse    = $complaints
                                                        ->where('user_id', $id)
                                                        ->where('responded', "true")
                                                        ->count();
                }            
    
                // filter complain confirmed by admiin
                if ($request->status == 'completed') {
                    $complaints = $complaints
                                        ->with(['complaint_detail' => function($query) use ($array) {
                                            $query->leftjoin('complaint_files', 'complaint_files.complaint_detail_id', '=', 'complaint_details.id')
                                            ->select($array);
                                        }, 'order' => function($query) use ($arrayOrder, $arrayOrderDetail, $arrayProduct) {
                                            $query->select($arrayOrder)
                                                ->with(['order_details' => function($quer) use ($arrayOrderDetail, $arrayProduct) {
                                                    $quer->select($arrayOrderDetail)
                                                        ->with(['product' => function($q) use ($arrayProduct) {
                                                            $q->select($arrayProduct);
                                                        }]);
                                                }]);
                                        }])
                                        ->where('user_id', $id)
                                        ->where('status', 'confirmed')
                                        ->orderBy('id', 'DESC');
                }
    
                // filter complain rejected by admiin
                if($request->status == 'rejected'){
                    $complaints = $complaints
                                    ->with(['complaint_detail' => function($query) use ($array) {
                                        $query->leftjoin('complaint_files', 'complaint_files.complaint_detail_id', '=', 'complaint_details.id')
                                        ->select($array);
                                    }, 'order' => function($query) use ($arrayOrder, $arrayOrderDetail, $arrayProduct) {
                                        $query->select($arrayOrder)
                                            ->with(['order_details' => function($quer) use ($arrayOrderDetail, $arrayProduct) {
                                                $quer->select($arrayOrderDetail)
                                                    ->with(['product' => function($q) use ($arrayProduct) {
                                                        $q->select($arrayProduct);
                                                    }]);
                                            }]);
                                    }])
                                    ->where('user_id', $id)
                                    ->where('status', 'rejected')
                                    ->orderBy('id', 'DESC');
                }
    
                if ($request->invoice) {
                    // get search invoice
                    $complaints = $complaints->paginate(10);
                } elseif ($request->status == 'no response yet') {
                    // get status no response yet
                    if ($checkComplaintsNoResponse > 0) {
                        $complaints = $this->complaints
                                                ->with(['complaint_detail' => function($query) use ($array) {
                                                    $query->leftjoin('complaint_files', 'complaint_files.complaint_detail_id', '=', 'complaint_details.id')
                                                    ->select($array);
                                                }, 'order' => function($query) use ($arrayOrder, $arrayOrderDetail, $arrayProduct) {
                                                    $query->select($arrayOrder)
                                                        ->with(['order_details' => function($quer) use ($arrayOrderDetail, $arrayProduct) {
                                                            $quer->select($arrayOrderDetail)
                                                                ->with(['product' => function($q) use ($arrayProduct) {
                                                                    $q->select($arrayProduct);
                                                                }]);
                                                        }]);
                                                }])
                                                ->where('status', null)
                                                ->where('user_id', $id)
                                                ->where('responded', 'false')
                                                ->orderBy('id', 'DESC')
                                                ->paginate(10);
    
                    } else {
                        $complaints = $this->complaints
                                                ->with(['complaint_detail' => function($query) use ($array) {
                                                    $query->leftjoin('complaint_files', 'complaint_files.complaint_detail_id', '=', 'complaint_details.id')
                                                    ->select($array);
                                                }, 'order' => function($query) use ($arrayOrder, $arrayOrderDetail, $arrayProduct) {
                                                    $query->select($arrayOrder)
                                                        ->with(['order_details' => function($quer) use ($arrayOrderDetail, $arrayProduct) {
                                                            $quer->select($arrayOrderDetail)
                                                                ->with(['product' => function($q) use ($arrayProduct) {
                                                                    $q->select($arrayProduct);
                                                                }]);
                                                        }]);
                                                }])
                                                ->where('status', null)
                                                ->where('user_id', $id)
                                                ->where('responded', 'false')
                                                ->paginate(10);
                    }
                } elseif ($request->status == 'response') {
                    // get status no response yet
                    if ($checkComplaintsResponse > 0) {
                        $complaints = $this->complaints
                                                ->with(['complaint_detail' => function($query) use ($array) {
                                                    $query->leftjoin('complaint_files', 'complaint_files.complaint_detail_id', '=', 'complaint_details.id')
                                                    ->select($array);
                                                }, 'order' => function($query) use ($arrayOrder, $arrayOrderDetail, $arrayProduct) {
                                                    $query->select($arrayOrder)
                                                        ->with(['order_details' => function($quer) use ($arrayOrderDetail, $arrayProduct) {
                                                            $quer->select($arrayOrderDetail)
                                                                ->with(['product' => function($q) use ($arrayProduct) {
                                                                    $q->select($arrayProduct);
                                                                }]);
                                                        }]);
                                                }])
                                                ->where('status', null)
                                                ->where('user_id', $id)
                                                ->where('responded', "true")
                                                ->orderBy('id', 'DESC')
                                                ->paginate(10);
                    } else {
                        $complaints = $this->complaints
                                                ->with(['complaint_detail' => function($query) use ($array) {
                                                    $query->leftjoin('complaint_files', 'complaint_files.complaint_detail_id', '=', 'complaint_details.id')
                                                    ->select($array);
                                                }, 'order' => function($query) use ($arrayOrder, $arrayOrderDetail, $arrayProduct) {
                                                    $query->select($arrayOrder)
                                                        ->with(['order_details' => function($quer) use ($arrayOrderDetail, $arrayProduct) {
                                                            $quer->select($arrayOrderDetail)
                                                                ->with(['product' => function($q) use ($arrayProduct) {
                                                                    $q->select($arrayProduct);
                                                                }]);
                                                        }]);
                                                }])
                                                ->where('status', null)
                                                ->where('user_id', $id)
                                                ->where('responded', "true")
                                                ->orderBy('id', 'DESC')
                                                ->paginate(10);
                    }
                } elseif ($request->status == 'completed') {
                    // get status completed
                    $complaints = $complaints->paginate(10);
                } elseif ($request->status == 'rejected') {
                    // get status completed
                    $complaints = $complaints->paginate(10);
                } elseif ($request->status == 'havent complained') {
                    // get status haven't complained
                    $complaints = $this->order
                                            ->with(['data_item' => function($query) use ($arrayProduct) {
                                                $query->where('product_id', '!=', null)
                                                    ->where('product_review_id', null)
                                                    ->with(['product' => function ($q) use ($arrayProduct) {
                                                        $q->select($arrayProduct);
                                                    }]);
                                            }])
                                            ->where(function($query) {
                                                $query->whereHas('data_unreview', function($q){$q;}, '>=', 1);
                                            })
                                            ->where('customer_id', $id)
                                            ->where('status', '4')
                                            ->where('complaint_id', null)
                                            ->whereNull('status_complaint')
                                            ->orderBy('id', 'DESC')
                                            ->select($arrayOrder)
                                            ->paginate(10);
                } else {
                    // get all complaints
                    $complaints = $this->complaints
                                            ->with(['complaint_detail' => function($query) use($array) {
                                                $query->leftjoin('complaint_files', 'complaint_files.complaint_detail_id', '=', 'complaint_details.id')
                                                        ->select($array);
                                            }, 'order' => function($query) use ($arrayOrder, $arrayOrderDetail, $arrayProduct) {
                                                $query->select($arrayOrder)
                                                    ->with(['order_details' => function($quer) use ($arrayOrderDetail, $arrayProduct) {
                                                        $quer->select($arrayOrderDetail)
                                                            ->with(['product' => function($q) use ($arrayProduct) {
                                                                $q->select($arrayProduct);
                                                            }]);
                                                    }]);
                                            }])
                                            ->where('user_id', $id)
                                            ->orderBy('id', 'DESC')
                                            ->paginate(10);
                }
    
                return response()->json([
                    'success' => true,
                    'message' => 'Get complaints successfully',
                    'data'    => $complaints
                ], 200);
            }
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get complaints failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function detail($id)
    {
        try {
            if (! JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data'    => null
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data'    => null
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data'    => null
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data'    => null
            ], 400);
        }

        try {
            $arrayOrder         = ['id', 'invoice', 'customer_id', 'subscribe_id', 'name', 'phone', 'address', 'kelurahan', 'kecamatan', 'kota', 'provinsi', 'payment_method', 'order_time', 'status', 'payment_total', 'payment_final', 'status_faktur', 'site_code', 'created_at', 'updated_at', 'delivery_service'];
            $arrayOrderDetail   = ['id', 'product_id', 'order_id', 'small_unit', 'konversi_sedang_ke_kecil', 'half', 'qty_konversi', 'qty', 'price_apps', 'total_price', 'product_review_id', 'promo_id', 'disc_cabang', 'rp_cabang', 'disc_principal', 'rp_principal', 'point_principal', 'bonus', 'bonus_qty', 'bonus_name', 'bonus_konversi', 'point'];
            $app_version        = auth()->user()->app_version;
            if($app_version == '1.1.1') {
                $arrayProduct      = $this->arraySelectProductOld();             
            } else {
                $arrayProduct      = $this->arraySelectProduct();             
            }
    
            $complaints = $this->complaints
                                    ->where('id', $id)
                                    ->with(['complaint_detail' => function($query) {
                                        $query->leftjoin('complaint_files', 'complaint_files.complaint_detail_id', '=', 'complaint_details.id')
                                                ->select(['complaint_details.*', 'complaint_files.file_1', 'complaint_files.file_2', 'complaint_files.file_3', 'complaint_files.file_4']);
                                    }, 'order' => function($query) use ($arrayOrder, $arrayOrderDetail, $arrayProduct) {
                                        $query->select($arrayOrder)
                                            ->with(['order_details' => function($quer) use ($arrayOrderDetail, $arrayProduct) {
                                                $quer->select($arrayOrderDetail)
                                                    ->with(['product' => function($q) use ($arrayProduct) {
                                                        $q->select($arrayProduct);
                                                    }]);
                                            }]);
                                    }])
                                    ->first();

            return response()->json([
                'success' => true,
                'message' => 'Get complaint details successfully',
                'data'    => $complaints
            ], 200);
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get detail complaint fails!',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            if (! JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data'    => null
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data'    => null
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data'    => null
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data'    => null
            ], 400);
        }

        // validation
        $validator = Validator::make(
            $request->all(),
            [
                'order_id'      => 'required',
                'title'         => 'required',
                'content'       => 'required',
                'brand_id'      => 'required',
                'file_1'        => 'mimes:jpeg,jpg,png,gif|max:1024',
                'file_2'        => 'mimes:jpeg,jpg,png,gif|max:1024',
                'file_3'        => 'mimes:jpeg,jpg,png,gif|max:1024',
                'file_4'        => 'mimes:mpeg,ogg,mp4,webm,3gp,mov,flv,avi,wmv,ts|max:10040',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success'   => false,
                'message'   => $validator->errors()->first(),
                'data'      => null
            ], 201);
        }

        // check user login
        $id = Auth::user()->id;

        try {
            // insert into complaints table
            $complaint = $this->complaints;

            $complaint->order_id    = $request->order_id;
            $complaint->user_id     = $id;
            $complaint->brand_id    = $request->brand_id;
            $complaint->qty         = $request->qty;
            $complaint->option      = $request->option;
            $complaint->half        = $request->half ? $request->half : NULL;
            $complaint->responded   = 'false';

            $complaint->save();

            // insert into complaint_detail table
            $complaintDetail = $this->complaintDetails;

            $complaintDetail->complaint_id  = $complaint->id;
            $complaintDetail->user_id       = $id;
            $complaintDetail->title         = $request->title;
            $complaintDetail->content       = $request->content;

            $complaintDetail->save();

            // insert into complaint_files table
            $complaintFile = $this->complaintFiles;

            $complaintFile->complaint_id        = $complaint->id;
            $complaintFile->complaint_detail_id = $complaintDetail->id;

            $relPathImage = '/images/complaint/';
            if (!file_exists(public_path($relPathImage))) {
                mkdir(public_path($relPathImage), 0755, true);
            }

            $relPathVideos = '/videos/complaint/';
            if (!file_exists(public_path($relPathVideos))) {
                mkdir(public_path($relPathVideos), 0755, true);
            }

            if ($request->hasFile('file_1')) {
                $file = $request->file('file_1');
                $ext = $file->getClientOriginalExtension();
    
                $newName = "complaint" . date('YmdHis') . "-1." . $ext;
    
                $image_resize = InterImage::make($file->getRealPath());
                $image_resize->save(('images/complaint/' . $newName));
                $complaintFile->file_1 = '/images/complaint/'.$newName;
            }

            if ($request->hasFile('file_2')) {
                $file = $request->file('file_2');
                $ext = $file->getClientOriginalExtension();
    
                $newName = "complaint" . date('YmdHis') . "-2." . $ext;
    
                $image_resize = InterImage::make($file->getRealPath());
                $image_resize->save(('images/complaint/' . $newName));
                $complaintFile->file_2 = '/images/complaint/'.$newName;
            }

            if ($request->hasFile('file_3')) {
                $file = $request->file('file_3');
                $ext = $file->getClientOriginalExtension();
    
                $newName = "complaint" . date('YmdHis') . "-3." . $ext;
    
                $image_resize = InterImage::make($file->getRealPath());
                $image_resize->save(('images/complaint/' . $newName));
                $complaintFile->file_3 = '/images/complaint/'.$newName;
            }
            
            if ($request->hasFile('file_4')) {
                $file = $request->file('file_4');
                $ext = $file->getClientOriginalExtension();
    
                $newName = "complaint" . date('YmdHis') . "-vid." . $ext;

                $path = public_path().'/videos/complaint/';
                $file->move($path, $newName);
                $complaintFile->file_4 = '/videos/complaint/'.$newName;
            }

            $complaintFile->save();

            // update complaint_id orders table
            $order = $this->order->find($request->order_id);

            $order->complaint_id        = $complaint->id;
            $order->status_complaint    = 1;
            $order->save();

            $complaintsContent = $this->complaints
                                    ->where('id', $complaint->id)
                                    ->with(['complaint_detail', 'complaint_file', 'order.order_details.product'])
                                    ->first();

            $logs   = $this->logs
                            ->create([
                                'log_time'      => Carbon::now(),
                                'activity'      => 'new complaint',
                                'table_id'      => $complaint->id,
                                'data_content'  => $complaintsContent,
                                'table_name'    => 'complaints, complaint_details, complaint_files, order',
                                'column_name'   => 'complaints.order_id, complaints.user_id, complaints.qty, complaints.option, complaint_details.complaint_id, complaint_details.user_id,  complaint_details.title, complaint_details.content, complaint_files.complaint_id, complaint_files.complaint_detail_id, complaint_files.files_1, complaint_files.files_2, complaint_files.files_3, complaint_files.files_4, orders.complaint_id',
                                'from_user'     => $id,
                                'to_user'       => null,
                                'platform'      => 'apps',
                            ]);

            return response()->json([
                'success' => true,
                'message' => 'Create complaints successfully',
                'data'    => $complaint
                                ->with('complaint_detail', 'complaint_file')
                                ->where('id', $complaint->id)
                                ->first()
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Create complaints failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function reply(Request $request, $id)
    {
        try {
            if (! JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data'    => null
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data'    => null
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data'    => null
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data'    => null
            ], 400);
        }

        // validation
        $validator = Validator::make(
            $request->all(),
            [
                'title'         => 'required',
                'content'       => 'required',
                'file_1'        => 'mimes:jpeg,jpg,png,gif|max:1024',
                'file_2'        => 'mimes:jpeg,jpg,png,gif|max:1024',
                'file_3'        => 'mimes:jpeg,jpg,png,gif|max:1024',
                'file_4'        => 'mimes:mpeg,ogg,mp4,webm,3gp,mov,flv,avi,wmv,ts|max:100040',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success'   => false,
                'message'   => $validator->errors()->first(),
                'data'      => null
            ], 201);
        }

        // check user login
        $user_id = Auth::user()->id;

        try {
            // update status responded
            $complaint = $this->complaints->findOrFail($id);
            $complaint->responded = 'false';
            $complaint->save();

            // insert into complaint_detail table
            $complaintDetail = $this->complaintDetails;

            $complaintDetail->complaint_id  = $id;
            $complaintDetail->user_id       = $user_id;
            $complaintDetail->title         = $request->title;
            $complaintDetail->content       = $request->content;

            $complaintDetail->save();

            // insert into complaint_files table
            $complaintFile = $this->complaintFiles;

            $complaintFile->complaint_id        = $id;
            $complaintFile->complaint_detail_id = $complaintDetail->id;

            $relPathImage = '/images/complaint/';
            if (!file_exists(public_path($relPathImage))) {
                mkdir(public_path($relPathImage), 0755, true);
            }

            $relPathVideo = '/videos/complaint/';
            if (!file_exists(public_path($relPathVideo))) {
                mkdir(public_path($relPathVideo), 0755, true);
            }

            if ($request->hasFile('file_1')) {
                $file = $request->file('file_1');
                $ext = $file->getClientOriginalExtension();
    
                $newName = "complaint" . date('YmdHis') . "." . $ext;
    
                $image_resize = InterImage::make($file->getRealPath());
                $image_resize->save(('images/complaint/' . $newName));
                $complaintFile->file_1 = '/images/complaint/'.$newName;
            }

            if ($request->hasFile('file_2')) {
                $file = $request->file('file_2');
                $ext = $file->getClientOriginalExtension();
    
                $newName = "complaint" . date('YmdHis') . "." . $ext;
    
                $image_resize = InterImage::make($file->getRealPath());
                $image_resize->save(('images/complaint/' . $newName));
                $complaintFile->file_2 = '/images/complaint/'.$newName;
            }

            if ($request->hasFile('file_3')) {
                $file = $request->file('file_3');
                $ext = $file->getClientOriginalExtension();
    
                $newName = "complaint" . date('YmdHis') . "." . $ext;
    
                $image_resize = InterImage::make($file->getRealPath());
                $image_resize->save(('images/complaint/' . $newName));
                $complaintFile->file_3 = '/images/complaint/'.$newName;
            }
            
            if ($request->hasFile('file_4')) {
                $file = $request->file('file_4');
                $ext = $file->getClientOriginalExtension();
    
                $newName = "complaint" . date('YmdHis') . "." . $ext;

                $path = public_path().'/videos/complaint/';
                $file->move($path, $newName);
                $complaintFile->file_4 = '/videos/complaint/'.$newName;
            }

            $complaintFile->save();

            $logs   = $this->logs
                                ->create([
                                    'log_time'      => Carbon::now(),
                                    'activity'      => 'new complaint repply from user',
                                    'table_id'      => $complaintDetail->id,
                                    'data_content'  => $complaintDetail,
                                    'table_name'    => 'complaint_details, complaint_files',
                                    'column_name'   => 'complaint_details.complaint_id, complaint_details.user_id, complaint_details.title, complaint_details.content, complaint_files.complaint_id, complaint_files.complaint_detail_id, complaint_files.files_1, complaint_files.files_2, complaint_files.files_3, complaint_files.files_4, orders.complaint_id',
                                    'from_user'     => $id,
                                    'to_user'       => null,
                                    'platform'      => 'apps',
                                ]);

            // for response
            $response   = $this->complaintDetails
                        ->with(['complaint.order_details.product', 'complaint_file'])
                        ->where('complaint_id', $id)
                        ->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Reply complaint successfully',
                'data'    => $response
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reply complaint failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function close($id)
    {
        try {
            if (! JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data'    => null
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data'    => null
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data'    => null
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data'    => null
            ], 400);
        }

        // check user login
        $user_id = Auth::user()->id;

        try {
            // update status complaint
            $complaint = $this->complaints->find($id);

            $complaint->status = '1';

            $complaint->save();

            // for response
            $response   = $this->complaints
                        ->with(['complaint_detail', 'complaint_file', 'order_details.product'])
                        ->where('id', $id)
                        ->where('user_id', $user_id)
                        ->first();

            return response()->json([
                'success' => true,
                'message' => 'Close complaint successfully',
                'data'    => $response
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Close complaint failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function notification(Request $request)
    {
        try {
            if (! JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data'    => null
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data'    => null
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data'    => null
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data'    => null
            ], 400);
        }

        try {
            $notifications = $this->logs->query();

            // check user login
            $id = Auth::user()->id;

            // get new order notifications
            if ($request->status == "seen") {
                $notifications  = $notifications
                                ->where('activity', 'seen complaint')
                                ->where('table_name', 'complaint_details')
                                ->where(function($query) use ($id) {
                                    $query->where('to_user', $id);
                                })
                                ->orderBy('log_time', 'DESC');

                // counting total
                $total  = $notifications
                        ->where('user_seen', null)
                        ->count();
            } 

            if ($request->status == "replied") {
                $notifications  = $notifications
                                ->where('activity', 'repply complaint')
                                ->where('table_name', 'complaints, complaint_details')
                                ->where(function($query) use ($id) {
                                    $query->where('to_user', $id);
                                })
                                ->orderBy('log_time', 'DESC');

                // counting total
                $total  = $notifications
                        ->where('user_seen', null)
                        ->count();
            } 

            if ($request->status == "confirm") {
                $notifications  = $notifications
                                ->where('activity', 'confirmed complaint')
                                ->where('table_name', 'complaints')
                                ->where(function($query) use ($id) {
                                    $query->where('to_user', $id);
                                })
                                ->orderBy('log_time', 'DESC');

                // counting total
                $total  = $notifications
                        ->where('user_seen', null)
                        ->count();
            } 

            if ($request->status == "reject") {
                $notifications  = $notifications
                                ->where('activity', 'rejected complaint')
                                ->where('table_name', 'complaints')
                                ->where(function($query) use ($id) {
                                    $query->where('to_user', $id);
                                })
                                ->orderBy('log_time', 'DESC');

                // counting total
                $total  = $notifications
                        ->where('user_seen', null)
                        ->count();
            } 

            if ($request->status == "sended stuff") {
                $notifications  = $notifications
                                ->where('activity', 'sended stuff complaint')
                                ->where('table_name', 'complaints')
                                ->where(function($query) use ($id) {
                                    $query->where('to_user', $id);
                                })
                                ->orderBy('log_time', 'DESC');

                // counting total
                $total  = $notifications
                        ->where('user_seen', null)
                        ->count();
            } 

            if ($request->status == "sended credit") {
                $notifications  = $notifications
                                ->where('activity', 'successfully sent credit')
                                ->where('table_name', 'complaints')
                                ->where(function($query) use ($id) {
                                    $query->where('to_user', $id);
                                })
                                ->orderBy('log_time', 'DESC');

                // counting total
                $total  = $notifications
                        ->where('user_seen', null)
                        ->count();
            } 

            if(!$request->status) { 
                $notifications  = $notifications
                                    ->select('logs.*')
                                    ->where('activity', 'not like', '%new complaint%')
                                    ->where('activity', 'not like', '%successfully sent credit%')
                                    ->where('activity', 'not like', '%successfully ordered complaint%')
                                    ->where('table_name', 'like', '%complaints%')
                                    ->where(function($query) use ($id) {                                    
                                        $query->where('to_user', $id);
                                    })
                                    ->orderBy('log_time', 'DESC');

                                // counting total
                $total  = $this->logs
                                    ->where('activity', 'not like', '%new complaint%')
                                    ->where('activity', 'not like', '%successfully sent credit%')
                                    ->where('activity', 'not like', '%successfully ordered complaint%')
                                    ->where('table_name', 'like', '%complaints%')
                                    ->where('user_seen', null)
                                    ->where('to_user', $id)
                                    ->count();

            }

            if ($request->order == 'asc') {
                $notifications   = $notifications->orderBy('log_time', 'asc');
            } else if ($request->order == 'desc') {
                $notifications = $notifications->orderBy('log_time', 'desc');
            } else {
                $notifications = $notifications->orderBy('log_time', 'desc');
            }

            $notifications = $notifications->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Get complaints notification successfully',
                'total'   => $total,
                'data'    => $notifications
            ], 200);
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get complaints notification failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function seenNotification($id)
    {
        try {
            if (! JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data'    => null
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data'    => null
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data'    => null
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data'    => null
            ], 400);
        }

        try {
            $notification = $this->logs->find($id);

            $notification->user_seen = '1';

            $notification->save();

            return response()->json([
                'success' => true,
                'message' => 'Seen complaint notification successfully',
                'data'    => $notification
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Seen comlaint notification failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }
}
