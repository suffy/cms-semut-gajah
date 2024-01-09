<?php

namespace App\Http\Controllers\Member;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Log;
use App\User;
use Illuminate\Support\Facades\DB;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $log;

    public function __construct(Log $log)
    {
        $this->log = $log;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $data = Log::where('to_user', Auth::user()->id)
            ->orderBy('id','desc')
            ->paginate(30);

            foreach($data as $n){
                $n->user_seen = 1;
                $n->save();
            }

        foreach($data as $d):

            if($d->table_id!==null) {
                $detail = DB::select("Select * from " . $d->table_name . " where id='" . $d->table_id . "'");
                $data->raw_data = $detail;
            }

        endforeach;

        return view('public.member.notification')
            ->with('notification', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($data)
    {
        $log_time = date('Y-m-d H:i:s');
        $activity = $data['activity'];
        $table_name = $data['table_name'];
        $table_id = $data['table_id'];
        $from_user = $data['from_user'];
        $to_user = $data['to_user'];

        $log = Log::create([
            'log_time' => $log_time,
            'activity' => $activity,
            'table_name' => $table_name,
            'table_id' => $table_id,
            'from_user' => $from_user,
            'to_user' => $to_user,
            'user_seen' => 0,
            'admin_seen' => 0,
            'status' => 1
        ]);

        if ($log->from_user == null) {
            $user = User::find($log->to_user);
        } else if ($log->to_user == null) {
            $user = User::find($log->from_user);
        }

        // FCM Notification builder
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60*20);

        $notificationBuilder = new PayloadNotificationBuilder();
        $activity = $log->activity;
        $notificationBuilder
            ->setTitle('Iklanqu.com')
            ->setBody("#".$table_id." - ".ucwords($activity));

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData(['a_data' => 'my_data']);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

        // FCM Notification
        $member = User::where('account_type', '1')
                            ->where('fcm_token','!=', null)
                            ->get();
        $member_token = array();
        foreach($member as $mbr):
            $member_token[] = $mbr->fcm_token;
        endforeach;

        if(count($member_token)>0){
            FCM::sendTo($member_token, $option, $notification, $data);
        }



        // Return response
        $status = 200;
        $msg = "send notification success";

        return response()->json(compact('status','msg','log'), 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    function getNotification(){
        $data = $this->log->where('user_seen', '0')
            ->where('to_user',Auth::user()->id)
            ->orderBy('id','desc')
            ->get();

        foreach($data as $d):

            if($d->table_name!==null) {
                $detail = DB::select("Select * from " . $d->table_name . " where id='" . $d->table_id . "'");
                $data->raw_data = $detail;
            }
        endforeach;

        return view('public/includes/member-notification')
            ->with('notification', $data);
    }

    function openNotification($id){

        $data = $this->log->find($id);

        if($data){
            $data->user_seen = "1";
            $data->save();
            if($data->table_name=="transactions"){
                return redirect(url('member/transactions-detail/'.$data->table_id));
            }
        }
    }
}
