<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use App\User;
use App\MetaUser;
use App\Chat;
use App\MappingSite;

class LandingPageController extends Controller
{
    protected $users, $meta_users, $chats, $sites;

    public function __construct(User $users, MetaUser $meta_users, Chat $chats, MappingSite $sites)
    {
        $this->users = $users;
        $this->meta_users = $meta_users;
        $this->chats = $chats;
        $this->sites = $sites;
    }

    public function index()
    {
        // Distribution
        $distribution_array = [];
        $total_sites = $this->sites->count();

        $response = Http::get('http://site.muliaputramandiri.com/restapi/api/master_data/site', [
            'X-API-KEY' => config('erp.x_api_key'),
            'token'     => config('erp.token_api'),
        ])->json();
        $active_sites = count($response['data']);
        $distribution_array['total_titik'] = number_format($total_sites, 0, ',', '.');;
        $distribution_array['jumlah_distributor'] = number_format($active_sites, 0, ',', '.');

        // Outlets
        $users_array = [];
        $users_total = 0;
        $users = $this->users->select('salur_code', DB::raw('count(id) as total'))->whereIn('salur_code', ['RT', 'SO', 'SW', 'WS'])->groupBy('salur_code')->get();
        foreach($users as $user) {
            $users_array[$user->salur_code] = $user->total;
            $users_total += $user->total;
        }
        $users_array['RT'] = number_format($users_array['RT'], 0, ',', '.');
        $users_array['SO'] = number_format($users_array['SO'], 0, ',', '.');
        $users_array['SW'] = number_format($users_array['SW'], 0, ',', '.');
        $users_array['WS'] = number_format($users_array['WS'], 0, ',', '.');
        $users_array['total'] = number_format($users_total, 0, ',', '.');
        
        // Coverage
        $coverage_array = [];
        $salesmen   = $this->meta_users->select(DB::raw('count(salesman_code) as total'))->groupBy('salesman_code')->get();
        $chat           = $this->chats->select(DB::raw('count(id) as total'))->where('created_at', '>=', Carbon::now()->subDays(30))->first();
        $chat_average   = $chat->total / 30;
        $coverage_array['salesmen'] = number_format(count($salesmen), 0, ',', '.');
        $coverage_array['outlet']   = number_format(round($salesmen->avg('total')), 0, ',', '.');
        $coverage_array['chat']     = number_format(ceil($chat_average), 0, ',', '.');

        $list_data = [
            'distribution'  => $distribution_array,
            'outlets' => $users_array,
            'coverage' => $coverage_array,
        ];

        $data = [
            'success' => 'true',
            'message' => 'Data berhasil diambil',
            'data' => $list_data
        ];

        return response()->json($data);
    }
}
