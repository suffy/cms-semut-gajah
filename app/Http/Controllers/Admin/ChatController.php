<?php

namespace App\Http\Controllers\Admin;

use App\Chat;
use App\Http\Controllers\Controller;
use App\Log;
use App\User;
use App\MappingSite;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

class ChatController extends Controller
{
    protected $user, $chat, $logs, $mappingSite;

    public function __construct(User $user, Chat $chat, Log $log, MappingSite $mappingSite)
    {
        $this->user = $user;
        $this->chat = $chat;
        $this->logs = $log;
        $this->mappingSite = $mappingSite;
    }

    public function index()
    {
        // check user login
        $user           = $this->user->find(auth()->id());
        // $manager        = $this->user->where('account_role', 'manager')->first();
        // $superadmin     = $this->user->where('account_role', 'superadmin')->first();
        // $admin          = $this->user->where('account_role', 'admin')->first();
        
        if ($user->account_role == 'manager' || $user->account_role == 'superadmin' || $user->account_role == 'admin') {
            // $lists = $this->chat
            //             ->select('chats.chat_id as chat_id', 'users.name as name', 'users.photo as photo', 'chats.created_at as sended_at', 'chats.status as status')
            //             ->join('users', 'users.id', '=', 'chats.from_id')
            //             ->where('chats.to_id', $manager->id)
            //             ->orWhere('chats.to_id', $superadmin->id)
            //             ->orWhere('chats.to_id', $admin->id)
            //             ->orderBy('chats.id', 'desc')
            //             ->groupBy('chats.from_id', 'chats.id', 'users.name', 'users.photo')
            //             ->paginate(10);

            $list_chat = $this->chat->distinct()->get(['chat_id']);
            $lists = array();
            foreach($list_chat as $row) {
                $data = $this->chat
                            ->select('chats.chat_id as chat_id', 'users.name as name', 'users.photo as photo', 'chats.created_at as sended_at', 'chats.status as status')
                            ->join('users', 'users.id', '=', 'chats.from_id')
                            ->where('chats.chat_id', $row->chat_id)
                            // ->where('chats.to_id', $manager->id)
                            // ->orWhere('chats.to_id', $superadmin->id)
                            // ->orWhere('chats.to_id', $admin->id)
                            ->orderBy('chats.chat_id', 'asc')
                            ->first()
                            ->toArray();
                            array_push($lists, $data);
            }

            return view('admin.pages.chats', compact('lists'));
        } else if ($user->account_role == 'distributor' || $user->account_role == 'distributor_ho') {
            if($user->account_role == 'distributor') {
                $list_chat = $this->chat->where('to_id', $user->id)->distinct()->get(['chat_id']);
            } else if($user->account_role == 'distributor_ho') {
                $sites = $this->mappingSite->where('kode', Auth::user()->site_code)->with(['ho_child' => function($q) {
                    $q->select('kode', 'sub');
                }])->first();
                
                $array_child = [];
                foreach($sites->ho_child as $child) {
                    array_push($array_child, $child->kode);
                }
                
                $distributor_id = $this->user->where('account_role', 'distributor')->whereIn('site_code', $array_child)->get(['id']);
                $array_id = [];
                foreach($distributor_id as $distri) {
                    array_push($array_id, $distri->id);
                }
                $list_chat = $this->chat->whereIn('to_id', $array_id)->distinct()->get(['chat_id']);
            }
            $lists = array();
            foreach($list_chat as $row) {
                $data = $this->chat
                            ->select('chats.chat_id as chat_id', 'users.name as name', 'users.photo as photo', 'chats.created_at as sended_at', 'chats.status as status')
                            ->join('users', 'users.id', '=', 'chats.from_id')
                            ->where('chats.chat_id', $row->chat_id)
                            ->where('chats.to_id', $user->id)
                            ->orderBy('chats.chat_id', 'asc')
                            ->first()
                            ->toArray();
                
                            array_push($lists, $data);
            }

            return view('admin.pages.chats', compact('lists'));
        }
        
        // if ($user->account_role == 'user') {
        //     $lists = $this->chat->select('chats.chat_id as chat_id', 'users.name as name', 'users.photo as photo', 'chats.created_at as sended_at', 'chats.status as status')
        //                 ->join('users', 'users.id', '=', 'chats.to_id')
        //                 ->where('chats.from_id', $user->id)
        //                 ->orWhere('chats.to_id', $user->id)
        //                 ->orderBy('chats.id', 'asc')
        //                 ->groupBy('chats.chat_id')
        //                 ->paginate(10);

        //     return view('admin.pages.chats', compact('lists'));
        // }
    }

