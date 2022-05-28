<?php

namespace App\Http\Controllers\Auth;

use App\Agent;
use App\BankBlack;
use App\BankInfo;
use App\Http\Controllers\Controller;
use App\MemberDealing;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        $agent_id =  Config::get('agent.agentId');
        //E-WALLET BANK
        $banker = BankInfo::query()->select(array('acount_type'))->where('status','=',1)->where('is_select','=',1)
            ->where('category','!=','CELLULAR')
            ->where('agent_id','=',$agent_id)->groupBy('acount_type')->get();
        $viewData = ['bankerList' => $banker];
        return view('auth.register',$viewData);
        
        
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $agent_name =  Config::get('agent.userName');
        $username = $agent_name.'-'.$data['name'];
        $olduser = User::query()->where('sn',$username) ->first();
        if($olduser!=null){
            return Validator::make($data, [
                'name' => ['required', 'string', 'max:255', 'confirmed'],
                'password' => ['required', 'string', 'min:6', 'confirmed']
            ]);
        }
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'confirmed']
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $agent_name =  Config::get('agent.userName');
        $agent = Agent::query()->where('sn', $agent_name)->firstOrFail();
        $agent_id = $agent->id;
        $bankerType =  $data['bank'];
        $bankerName =  $data['bank_name'];
        $bankerNumber =  $data['bank_number'];
        $referral = $data['referral'];
        if($referral){
            $user = User::query()->where('ref_code','=',$referral)->first();
            if($user) {
                $referral = $user->sn;
            }else{
                $referral = "";
            }
        }
        $login_ip =  $data['real_ip'];
        $nameLen = $data['nameLen'];
        $login_token = str_replace('-','0',Str::uuid());
        $bet_limits = (array)json_decode($agent->bet_limit);
        $bc = $bet_limits['bc'];
        $dt = $bet_limits['dt'];
        $ro = $bet_limits['ro'];
        $sb = $bet_limits['sb'];
        $bc_array = explode(',',$bc);
        $dt_array = explode(',',$dt);
        $ro_array = explode(',',$ro);
        $sb_array = explode(',',$sb);
        $usbc = $bc;
        $usdt = $dt;
        $usro = $ro;
        $ussb = $sb;
        if(count($bc_array) > 4){
            $usbc = $bc_array[0].','.$bc_array[1].','.$bc_array[2].','.$bc_array[3];
        }
        if(count($dt_array) > 4){
            $usdt = $dt_array[0].','.$dt_array[1].','.$dt_array[2].','.$dt_array[3];
        }
        if(count($ro_array) > 4){
            $usro = $ro_array[0].','.$ro_array[1].','.$ro_array[2].','.$ro_array[3];
        }
        if(count($sb_array) > 4){
            $ussb = $sb_array[0].','.$sb_array[1].','.$sb_array[2].','.$sb_array[3];
        }
        $use_arry = ['bc' =>$usbc,'dt' =>$usdt,'ro' =>$usro,'sb' =>$ussb];
        $user_limit_json = json_encode($use_arry);
        $user = User::create([
            'agent_id' => $agent_id,
            'sn' => $agent->sn.'-'.$data['name'],
            'show_name' => $data['name'],
            'nickname' => $nameLen,
            'password' => bcrypt($data['password']),
            'point' => 0,
            'credit' => 0,
            'statics' => 1,
            'commission' =>$agent->commission,
            'game_type' => $agent->game_type,
            'win_limit' => 10,
            'status' => 1,
            'last_login_ip' => $login_ip,
            'login_times' => 1,
            'token' => $login_token,
            'token_expire_time' => 0,
            'last_login_location' => 'PC',
            'last_login_time' => date('Y-m-d H:i:s'),
            'bet_limit' => $user_limit_json,
            'agent_chain' =>  $agent['agent_chain'] ? $agent['agent_chain'] . '>' . $agent['sn'] : $agent['sn'],
            'bet_status' => 1,
            'is_online' => 1,
            'phone_number' => $data['phone'],
            'email' => $data['email'],
            'banker' => $bankerType.','.$bankerName.','.$bankerNumber,
            'create_id' => $agent_id,
            'referral' => $referral,
            'refer_com' => $agent->refer_com
        ]);
        $agent_com = json_decode($agent->commission);
        MemberDealing::query()->create([
            'agent_id' => $agent->id,
            'user_id' => $user->id,
            'bc_com' => $agent_com->bc,
            'dt_com' => $agent_com->dt,
            'sb_com' => $agent_com->sb,
            'ro_com' => $agent_com->ro
        ]);
        //调用接口注册
        $this->request('RegUserInfo',['username' => $user->sn,'nickname'=> $user->nickname,'currency' =>$agent->currency, 'keyword' => Config::get('agent.keyword'),'bet_limit_groups' => 'TESTING']);
        return $user;
    }

}
