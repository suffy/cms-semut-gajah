<?php

namespace App\Http\Controllers;

use App\Contact;
use App\DataOption;
use App\Message;
use App\StatCounter;
use App\TbKecamatan;
use App\TbKota;
use App\TbProvinsi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;

class HomeController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function sendMessage(Request $request){

        $url = $request->input('url');

        if($request->input('additional_info')==''){

            if($request->input('name')==null){

                return redirect($url)
                            ->with('status', 2)
                            ->with('message', "Name is Empty!");

            }else if($request->input('email')==null){

                return redirect($url)
                            ->with('status', 2)
                            ->with('message', "Email is Empty!");

            }else if($request->input('subject')==null){

                return redirect($url)
                            ->with('status', 2)
                            ->with('message', "Subject is Empty!");

            }else if($request->input('message')==null){

                return redirect($url)
                            ->with('status', 2)
                            ->with('message', "Message is Empty!");

            }else{

                if(strlen($request->input('message'))<5 || strlen($request->input('message'))>300){

                    return redirect($url)
                            ->with('status', 2)
                            ->with('message', "Opps sorry, your message is too long!");

                }else{

                    $badwords = array("4r5e", "5h1t", "5hit", "a55", "anal", "anus", "ar5e", "arrse", "arse", "ass", "ass-fucker", "asses", "assfucker", "assfukka", "asshole", "assholes", "asswhole", "a_s_s", "b!tch", "b00bs", "b17ch", "b1tch", "ballbag", "balls", "ballsack", "bastard", "beastial", "beastiality", "bellend", "bestial", "bestiality", "bi+ch", "biatch", "bitch", "bitcher", "bitchers", "bitches", "bitchin", "bitching", "bloody", "blow job", "blowjob", "blowjobs", "boiolas", "bollock", "bollok", "boner", "boob", "boobs", "booobs", "boooobs", "booooobs", "booooooobs", "breasts", "buceta", "bugger", "bum", "bunny fucker", "butt", "butthole", "buttmuch", "buttplug", "c0ck", "c0cksucker", "carpet muncher", "cawk", "chink", "cipa", "cl1t", "clit", "clitoris", "clits", "cnut", "cock", "cock-sucker", "cockface", "cockhead", "cockmunch", "cockmuncher", "cocks", "cocksuck", "cocksucked", "cocksucker", "cocksucking", "cocksucks", "cocksuka", "cocksukka", "cok", "cokmuncher", "coksucka", "coon", "cox", "crap", "cum", "cummer", "cumming", "cums", "cumshot", "cunilingus", "cunillingus", "cunnilingus", "cunt", "cuntlick", "cuntlicker", "cuntlicking", "cunts", "cyalis", "cyberfuc", "cyberfuck", "cyberfucked", "cyberfucker", "cyberfuckers", "cyberfucking", "d1ck", "damn", "dick", "dickhead", "dildo", "dildos", "dink", "dinks", "dirsa", "dlck", "dog-fucker", "doggin", "dogging", "donkeyribber", "doosh", "duche", "dyke", "ejaculate", "ejaculated", "ejaculates", "ejaculating", "ejaculatings", "ejaculation", "ejakulate", "f u c k", "f u c k e r", "f4nny", "fag", "fagging", "faggitt", "faggot", "faggs", "fagot", "fagots", "fags", "fanny", "fannyflaps", "fannyfucker", "fanyy", "fatass", "fcuk", "fcuker", "fcuking", "feck", "fecker", "felching", "fellate", "fellatio", "fingerfuck", "fingerfucked", "fingerfucker", "fingerfuckers", "fingerfucking", "fingerfucks", "fistfuck", "fistfucked", "fistfucker", "fistfuckers", "fistfucking", "fistfuckings", "fistfucks", "flange", "fook", "fooker", "fuck", "fucka", "fucked", "fucker", "fuckers", "fuckhead", "fuckheads", "fuckin", "fucking", "fuckings", "fuckingshitmotherfucker", "fuckme", "fucks", "fuckwhit", "fuckwit", "fudge packer", "fudgepacker", "fuk", "fuker", "fukker", "fukkin", "fuks", "fukwhit", "fukwit", "fux", "fux0r", "f_u_c_k", "gangbang", "gangbanged", "gangbangs", "gaylord", "gaysex", "goatse", "God", "god-dam", "god-damned", "goddamn", "goddamned", "hardcoresex", "hell", "heshe", "hoar", "hoare", "hoer", "homo", "hore", "horniest", "horny", "hotsex", "jack-off", "jackoff", "jap", "jerk-off", "jism", "jiz", "jizm", "jizz", "kawk", "knob", "knobead", "knobed", "knobend", "knobhead", "knobjocky", "knobjokey", "kock", "kondum", "kondums", "kum", "kummer", "kumming", "kums", "kunilingus", "l3i+ch", "l3itch", "labia", "lust", "lusting", "m0f0", "m0fo", "m45terbate", "ma5terb8", "ma5terbate", "masochist", "master-bate", "masterb8", "masterbat*", "masterbat3", "masterbate", "masterbation", "masterbations", "masturbate", "mo-fo", "mof0", "mofo", "mothafuck", "mothafucka", "mothafuckas", "mothafuckaz", "mothafucked", "mothafucker", "mothafuckers", "mothafuckin", "mothafucking", "mothafuckings", "mothafucks", "mother fucker", "motherfuck", "motherfucked", "motherfucker", "motherfuckers", "motherfuckin", "motherfucking", "motherfuckings", "motherfuckka", "motherfucks", "muff", "mutha", "muthafecker", "muthafuckker", "muther", "mutherfucker", "n1gga", "n1gger", "nazi", "nigg3r", "nigg4h", "nigga", "niggah", "niggas", "niggaz", "nigger", "niggers", "nob", "nob jokey", "nobhead", "nobjocky", "nobjokey", "numbnuts", "nutsack", "orgasim", "orgasims", "orgasm", "orgasms", "p0rn", "pawn", "pecker", "penis", "penisfucker", "phonesex", "phuck", "phuk", "phuked", "phuking", "phukked", "phukking", "phuks", "phuq", "pigfucker", "pimpis", "piss", "pissed", "pisser", "pissers", "pisses", "pissflaps", "pissin", "pissing", "pissoff", "poop", "porn", "porno", "pornography", "pornos", "prick", "pricks", "pron", "pube", "pusse", "pussi", "pussies", "pussy", "pussys", "rectum", "retard", "rimjaw", "rimming", "s hit", "s.o.b.", "sadist", "schlong", "screwing", "scroat", "scrote", "scrotum", "semen", "sex", "sh!+", "sh!t", "sh1t", "shag", "shagger", "shaggin", "shagging", "shemale", "shi+", "shit", "shitdick", "shite", "shited", "shitey", "shitfuck", "shitfull", "shithead", "shiting", "shitings", "shits", "shitted", "shitter", "shitters", "shitting", "shittings", "shitty", "skank", "slut", "sluts", "smegma", "smut", "snatch", "son-of-a-bitch", "spac", "spunk", "s_h_i_t", "t1tt1e5", "t1tties", "teets", "teez", "testical", "testicle", "tit", "titfuck", "tits", "titt", "tittie5", "tittiefucker", "titties", "tittyfuck", "tittywank", "titwank", "tosser", "turd", "tw4t", "twat", "twathead", "twatty", "twunt", "twunter", "v14gra", "v1gra", "vagina", "viagra", "vulva", "w00se", "wang", "wank", "wanker", "wanky", "whoar", "whore", "willies", "willy", "xrated", "xxx","porno", "senggama", "ngewe", "ngeue", "http:", "https:", "//", "anjing", "sampah", "setan", "keparat",
                        "bangsat", "bngst", "tai", "ngentot", "goblok", "crot", "titit", "kontol", "memek", "itil", "kawin", "kentu");


                    $lolos = 1;
                    foreach ($badwords as $bad) {
                        //if (strstr($string, $url)) { // mine version
                        if (strpos($request->input('message'), $bad) !== FALSE) { // Yoshi version
                            $lolos = 0;
                        }else if(strpos($request->input('subject'), $bad) !== FALSE){
                            $lolos = 0;
                        }else if(strpos($request->input('name'), $bad) !== FALSE){
                            $lolos = 0;
                        }else if(strpos($request->input('email'), $bad) !== FALSE){
                            $lolos = 0;
                        }
                    }

                        if($lolos){
                            $contact = Message::create([
                                'name' => $request->input('name'),
                                'email' => $request->input('email'),
                                'type' => $request->input('type'),
                                'subject' => $request->input('subject'),
                                'phone' => $request->input('phone'),
                                'message' => $request->input('message'),
                                'status' => '0'
                            ]);

                            $contact=true;

                            if($contact){

                                $data = array(
                                    'name' => $request->input('name'),
                                    'email'=>$request->input('email'),
                                    'subject'=>$request->input('subject'),
                                    'message'=>$request->input('message')
                                );

                                Mail::send('email/message-template', ['data' => $data], function ($message) use($request) {
                                    $message->from('cs@sakurakomputer.com','Sakurakomputer.com Contact Form');

                                    $email = DataOption::where('slug', 'email')->first();

                                    if($email){
                                        $message->to($email->option_value)->subject($request->input('subject'));
                                    }else{
                                        $message->to('muhhusniaziz@gmail.com')->subject($request->input('subject'));     
                                    }
                                });

                                return redirect($url)
                                ->with('status', 1)
                                ->with('message', "Message sent!");
                        }else{
                                $resp = array(
                                    'status' => '0',
                                    'message' => 'Failed send message!'
                                );

                                return redirect($url)
                                ->with('status', 2)
                                ->with('message', "Failed to send message!");
                        }


                    }else{
                        $resp = array(
                            'status' => '0',
                            'message' => 'Failed send message!'
                        );

                        return redirect($url)
                            ->with('status', 2)
                            ->with('message', "Failed to send message!");
                    }

                }



            }

        }else{

          $resp = array(
              'status' => '0',
              'message' => 'Failed!'
          );

          return redirect($url)
            ->with('status', 2)
            ->with('message', "Failed to send message!");

        }



    }

    public function statCounter(Request $request){

        $ses = new \Symfony\Component\HttpFoundation\Session\Session();

        $ip 	= $request->ip();
        $date 	= date('Y-m-d');

        $query = StatCounter::where('date', $date)
            ->get();

        if (count($query) < 1){

            $stat = StatCounter::create([
                'date' => $date,
                'visitors' => '1',
                'views' => '1'
            ]);

        }else{

            if(empty($ses->get('ip'))){

                $ses->set('ip', $ip);
                $stat = StatCounter::where('date', $date)->first();
                if($stat){
                    $stat->visitors = $stat->visitors+1;
                    $stat->views = $stat->views+1;
                    $stat->save();
                }

            }else{

                if($ip!==$ses->get('ip')){
                    $ses->set('ip', $ip);
                }
                $stat = StatCounter::where('date', $date)->first();
                if($stat){
                    $stat->views = $stat->views+1;
                    $stat->save();
                }
            }

        }

        return $ses->get('ip');

    }

    public function findAlamat(Request $request){
        $provinsi = $request->provinsi ?? "";
        $kota = $request->kota ?? "";
        $kecamatan = $request->kecamatan ?? "";

        if($provinsi==""){
            $data_provinsi = TbProvinsi::all(); 
        }

        if($kota==""){
            $data_kota = TbKota::where('province_id', $provinsi)->get();
        }

        if($kecamatan==""){
            $data_kecamatan = TbKecamatan::where('city_id', $kota)->get();
        }

        $resp = array(
            'status' => '1',
            'msg' => 'Sukses',
            'data' => array(
                'provinsi' => $data_provinsi ?? [],
                'kota' => $data_kota ?? [],
                'kecamatan' => $data_kecamatan ?? [],
            )
        );
        return $resp;

    }
}
