<?php

namespace App\Http\Controllers\Api;

use App\Chat;
use App\Http\Controllers\Controller;
use App\Log;
use App\User;
use App\MappingSite;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use LaravelFCM\Facades\FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ChatController extends Controller
{
    protected $users, $chats, $logs;

    public function __construct(Chat $chat, User $user, Log $log, MappingSite $mappingSite)
    {
        $this->users = $user;
        $this->chats = $chat;
        $this->logs = $log;
        $this->mappingSite = $mappingSite;
    }

    public function get($chatId)
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
        $user = Auth::user();

        if ($user->account_role == 'manager' || $user->account_role == 'superadmin' || $user->account_role == 'admin') {
            try {
                $messages   = $this->users
                        ->select('chats.id as id', 'users.name as name', 'chats.from_id as from_id', 'chats.to_id as to_id', 'chats.message as message', 'chats.created_at as sended_at')
                        ->join('chats', 'chats.from_id', '=', 'users.id')
                        ->where('chats.chat_id', $chatId)
                        ->get();

                foreach ($messages as $message) {
                    $message    = $this->chat
                                ->where('id', $message->id)
                                ->where('to_id', $user->id)
                                ->update([
                                    'status' => '1'
                                ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Get messages successfully',
                    'data'    => $messages
                ], 200);
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Get messages failed',
                    'data'    => $e->getMessage()
                ], 500);
            }
        } 

        if ($user->account_role == 'user') {
            try {
                $messages = User::select('chats.id as id', 'users.name as name', 'chats.from_id as from_id', 'chats.to_id as to_id', 'chats.message as message', 'chats.created_at as sended_at')
                        ->join('chats', 'chats.from_id', '=', 'users.id')
                        ->where('chats.chat_id', $chatId)
                        ->get();

                foreach ($messages as $message) {
                    $message = $this->chats->find($message->id);

                    $message->status = '1';

                    $message->save();
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Get messages successfully',
                    'data'    => $messages
                ], 200);
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Get messages failed',
                    'data'    => $e->getMessage()
                ], 500);
            }
        }
    }

    public function post(Request $request)
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
        $id         = Auth::user()->id;
        $site_code  = Auth::user()->site_code;

        // $managerID      = $this->users
        //                             ->where('account_role', 'manager')
        //                             ->value('id');
        $distributorID      = $this->users
                                        ->where('account_role', 'distributor')
                                        ->where('site_code', $site_code)
                                        ->value('id');

        $distributor        = $this->mappingSite->where('kode', $site_code)->first();

        $firstMessage   = $this->chats->where('from_id', $id)->orWhere('from_id', $id)->get();
        
        if (count($firstMessage) == 0) {
            try {
                $message                = $this->chats;

                $message->chat_id       = Str::uuid()->toString();
                $message->from_id       = $id;
                $message->to_id         = $distributorID;
                $message->message       = $request->message;

                $message->save();

                // logs
                $logs = $this->logs;

                $logs->log_time     = Carbon::now();
                $logs->activity     = "Send message from user with id : " . $id;
                $logs->data_content = $message;
                $logs->table_name   = 'chats';
                $logs->column_name  = 'chat_id, from_id, to_id, message';
                $logs->from_user    = auth()->user()->id;
                $logs->to_user      = $distributorID;
                $logs->platform     = "apps";

                $logs->save();
                
                $this->broadcastMessage(auth()->user()->name, $id, $distributorID, $request->message, Carbon::now());
                
                // send wa
                if($distributor->telp_wa != null) {
                    $userkey = config('zenziva.USER_KEY_ZENZIVA');
                    $passkey = config('zenziva.API_KEY_ZENZIVA');
                    $telepon = $distributor->telp_wa;
                    $message_send = 
                            "Semut Gajah Official \r\n" . 
                            "Pemberitahuan \r\n \r\n" .
                            
                            "Halo Distributor " .  $distributor->kode . ", \r\n".
                            "Terdapat pesan baru dari user. \r\n \r\n" .  
                            "Balas pesan user melalui website Semut Gajah dibawah \r\n" . "https://production.semutgajah.com";
                    $url = 'https://console.zenziva.net/wareguler/api/sendWA/';
                    $curlHandle = curl_init();
                    curl_setopt($curlHandle, CURLOPT_URL, $url);
                    curl_setopt($curlHandle, CURLOPT_HEADER, 0);
                    curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
                    curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
                    curl_setopt($curlHandle, CURLOPT_POST, 1);
                    curl_setopt($curlHandle, CURLOPT_POSTFIELDS, array(
                        'userkey'   => $userkey,
                        'passkey'   => $passkey,
                        'to'        => $telepon,
                        'message'   => $message_send
                    ));
                    $results = json_decode(curl_exec($curlHandle), true);
                    curl_close($curlHandle);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Send message successfully',
                    'data'    => $message
                ], 200);
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Send message failed',
                    'data'    => $e->getMessage()
                ], 500);
            }
        } else {
            try {
                $uuid = $this->chats->where('from_id', $id)->orWhere('from_id', $id)->value('chat_id');

                $message            = $this->chats;

                $message->chat_id   = $uuid;
                $message->from_id   = $id;
                $message->to_id     = $distributorID;
                $message->message   = $request->message;

                $message->save();

                // logs
                $logs = $this->logs;

                $logs->log_time     = Carbon::now();
                $logs->activity     = "Send message from user with id : " . $id;
                $logs->data_content = $message;
                $logs->table_name   = 'chats';
                $logs->column_name  = 'chat_id, from_id, to_id, message';
                $logs->from_user    = auth()->user()->id;
                $logs->to_user      = $distributorID;
                $logs->platform     = "apps";

                $logs->save();

                $this->broadcastMessage(auth()->user()->name, $id, $distributorID, $request->message, Carbon::now());
                
                // send wa
                if($distributor->telp_wa != null) {
                    $userkey = config('zenziva.USER_KEY_ZENZIVA');
                    $passkey = config('zenziva.API_KEY_ZENZIVA');
                    $telepon = $distributor->telp_wa;
                    $message_send = 
                            "Semut Gajah Official \r\n" . 
                            "Pemberitahuan \r\n \r\n" .
                            
                            "Halo Distributor " .  $distributor->kode . ", \r\n".
                            "Terdapat pesan baru dari user. \r\n \r\n" .  
                            "Balas pesan user melalui website Semut Gajah dibawah \r\n" . "https://production.semutgajah.com";
                    $url = 'https://console.zenziva.net/wareguler/api/sendWA/';
                    $curlHandle = curl_init();
                    curl_setopt($curlHandle, CURLOPT_URL, $url);
                    curl_setopt($curlHandle, CURLOPT_HEADER, 0);
                    curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
                    curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
                    curl_setopt($curlHandle, CURLOPT_POST, 1);
                    curl_setopt($curlHandle, CURLOPT_POSTFIELDS, array(
                        'userkey'   => $userkey,
                        'passkey'   => $passkey,
                        'to'        => $telepon,
                        'message'   => $message_send
                    ));
                    $results = json_decode(curl_exec($curlHandle), true);
                    curl_close($curlHandle);
                }
        
                return response()->json([
                    'success' => true,
                    'message' => 'Send message successfully',
                    'data'    => $message
                ], 200);
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Send message failed',
                    'data'    => $e->getMessage()
                ], 500);
            }
        }
    }

    public function unread()
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
        $id = Auth::user()->id;

        try {
            $unread = $this->chats
                    ->where('to_id', $id)
                    ->where('status', null)
                    ->get();

            return response()->json([
                'success' => true,
                'message' => 'Count unread messages successfully',
                'data'    => count($unread)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Count unread messages failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function getChat()
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
        $user = Auth::user();

        try {
            // check user login
            $manager = $this->users->where('account_role', 'manager')->first();
            $superadmin = $this->users->where('account_role', 'superadmin')->first();
            $admin = $this->users->where('account_role', 'admin')->first();
            
            if ($user->account_role == 'manager' || $user->account_role == 'superadmin' || $user->account_role == 'admin') {
                $lists = $this->chats->select('chats.chat_id as chat_id', 'users.name as name', 'users.last_login as last_login', 'users.photo as photo', 'chats.created_at as sended_at')
                            ->join('users', 'users.id', '=', 'chats.from_id')
                            ->where('chats.to_id', $manager->id)
                            ->orWhere('chats.to_id', $superadmin->id)
                            ->orWhere('chats.to_id', $admin->id)
                            ->orderBy('chats.id', 'desc')
                            ->groupBy('chats.from_id', 'chats.chat_id', 'users.name', 'users.photo')
                            ->paginate(10);
            } 
            
            if ($user->account_role == 'user') {
                $lists = $this->chats->select('chats.chat_id as chat_id', 'users.name as name', 'users.last_login as last_login', 'users.photo as photo', 'chats.created_at as sended_at')
                            ->join('users', 'users.id', '=', 'chats.to_id')
                            ->where('chats.from_id', $user->id)
                            ->orWhere('chats.to_id', $user->id)
                            ->orderBy('chats.id', 'asc')
                            ->groupBy('chats.chat_id', 'chats.id', 'users.name', 'users.last_login', 'users.photo', 'chats.created_at')
                            ->paginate(10);
            }

            return response()->json([
                'success' => true,
                'message' => 'Get chats successfully',
                'data'    => $lists
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get chats failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function notification()
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
        $id = Auth::user()->id;

        $notifications  = $this->chats
                        ->with('to_user')
                        ->where('to_id', $id)
                        ->where('status', null);

        $notifications = $notifications->get();

        // counting total
        $total = $notifications->count();

        try {
            return response()->json([
                'success' => true,
                'message' => 'Get chat notifications successfully',
                'total'   => $total,
                'data'    => $notifications
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get chat notifications failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function seenNotification($chatId)
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
        $id = Auth::user()->id;

        $notifications  = $this->chats
                        ->where('to_id', $id)
                        ->where('chat_id', $chatId)
                        ->update([
                            'status' => '1'
                        ]);

        try {
            return response()->json([
                'success' => true,
                'message' => 'Get chat notifications successfully',
                'data'    => $notifications
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get chat notifications failed',
                'data'    => $e->getMessage()
            ], 500);
        }
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
            'to_id'         => $toId,
            'message'       => $message,
            'created_at'    => $sendedAt,
        ]);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

        // to multiple device
        // $tokens = User::all()->pluck('fcm_token')->toArray();

        // to multiple device
        // $tokens = $this->users
        //         ->where('id', $fromId)
        //         ->orWhere('id', $toId)
        //         ->pluck('fcm_token')
        //         ->toArray();

        // to a device
        $tokens = $this->users->where('id', $fromId)->value('fcm_token');

        $downstreamResponse = FCM::sendTo($tokens, $option, $notification, $data);

        return $downstreamResponse->numberSuccess();

    }
}