    public function getChats()
    {
        // check user login
        $user = $this->user->find(auth()->id());
        $manager = $this->user->where('account_role', 'manager')->first();
        $superadmin = $this->user->where('account_role', 'superadmin')->first();
        $admin = $this->user->where('account_role', 'admin')->first();
        
        if ($user->account_role == 'manager' || $user->account_role == 'superadmin' || $user->account_role == 'admin') {
            // $lists = $this->chat
            //             ->select('chats.chat_id as chat_id', 'users.name as name', 'users.photo as photo', 'chats.created_at as sended_at', 'chats.status as status')
            //             ->join('users', 'users.id', '=', 'chats.from_id')
            //             ->where('chats.to_id', $manager->id)
            //             ->orWhere('chats.to_id', $superadmin->id)
            //             ->orWhere('chats.to_id', $admin->id)
            //             ->orderBy('chats.id', 'desc')
            //             ->groupBy('chats.from_id', 'chats.id', 'users.name', 'users.photo')
            //             ->paginate(10);
                        
            $list_chat = $this->chat->distinct()->get(['chat_id']);
            $lists = array();
            foreach($list_chat as $row) {
                $data = $this->chat
                            ->select('chats.chat_id as chat_id', 'users.name as name', 'users.photo as photo', 'chats.created_at as sended_at', 'chats.status as status')
                            ->join('users', 'users.id', '=', 'chats.from_id')
                            ->where('chats.chat_id', $row->chat_id)
                            // ->where('chats.to_id', $manager->id)
                            // ->orWhere('chats.to_id', $superadmin->id)
                            // ->orWhere('chats.to_id', $admin->id)
                            ->orderBy('chats.chat_id', 'asc')
                            ->first()
                            ->toArray();
                
                            array_push($lists, $data);
            }

            $response = array(
                'status' => 1,
                'message' => 'Success',
                'data' => $lists
            );
    
            return $response;
            // return view('admin.pages.chats', compact('lists'));
        } else if ($user->account_role == 'distributor') {
            $list_chat = $this->chat->where('to_id', $user->id)->distinct()->get(['chat_id']);
            $lists = array();
            foreach($list_chat as $row) {
                $data = $this->chat
                            ->select('chats.chat_id as chat_id', 'users.name as name', 'users.photo as photo', 'chats.created_at as sended_at', 'chats.status as status')
                            ->join('users', 'users.id', '=', 'chats.from_id')
                            ->where('chats.chat_id', $row->chat_id)
                            ->where('chats.to_id', $user->id)
                            ->orderBy('chats.chat_id', 'asc')
                            ->first()
                            ->toArray();
                
                            array_push($lists, $data);
            }

            $response = array(
                'status' => 1,
                'message' => 'Success',
                'data' => $lists
            );
    
            return $response;
        }
        
        if ($user->account_role == 'user') {
            $lists = $this->chat->select('chats.chat_id as chat_id', 'users.name as name', 'users.photo as photo', 'chats.created_at as sended_at', 'chats.status as status')
                        ->join('users', 'users.id', '=', 'chats.to_id')
                        ->where('chats.from_id', $user->id)
                        ->orWhere('chats.to_id', $user->id)
                        ->orderBy('chats.id', 'asc')
                        ->groupBy('chats.chat_id')
                        ->paginate(10);

            $response = array(
                'status' => 1,
                'message' => 'Success',
                'data' => $lists
            );
    
            return $response;
            // return view('admin.pages.chats', compact('lists'));
        }
    }

    public function getMessages($chatId)
    {
        // check user login
        $user = $this->user->find(auth()->id());

        if ($user->account_role == 'manager' || $user->account_role == 'superadmin' || $user->account_role == 'admin') {
            $messages = User::select('chats.id as id', 'users.name as name', 'chats.from_id as from_id', 'chats.to_id as to_id', 'chats.message as message', 'chats.created_at as sended_at')
                        ->join('chats', 'chats.from_id', '=', 'users.id')
                        ->where('chats.chat_id', $chatId)
                        ->orderBy('chats.id', 'asc')
                        ->get();

            foreach ($messages as $message) {
                $message    = $this->chat
                            ->where('id', $message->id)
                            ->where('to_id', $user->id)
                            ->update([
                                'status' => '1'
                            ]);
            }
            
            $response = array(
                'status' => 1,
                'message' => 'Success',
                'data' => $messages
            );
    
            return $response;
        } else if ($user->account_role == 'distributor') {
            $messages = User::select('chats.id as id', 'users.name as name', 'chats.from_id as from_id', 'chats.to_id as to_id', 'chats.message as message', 'chats.created_at as sended_at')
                        ->join('chats', 'chats.from_id', '=', 'users.id')
                        ->where('chats.chat_id', $chatId)
                        ->orderBy('chats.id', 'asc')
                        ->get();

            foreach ($messages as $message) {
                $message    = $this->chat
                            ->where('id', $message->id)
                            ->where('to_id', $user->id)
                            ->update([
                                'status' => '1'
                            ]);
            }
            
            $response = array(
                'status' => 1,
                'message' => 'Success',
                'data' => $messages
            );
    
            return $response;
        }

        if ($user->account_role == 'user') {
            $messages = User::select('chats.id as id', 'users.name as name', 'chats.from_id as from_id', 'chats.to_id as to_id', 'chats.message as message', 'chats.created_at as sended_at')
                        ->join('chats', 'chats.from_id', '=', 'users.id')
                        ->where('chats.chat_id', $chatId)
                        ->get();

            foreach ($messages as $message) {
                $message = $this->chat->find($message->id);

                $message->status = '1';

                $message->save();
            }
            
            $response = array(
                'status' => 1,
                'message' => 'Success',
                'data' => $messages
            );
    
            return $response;
        }
    }

    public function sendMessageUser(Request $request)
    {
        // check user login
        $user = $this->user->find(auth()->id());

        $id = $this->user->where('account_role', 'manager')->value('id');
        $firstMessage = $this->chat->where('from_id', $user->id)->orWhere('from_id', $user->id)->get();

        if (count($firstMessage) == 0) {
            $message = $this->chat->create([
                        'chat_id'   => Str::uuid()->toString(),
                        'from_id'   => $user->id, 
                        'to_id'     => $id,
                        'message'   => $request->message
                    ]);

            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "Send message : " . $request->message;
            $logs->data_content = "from_id : " . $user->id . ", to_id : " . $id . ", message : " . $request->message;
            $logs->table_name   = 'chats';
            $logs->column_name  = 'chat_id, from_id, to_id, message';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = $id;
            $logs->platform     = 'web';

            $logs->save();
            
            $this->broadcastMessage(auth()->user()->name, $user->id, $request->message, Carbon::now());

            $response = array(
                'status' => 1,
                'message' => 'Success',
                'data' => $message
            );

            return $response;
        } else {
            $uuid = $this->chat->where('from_id', $user->id)->orWhere('from_id', $user->id)->value('chat_id');
            $message = $this->chat->create([
                        'chat_id'   => $uuid,
                        'from_id'   => $user->id, 
                        'to_id'     => $id,
                        'message'   => $request->message
                    ]);

            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "Send message : " . $request->message;
            $logs->data_content = "from_id : " . $user->id . ", to_id : " . $id . ", message : " . $request->message;
            $logs->table_name   = 'chats';
            $logs->column_name  = 'chat_id, from_id, to_id, message';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = $id;
            $logs->platform     = 'web';

            $logs->save();

            $this->broadcastMessage(auth()->user()->name, $user->id, $id, $request->message, Carbon::now());
    
            $response = array(
                'status' => 1,
                'message' => 'Success',
                'data' => $message
            );

            return $response;
        }
    }

    public function sendMessageAdmin(Request $request, $chatId)
    {
        // check user login
        $user = $this->user->find(auth()->id());

        $messageSelect = $this->chat->where('chat_id', $chatId)->orderBy('id', 'asc')->first();
        
        $message = $this->chat->create([
                    'chat_id'   => $chatId,
                    'from_id'   => $user->id, 
                    'to_id'     => $messageSelect->from_id,
                    'message'   => $request->message
                ]);

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Send message : " . $request->message;
        $logs->data_content = "from_id : " . $user->id . ", to_id : " . $messageSelect->from_id . ", message : " . $request->message;
        $logs->table_name   = 'chats';
        $logs->column_name  = 'chat_id, from_id, to_id, message';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = $messageSelect->from_id;
        $logs->platform     = 'web';

        $logs->save();
        
        $this->broadcastMessage(auth()->user()->name, $user->id, $messageSelect->from_id, $request->message, Carbon::now());
        
        $response = array(
            'status' => 1,
            'message' => 'Success',
            'data' => $message
        );

        return $response;
    }

    public function broadcastMessage($name, $fromId, $toId, $message, $sendedAt)
    {
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60 * 20);

        $notificationBuilder = new PayloadNotificationBuilder('New message from : ' . $name);
        $notificationBuilder->setBody($message)
            ->setSound('default');
            // ->setClickAction('http://localhost:8000/admin/chats');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData([
            'name'          => $name,
            'from_id'       => $fromId,
            'message'       => $message,
            'created_at'    => $sendedAt,
        ]);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

        // to multiple device
        // $tokens = User::all()->pluck('fcm_token')->toArray();

        // to multiple device
        // $tokens = $this->user->where('id', $fromId)->orWhere('id', $toId)->pluck('fcm_token')->toArray();
        $tokens = $this->user->where('id', $fromId)->pluck('fcm_token')->toArray();

        $downstreamResponse = FCM::sendTo($tokens, $option, $notification, $data);

        $fcm_token  = $this->user->where('id', $toId)->pluck('fcm_token')->all();

        $this->sendNotification($fcm_token);
        return $downstreamResponse->numberSuccess();
    }

    public function sendNotification($fcm_token)
    {
        $SERVER_API_KEY = config('firebase.server_api_key');                        // get server_api_key from config

        $data = [
            "registration_ids" => $fcm_token,
            "notification" => [
                "title" => 'Pesan Dari Admin',
                "body" => 'Pesan kamu telah dibalas oleh admin, silahkan buka apps Semut Gajah.',  
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
