<?php

namespace App\Http\Controllers;
use App\Agent;
use App\BankBlack;
use App\BankInfo;
use App\BillInfo;
use App\BonusDetail;
use App\Exceptions\GameException;
use App\GroupLimit;
use App\HbSlots;
use App\LoginIps;
use App\MemberDealing;
use App\PlaySet;
use App\PplayInfo;
use App\TransferRecord;
use App\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    public function getUserInfo(Request $request){
        return $request->user();
    }
    public function login(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'pwd' => 'required'
        ]);
        $username = $request->input('username');
        $password = $request->input('pwd');
        $agent_name =  Config::get('agent.userName');
        $agent = Agent::query()->where('sn', '=',$agent_name)->first();
        //加前缀
        $username = $agent->sn.'-'.$username;
        $user = User::query()->where('sn','=',$username)->first();
        if($user) {
            if ($user->agent_id != $agent->id) {
                return [
                    'code' => 2,
                    'msg' => '登录失败!'
                ];
            }
        }
        if (Auth::attempt(['sn' => $username, 'password' => $password,])) {
            // 验证成功的逻辑
            $user = Auth::user();
            if($user->status === 1) {
            $ip = $request->input('real_ip');
            $app_version =  $request->input('app_version');
            $app_type =  $request->input('app_type');
            $login_token = str_replace('-','0',Str::uuid());
            $newuser = User::query()->where('id','=',$user->id)->first();
            if($ip && $ip!='8.8.8.8') {
                $newuser->last_login_ip = $ip;
            }else{
                $newuser->last_login_ip = $request->ip();
            }
            $newuser->login_times += 1;
            //$newuser->is_online = 1;
            $newuser->token_expire_time = 0;
            $newuser->status = 1;
            $newuser->is_online = 0;
            $newuser->last_login_time =  date('Y-m-d H:i:s');
            $newuser->token = $login_token;
            $newuser->last_login_location = 'APP:'.$app_type.'('.$app_version.')';
            $newuser->update();
            $loginIps = LoginIps::query()->where('ip','=', $newuser->last_login_ip)->first();
            if($loginIps){
                return [
                    'code' => 0,
                    'msg' => '登录失败!'
                ];
            }
            $deviceType = 1;
            $returnData = $this->request('rpc/auth', ['username' => $user->sn,'ip' => $user->last_login_ip,'deviceType' =>$deviceType,'keyword' => Config::get('agent.keyword')]);
            // TODO 验证有效性
            return [
                'code' => 1,
                'msg' => '登录成功',
                'data' => [
                    'username'=> config('agent.agentSn').'_'.$user->sn,
                    'token' => $returnData['authToken'],
                    'lang' => 'zh_CN',
                    'login_token' => $login_token
                ]
            ];
            }else{
                return [
                    'code' => 2,
                    'msg' => '登录失败!'
                ];
            }
        } else {
            $user = User::query()->where('sn','=',$username)->first();
            if($user && $user->status!=0) {
                $user->token_expire_time += 1;
                if ($user->token_expire_time >= 3) {
                    $user->token_expire_time = 0;
                    $user->status = 0;
                    $user->update();
                    return [
                        'code' => 2,
                        'msg' => '登录失败!'
                    ];
                }
                $user->update();
            }
            else if($user && $user->status === 0){
                return [
                    'code' => 2,
                    'msg' => '登录失败!'
                ];
            }
            return [
                'code' => 3,
                'msg' => '登录失败!'
            ];
        }
    }

    /**
     * APP获取用户报表数据
     * @param Request $request
     * @return array
     *
     */
    public  function  getReports(Request $request)
    {
        $gameType = $request->input('gameType');
        $username = $request->input('username');
        $user = User::query()->where('sn', '=', $username)->firstOrFail();
        if ($user) {
            $page = 1;
            if ($request->input('page')) {
                $page = (int)$request->input('page');
            }
            $perPage = (int)$request->input('perPage');
            $time = $request->input('time');
            //当天零点开始
            $starTime = date('Y-m-d H:i:s',strtotime(Carbon::parse('today')->toDateTimeString()));
            $endTime = date('Y-m-d H:i:s');
            if($time === 'yesterday'){
                $starTime = $this->getYestoday();
                $endTime = date('Y-m-d H:i:s',strtotime(Carbon::parse('today')->toDateTimeString()));
            }
            if($time === 'week'){
                $starTime = $this->getWeekday();
                $endTime =  date('Y-m-d H:i:s', strtotime(Carbon::now()->endOfWeek()));
            }
            if($time === 'month'){
                $starTime = date('Y-m-d H:i:s', strtotime(Carbon::now()->startOfMonth()->toDateTimeString()));
            }
            $billQuery = BillInfo::query()->where('bet_time', '>=', date('Y-m-d H:i:s', strtotime($starTime)))
                ->where('bet_time', '<=', date('Y-m-d H:i:s', strtotime($endTime)));
            $countQurey = BillInfo::query()->where('user_id', '=', $user['id'])
                ->where('bet_time', '<=', date('Y-m-d H:i:s', strtotime($endTime)))
                ->where('bet_time', '>=', date('Y-m-d H:i:s', strtotime($starTime)));
            if ($gameType === '1' || $gameType === '0') {
                $billQuery->where('game_type', '=', $gameType);
                $countQurey->where('game_type', '=', $gameType);
            }
            $countQurey->where('game_type', '!=', 5);
            $countData = $countQurey->selectRaw('sum(user_pump) as user_pump,sum(bet_amount) as bet_amount ,sum(settle_result) as settle_result,sum(rolling) as rolling')->firstOrFail();
            $billInfo = $billQuery->where('user_id', $user->id)->orderByDesc('bet_time')->paginate($perPage, '*', 'page', $page);
            $total = [
                'betAmountCount' => $countData['bet_amount'],
                'validBetCount'=> $countData['rolling'],
                //前端显示算上用户的佣金
                'winloseCount' => $countData['settle_result'] + $countData['user_pump']
            ];
             $items = [];
//            array_push($items,$total);
            $showId = 1;
            $curBetAmount = 0.00;
            $curValidBet = 0.00;
            $curWinLose = 0.00;
            foreach ($billInfo as $bill){
                 $item['showId'] = $showId;
              $item['gameNo'] = $bill->round_id;
              $item['gameLobby'] = 'LG88';
              $item['table'] = $bill->table_id;
              $item['caLculate'] = $bill->settle_time;
              $item['bet'] = $bill->bet_amount;
              $item['validBet'] = $bill->rolling;
              $item['result'] = $bill->round_result;
                $item['betType'] = $bill->bet_type;
              $item['resultDetail'] =  $bill->round_details;
              //前端显示输赢需要算上佣金
              $item['winlose'] = $bill->settle_result + $bill->user_pump;
              //前端详情需要显示下注输赢金额
                $item['settleResult'] = $bill->settle_result;
              $item['betTime'] =  $bill->bet_time;
              $item['gameType'] = $bill->game_type;
              $item['betDetail'] = (int)$bill->bet_type;
              $item['status'] = $bill->status;
              $item['videoUrl'] = $bill->play_video;
              array_push($items,$item);
              $showId += 1;
              //计算当前页
                $curBetAmount += $item['bet'];
                $curValidBet += $item['validBet'];
                $curWinLose +=  $item['winlose'];
            }
          //  return {code:1,msg:'success',param:param,mate:mate}
            $curBets = [
                'curBetAmount' => $curBetAmount,
                'curValidBet' => $curValidBet,
                'curWinLose' => $curWinLose
            ];
            $mate['currentPage'] = $billInfo->currentPage();
            $mate['total'] = $billInfo->total();
            $reportData = [
                'code' => 1,
                'msg' => 'success',
                'param' => $items,
                'curBets' => $curBets,
                'total' => $total,
                'hide_rebate' => $user->hide_rebate,
                'mate' => $mate
            ];
            return $reportData;
        }
        return [
            'code' => 0,
            'msg' => 'user is null'
        ];
    }

        /**
         * [getYestoday 获得昨天的格式化日期]
         * @return {[type]} [description]
         */
        private function getYestoday() {
            return date('Y-m-d H:i:s',strtotime(Carbon::parse('today')->subDays(1)->toDateTimeString()));
        }

        /**
         * getWeekday 获得一周
         * @return {[type]} [description]
         */
       private function getWeekday() {
           return  date('Y-m-d H:i:s',strtotime(Carbon::now()->startOfWeek()));  //本周
        }

    /**
     * @param Request $request
     * @return array
     */
    public function resetPassword(Request $request)
    {
        $username = $request->input('username');
        $login_token = $request->header('Authorization');
        $user = User::query()->where('sn', '=', $username)->where('token','=',$login_token)->firstOrFail();
        if ($user) {
            $password = $request->input('password');
            $passwordConf = $request->input('password_confirmation');
            if ($password != $passwordConf) {
                return ['code' => 0,
                    'msg' => 'password is not same'
                ];
            }
            $user->password = Hash::make($password);
            $user->save();
            return ['code' => 1,
                'msg' => 'password is update success'
            ];
        }else{
            return ['code' => 0,
                'msg' => 'user is null'
            ];
        }
    }

    /**
     * @return array
     */
    public function getBalance(Request $request)
    {
        $login_token = $request->header('Authorization');
        $username = $request->input('username');
        $user = User::query()->where('sn', '=', $username)->where('token','=',$login_token)->first();
        $code = 1;
        if($user) {
            if($user->table_id == '0'){
                $code = 2;
                $user->table_id = '';
                $user->save();
            }else if($user->status === 0 || $user->is_online === 2){
                $code = 3;
            }else if($user->status == 'PC'){
                $code = 3;
            }
            return [
                'code' => $code,
                'balance' =>$user->point,
                'msg' => 'balance is update success'
            ];
        }else{
            return [
                'code' => 0,
                'msg' => 'user is null'
            ];
        }
    }
    /**
     * 修改密码
     */
    public function upPassword(Request $request){
        $username = $request->input('username');
        $old_pwd = $request->input('old_pwd');
        $user = User::query()->where('sn', '=', $username)->first();
        if ($user) {
            if (Hash::check($old_pwd, $user->password)) {
                $password = $request->input('password');
                $passwordConf = $request->input('password_confirmation');
                if ($password != $passwordConf) {
                    return ['code' => 0,
                        'msg' => 'password is not same'
                    ];
                }
                $user->password = Hash::make($password);
                $user->save();
                return ['code' => 1,
                    'msg' => 'password is update success'
                ];
            }
        }
            return ['code' => 0,
                'msg' => 'user is null'
            ];
    }

    /**
     * 获取用户 Limit
     * @param Request $request
     */
    public function getUserLimit(Request $request){
        $username = $request->input('username');
        $baccArray = [];
        $dtArray = [];
        $roArray = [];
        $sbArray = [];
        $user = User::query()->where('sn', '=', $username)->first();
        if ($user) {
            //{"bc":"1,5,6","dt":"2,11,12,13","ro":"3,7,8","sb":"4,9,10"}
             $bet_limit = json_decode($user->bet_limit);
             $bacc = $bet_limit->bc;
             $dt = $bet_limit->dt;
             $ro = $bet_limit->ro;
             $sb = $bet_limit->sb;
             if($bacc && $bacc!='0'){
                     $bacc_limitIds = explode(',', $bacc);
                     if(count($bacc_limitIds)>1){
                         $bacGroupLimits = GroupLimit::query()->whereIn('id',$bacc_limitIds)->orderBy('min')->get();
                         foreach ($bacGroupLimits as $bacGroupLimit){
                             $baccValues = $bacGroupLimit->limit_value;
                             $baccAll = $baccValues['all'];
                             $baccMin =  explode('-', $baccAll)[0];
                             $baccMax =  explode('-', $baccAll)[1];
                             $baccLimit = [
                                 'min' => $baccMin,
                                 'max' => $baccMax
                             ];
                             $baccArray[] = $baccLimit;
                         }
                     } else{
                         $bacGroupLimit = GroupLimit::query()->where('id','=',$bacc)->first();
                         if($bacGroupLimit){
                             $baccValues = $bacGroupLimit->limit_value;
                             $baccAll = $baccValues['all'];
                             $baccMin =  explode('-', $baccAll)[0];
                             $baccMax =  explode('-', $baccAll)[1];
                             $baccLimit = [
                                 'min' => $baccMin,
                                 'max' => $baccMax
                             ];
                             $baccArray[] = $baccLimit;
                         }
                     }
             }
            if($dt && $dt!='0'){
                $dt_limitIds = explode(',', $dt);
                if(count($dt_limitIds)>1){
                    $dtGroupLimits = GroupLimit::query()->whereIn('id',$dt_limitIds)->orderBy('min')->get();
                    foreach ($dtGroupLimits as $dtGroupLimit){
                        $dtValues = $dtGroupLimit->limit_value;
                        $dtAll = $dtValues['all'];
                        $dtMin =  explode('-', $dtAll)[0];
                        $dtcMax =  explode('-', $dtAll)[1];
                        $dtLimit = [
                            'min' => $dtMin,
                            'max' => $dtcMax
                        ];
                        $dtArray[] = $dtLimit;
                    }
                } else{
                    $dtGroupLimit = GroupLimit::query()->where('id','=',$dt)->first();
                    if($dtGroupLimit){
                        $dtValues = $dtGroupLimit->limit_value;
                        $dtAll = $dtValues['all'];
                        $dtMin =  explode('-', $dtAll)[0];
                        $dtcMax =  explode('-', $dtAll)[1];
                        $dtLimit = [
                            'min' => $dtMin,
                            'max' => $dtcMax
                        ];
                        $dtArray[] = $dtLimit;
                    }
                }
            }
            if($ro && $ro!='0'){
                $ro_limitIds = explode(',', $ro);
                if(count($ro_limitIds)>1){
                    $roGroupLimits = GroupLimit::query()->whereIn('id',$ro_limitIds)->orderBy('min')->get();
                    foreach ($roGroupLimits as $roGroupLimit){
                        $roValues = $roGroupLimit->limit_value;
                        $roAll = $roValues['all'];
                        $roMin =  explode('-', $roAll)[0];
                        $rocMax =  explode('-', $roAll)[1];
                        $roLimit = [
                            'min' => $roMin,
                            'max' => $rocMax
                        ];
                        $roArray[] = $roLimit;
                    }
                } else{
                    $roGroupLimit = GroupLimit::query()->where('id','=',$ro)->first();
                    if($roGroupLimit){
                        $roValues = $roGroupLimit->limit_value;
                        $roAll = $roValues['all'];
                        $roMin =  explode('-', $roAll)[0];
                        $rocMax =  explode('-', $roAll)[1];
                        $roLimit = [
                            'min' => $roMin,
                            'max' => $rocMax
                        ];
                        $roArray[] = $roLimit;
                    }
                }
            }
            if($sb && $sb!='0'){
                $sb_limitIds = explode(',', $sb);
                if(count($sb_limitIds)>1){
                    $sbGroupLimits = GroupLimit::query()->whereIn('id',$sb_limitIds)->orderBy('min')->get();
                    foreach ($sbGroupLimits as $sbGroupLimit){
                        $sbValues = $sbGroupLimit->limit_value;
                        $sbAll = $sbValues['all'];
                        $sbMin =  explode('-', $sbAll)[0];
                        $sbcMax =  explode('-', $sbAll)[1];
                        $sbLimit = [
                            'min' => $sbMin,
                            'max' => $sbcMax
                        ];
                        $sbArray[] = $sbLimit;
                    }
                } else{
                    $sbGroupLimit = GroupLimit::query()->where('id','=',$sb)->first();
                    if($sbGroupLimit){
                        $sbValues = $sbGroupLimit->limit_value;
                        $sbAll = $sbValues['all'];
                        $sbMin =  explode('-', $sbAll)[0];
                        $sbcMax =  explode('-', $sbAll)[1];
                        $sbLimit = [
                            'min' => $sbMin,
                            'max' => $sbcMax
                        ];
                        $sbArray[] = $sbLimit;
                    }
                }
            }
        }else{
            $baccLimit = [
                'min' => 20,
                'max' => 1000
            ];
            $baccArray[] = $baccLimit;
            $dtLimit = [
                'min' => 10,
                'max' => 500
            ];
            $dtArray[] = $dtLimit;
            $roLimit = [
                'min' => 20,
                'max' => 800
            ];
            $roArray[] = $roLimit;
            $sbLimit = [
                'min' => 10,
                'max' => 600
            ];
            $sbArray[] = $sbLimit;
        }
        return [
            'code' => 1,
            'data' => [
                'bacc' =>$baccArray,
                'dt' => $dtArray,
                'sicbo' => $sbArray,
                'roulette' => $roArray
            ],
            'msg' => 'get user limit success'
           ];

    }

    /**
     * 修改昵称或者头像
     */
    public function updateUserInfo(Request $request){
        $username = $request->input('username');
        $avatar = $request->input('avatar');
        $nickname = $request->input('nickname');
        $user = User::query()->where('sn', '=', $username)->first();
        if ($user) {
                if($avatar){
                    $user->avatar = $avatar;
                }
                if($nickname){
                    $user->nickname = $nickname;
                }
                $user->update();
                $this->request('UpdateUser', ['username' => $user->sn,'nickname' => $nickname,'avatar' => $avatar,'keyword' => Config::get('agent.keyword')]);
                return ['code' => 1,
                    'msg' => 'user is update success'
                ];
        }
        return ['code' => 0,
            'msg' => 'user is null'
        ];
    }

    public function getBankList(Request $request)
    {
        $agent_id =  Config::get('agent.agentId');
        //E-WALLET BANK
        $banker = BankInfo::query()->select(array('acount_type'))->where('status','=',1)->where('is_select','=',1)
            ->where('category','!=','CELLULAR')
            ->where('agent_id','=',$agent_id)->groupBy('acount_type')->get();
        $items = [];
        foreach ($banker as $b) {
            $item['acount_type'] = $b->acount_type;
            array_push($items,$item);
        }
        return [
            'code' => 1,
            'data' => $items,
            'msg' => 'get success'
        ];
    }

    /**
     * app检查用户名
     * @param Request $request
     */
    public function  checkUsername(Request $request){
        $name = $request->input('name');
        $agent_name =  Config::get('agent.userName');
        if($name) {
//            $agent = Agent::query()->where('sn', $name)->first();
//            if ($agent) {
//                return [
//                    'code' => 0,
//                    'data' => 0,
//                ];
//            }
            $name = $agent_name.'-'.$name;
            $olduser = User::query()->where('sn', $name)->first();
            if ($olduser != null) {
                return [
                    'code' => 0,
                    'data' => 0,
                ];
            } else {
                return [
                    'code' => 1,
                    'data' => 1,
                ];
            }
        }

    }

    /**
     * app检查银行账号
     * @param Request $request
     */
    public function  checkBanker(Request $request){
        $bankerNumber = $request->input('bank_number');
        $black_bank = BankBlack::query()->where('acount_number','=',$bankerNumber)->first();
        if($black_bank){
            return [
                'code' => 1,
                'data' => [
                    'is_black' => 1
                ]
            ];
        }else{
            $agent_id =  Config::get('agent.agentId');
            $userlist = User::query()->where('banker','like','%'.$bankerNumber)
                ->where('agent_id','=',$agent_id)->get();
            $isTrue = false;
            foreach ($userlist as $user) {
                $memberBanker = $user->banker;
                $mBanker = explode(',',$memberBanker);
                $meAccountNumber = $mBanker[2];
                if (strcmp($bankerNumber, $meAccountNumber) == 0) {
                    $isTrue = true;
                }
            }
            if($isTrue) {
                return [
                    'code' => 1,
                    'data' => [
                        'is_black' => 2
                    ]
                ];
            }
        }
        return [
            'code' => 1,
            'data' => [
                'is_black' => 0
            ]
        ];
    }

    /**
     * app检查推荐码
     * @param Request $request
     */
    public function  checkRefCode(Request $request){
        $ref_code = $request->input('ref_code');
        $agent_id =  Config::get('agent.agentId');
        if($ref_code){
            $olduser = User::query()->where('ref_code', $ref_code)->where('agent_id','=',$agent_id)->first();
            if ($olduser != null) {
                return [
                    'code' => 1,
                    'data' => 1,
                ];
            } else {
                return [
                    'code' => 0,
                    'data' => 0,
                ];
            }
        }else{
            return [
                'code' => 1,
                'data' => 1,
            ];
        }

    }

    /**
     * app检查验证码
     * @param Request $request
     */
    public function  checkCaptcha(Request $request){

        if (captcha_api_check($request->input('captcha'),$request->input('key'))) {
            return [
                'code' => 1,
                'data' => 1,
            ];
        }else{
            return [
                'code' => 0,
                'data' => 0,
            ];
        }
    }

    /**
     * 获取验证码
     * @param Request $request
     */
    public function getCaptcha(Request $request){
        $capData = [
            'data' => app('captcha')->create('flat',true)
        ];
        return [
            'code' => 1,
            'data' => $capData,
        ];
    }

    /**
     * app上注册用户
     * @param Request $request
     * @return array
     */
    public function appReg(Request $request){
        $agent_name =  Config::get('agent.userName');
        $agent = Agent::query()->where('sn', $agent_name)->firstOrFail();
        $agent_id = $agent->id;
        $data = $request->input();
        $bankerType =  $data['bank'];
        $bankerName =  $data['bank_name'];
        $bankerNumber =  $data['bank_number'];
        $referral = $request->input('referral');
        if($referral){
            $user = User::query()->where('ref_code','=',$referral)->first();
            if($user) {
                $referral = $user->sn;
            }else{
                $referral = "";
            }
        }
        $login_ip =  $request->ip();
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
        $deviceType = 1;
        $returnData = $this->request('rpc/auth', ['username' => $user->sn,'ip' => $user->last_login_ip,'deviceType' =>$deviceType,'keyword' => Config::get('agent.keyword')]);
        // TODO 验证有效性
        return [
            'code' => 1,
            'msg' => '登录成功',
            'data' => [
                'username'=> config('agent.agentSn').'_'.$user->sn,
                'token' => $returnData['authToken'],
                'lang' => 'zh_CN',
                'login_token' => $login_token
            ]
        ];
    }

    /**
     * 获取推荐人列表
     * @param Request $request
     */
    public function  getRefList(Request $request){
        $username = $request->input('username');
        $user = User::query()->where('sn', '=', $username)->first();
        $page = 1;
        if(!$user->ref_code){
            $user->ref_code = $this->make_coupon_card();
            $user->update();
        }
        $referrallink = Config::get('agent.referralLink').'#/pages/register/register';
        if($request->input('page')) {
            $page = (int)$request->input('page');
        }
        $refList = User::query()->where('referral','=',$user->sn)->paginate(50,'*','page',$page);
        $items = [];
        foreach ($refList as $r) {
            $item['username'] = $r->sn;
            $item['date'] = date('Y-m-d H:i:s', strtotime($r->created_at));;
            array_push($items,$item);
        }
        $data = [
            'referrallink' => $referrallink.'?referralcode='.$user->ref_code,
            'referralcode' => $user->ref_code,
            'items' => $items
        ];
        return [
            'code' => 1,
            'data' => $data,
            'msg' => 'get success'
        ];
    }

    /**
     * 获取用户取款信息
     * @param Request $request
     */
    public function getWithDraw(Request $request){
        $fail_msg = 0;
        $username = $request->input('username');
        $user = User::query()->where('sn', '=', $username)->first();
        $agent = Agent::query()->find($user->agent_id);
        $memberBanker = $user->banker;
        $mBanker = explode(',',$memberBanker);
        $mebankerName = $mBanker[0];
        $meAccountName = $mBanker[1];
        $meAccountNumber = $mBanker[2];
        $transferQuery = TransferRecord::query()->where('user_name',$user->sn)
            ->where('status',0)
            ->where('type',1)->first();
        if($transferQuery){
            $fail_msg = 1;
        }
        //真实点数,如果用户是IDR,VND,NMK,KHR
        $currency = $agent->currency;
        $blance = $user->point;
        if($currency == 'IDR' || $currency == 'VND' || $currency == 'NMK' || $currency == 'KHR'){
            $blance = $blance * 1000;
        }
        $data = [
            'fail_msg'=>$fail_msg,
            'min_withdraw' => $agent->min_withdraw,
            'userbankname' => $mebankerName,
            'userbankaccount'=>$meAccountName,
            'userbanknumber' => $meAccountNumber,
            'balance' => $blance
         ];
        return [
            'code' => 1,
            'data' => $data,
            'msg' => 'get success'
        ];
    }

    /**
     * 取款提交
     * @param Request $request
     */
    public function setWithDraw(Request $request){
        $moneyValue = $request->input('amount');
        $username = $request->input('username');
        $user = User::query()->where('sn', '=', $username)->first();
        $memberBanker = $user->banker;
        $mBanker = explode(',',$memberBanker);
        $mebankerName = $mBanker[0];
        $meAccountName = $mBanker[1];
        $meAccountNumber = $mBanker[2];
        $agent = Agent::query()->findOrFail($user->agent_id);
        $fail_msg = '';
        if($agent->statics != 1 ){
            return [
                'code' => 0,
                'data' => 0,
                'msg' => 'failed'
            ];
        }
        $black_bank = BankBlack::query()->where('acount_type','=',$mebankerName)
            ->where('acount_name','=',$meAccountName)->where('acount_number','=',$meAccountNumber)->first();
        if($black_bank){
            return [
                'code' => 0,
                'data' => 0,
                'msg' => 'failed'
            ];
        }
        $transferQuery = TransferRecord::query()->where('user_name',$user->sn)
            ->where('status',0)
            ->where('type',1)->first();
        if($transferQuery){
            return [
                'code' => 0,
                'data' => 0,
                'msg' => 'failed'
            ];
        }
        if($moneyValue < 0){
            return [
                'code' => 0,
                'data' => 0,
                'msg' => 'failed'
            ];
        }
        //真实点数,如果用户是IDR,VND,NMK,KHR
        $currency = $agent->currency;
        $setMoney = $moneyValue;
        if($currency == 'IDR' || $currency == 'VND' || $currency == 'NMK' || $currency == 'KHR'){
            $setMoney = $moneyValue * 0.001;
        }
        if($setMoney > $user->point){
            return [
                'code' => 0,
                'data' => 0,
                'msg' => 'failed'
            ];
        }

        if ($moneyValue != '' && $mebankerName != '' && $meAccountName != '' && $meAccountNumber != '') {
            DB::beginTransaction();
            try{
                $user = User::query()->where('id', $user->id)->lockForUpdate()->first();
                if($setMoney > $user->point){
                    return [
                        'code' => 0,
                        'data' => 0,
                        'msg' => 'failed'
                    ];
                }
                $bank = BankInfo::query()->where('acount_type', '=', $mebankerName)
                    ->where('agent_id', '=', $user->agent_id)->first();
                $category = '';
                if ($bank) {
                    $category = $bank->category;
                } else {
                    $transferQuery = TransferRecord::query()->where('user_name', $user->sn)
                        ->where('status', 3)
                        ->where('type', 1)->first();
                    $category = $transferQuery->endperson;
                }
                TransferRecord::create([
                    'user_id' => $user->agent_id,
                    'user_name' => $user->sn,
                    'name' => $user->nickname,
                    'money_value' => -$moneyValue,
                    'set_money' => -$setMoney,
                    'transfer_id' => time(),
                    'transfer_time' => date('Y-m-d H:i:s'),
                    'w_bank_info' => $mebankerName . ',' . $meAccountName . ',' . $meAccountNumber,
                    'opperson' => $user->sn,
                    'des_point' => $user->point,
                    'set_point' => $user->point - $setMoney,
                    'endperson' => $category,
                    'type' => 1,
                    'status' => 0,
                    'phone' => $user->phone_number
                ]);
                $user->point = $user->point - $setMoney;
                $user->update();
                DB::commit();
            }catch (\Exception $e){
                DB::rollBack();
                Log::error($e);
            }
            return [
                'code' => 1,
                'data' => 1,
                'msg' => 'suc'
            ];
        }
        return [
            'code' => 0,
            'data' => 0,
            'msg' => 'failed'
        ];
    }

    /**
     * 获取用户取款信息
     * @param Request $request
     */
    public function getDeposit(Request $request){
        $fail_msg = 0;
        $username = $request->input('username');
        $user = User::query()->where('sn', '=', $username)->first();
        $agent = Agent::query()->find($user->agent_id);
        $memberBanker = $user->banker;
        $mBanker = explode(',',$memberBanker);
        $mebankerName = $mBanker[0];
        $meAccountName = $mBanker[1];
        $meAccountNumber = $mBanker[2];
        $transferQuery = TransferRecord::query()->where('user_name',$user->sn)
            ->where('status',0)
            ->where('type',0)->first();
        if($transferQuery){
            $fail_msg = 1;
        }
        $banker = BankInfo::query()->where('status','=',1)->where('is_select','=',1)
            ->where('agent_id','=',$user->agent_id)->orderBy('category')->get();
        $items = [];
        foreach ($banker as $b) {
            $item['agentbankname'] = $b->acount_type;
            $item['agentbankaccount'] = $b->acount_name;
            $item['agentbanknumber'] = $b->acount_number;
            $item['bankId'] = $b->id;
            $item['minAmount'] = $b->min_amount;
            $item['category'] = $b->category;
            $item['conversion'] = $b->conversion;
            array_push($items,$item);
        }
        $data = [
            'fail_msg'=>$fail_msg,
            'userbankname' => $mebankerName,
            'userbankaccount'=>$meAccountName,
            'userbanknumber' => $meAccountNumber,
            'items' => $items
        ];
        return [
            'code' => 1,
            'data' => $data,
            'msg' => 'get success'
        ];
    }

    /**
     * 存款
     * @param Request $requests
     */
    public function  setDeposit(Request $request){
        $moneyValue = $request->input('amount');
        $username = $request->input('username');
        $user = User::query()->where('sn', '=', $username)->first();
        $memberBanker = $user->banker;
        $mBanker = explode(',',$memberBanker);
        $mebankerName = $mBanker[0];
        $meAccountName = $mBanker[1];
        $meAccountNumber = $mBanker[2];
        $remark = $request->input('remark');
        $bankId = $request->input('bankId');
        $agent = Agent::query()->findOrFail($user->agent_id);
        $is_no = true;
        if($agent->statics != 1 ){
            return [
                'code' => 0,
                'data' => 0,
                'msg' => 'failed'
            ];
        }
        $black_bank = BankBlack::query()->where('acount_number','=',$meAccountNumber)->first();
        if($black_bank){
            return [
                'code' => 0,
                'data' => 0,
                'msg' => 'failed'
            ];
        }
        $transferQuery = TransferRecord::query()->where('user_name',$user->sn)
            ->where('status',0)
            ->where('type',0)->first();
        if($transferQuery){
            return [
                'code' => 0,
                'data' => 0,
                'msg' => 'failed'
            ];
        }
        if ($is_no && $moneyValue != '' && $mebankerName != '' && $meAccountName != '' && $meAccountNumber != '') {
            $bank = BankInfo::query()->where('id', '=', $bankId)
                ->where('status','=',1)
                ->first();
            if($bank) {
                $conversion = $bank->conversion;
                //获取到后台银行设置的存款比例,比如他存100，转换成90金额入帐
                $newmoneyValue = $moneyValue * $conversion * 0.01;
                //真实点数,如果用户是IDR,VND,NMK,KHR
                $currency = $agent->currency;
                $setMoney = $newmoneyValue;
                if($currency == 'IDR' || $currency == 'VND' || $currency == 'NMK' || $currency == 'KHR'){
                    $setMoney = $newmoneyValue * 0.001;
                }
                TransferRecord::create([
                    'user_id' => $user->agent_id,
                    'user_name' => $user->sn,
                    'name' => $user->nickname,
                    'money_value' => $newmoneyValue,
                    'set_money' => $setMoney,
                    'transfer_id' => time(),
                    'transfer_time' => date('Y-m-d H:i:s'),
                    'w_bank_info' => $mebankerName . ',' . $meAccountName . ',' .$meAccountNumber,
                    'opperson' => $user->sn,
                    'endperson' => $bank->category,
                    'type' => 0,
                    'status' => 0,
                    'd_bank_id' => $bank->id,
                    'd_bank_info' => $bank->acount_type . ',' . $bank->acount_name . ',' . $bank->acount_number,
                    'set_name' => $user->sn,
                    'remark' => $remark
                ]);
                return [
                    'code' => 1,
                    'data' => 1,
                    'msg' => 'success'
                ];
            }
        }
        return [
            'code' => 0,
            'data' => 0,
            'msg' => 'success'
        ];
    }

    public function  getDwlist(Request $request){
        $username = $request->input('username');
        $type = $request->input('type');
        $user = User::query()->where('sn', '=', $username)->first();
        $page = 1;
        if($request->input('page')) {
            $page = (int)$request->input('page');
        }
        $starTime = $request->input('starTime') ?? Carbon::parse('today')->subDays(7)->toDateTimeString();
        $endTime = $request->input('endTime') ?? date('Y-m-d H:i:s',strtotime(date('Y-m-d')) + 24*3600 -1);
        if($type){
            $bounusDetail = BonusDetail::query()->where('user_id','=', $user->id)
                ->where('status','=',1)
                ->orderByDesc('created_at')->paginate(20, '*', 'page', $page);
            $mate['currentPage'] = $bounusDetail->currentPage();
            $mate['total'] = $bounusDetail->total();
            $items = [];
            foreach ($bounusDetail as $r) {
                $item['date'] = date('Y-m-d H:i:s', strtotime($r->created_at));
                $item['transferid'] = $r->bounus_id;
                $item['amount'] = number_format($r->total_bonus,2,'.','');
                $item['transfertype'] = 'Bonus';

                if ($r->status === 1) {
                    $item['status'] = 'Successful';
                } else if ($r->status === 3) {
                    $item['status'] = 'Pending';
                } else {
                    $item['status'] = 'Failure';
                }
                array_push($items, $item);
            }
        }else {
            $transferQuery = TransferRecord::query()->where('user_name', $user->sn);
            $transferRecords = $transferQuery->orderBy('id', 'desc')->paginate(20, '*', 'page', $page);
            $mate['currentPage'] = $transferRecords->currentPage();
            $mate['total'] = $transferRecords->total();
            $items = [];
            foreach ($transferRecords as $r) {
                $item['date'] = date('Y-m-d H:i:s', strtotime($r->created_at));
                $item['transferid'] = $r->transfer_id;
                $item['amount'] = $r->money_value;
                if ($r->type === 1) {
                    $item['transfertype'] = 'Withdraw';
                } else {
                    $item['transfertype'] = 'Deposit';
                }
                if ($r->status === 0 || $r->status === 1) {
                    $item['status'] = 'Pending';
                } else if ($r->status === 3) {
                    $item['status'] = 'Successful';
                } else {
                    $item['status'] = 'Failure';
                }
                $item['remark'] = $r->remark;
                $item['Catatan'] = $r->w_bank_info;
                array_push($items, $item);
            }
        }
        $reportData = [
            'code' => 1,
            'data' => [
                'msg' => 'success',
                'param' => $items,
                'mate' => $mate
            ]
        ];
        return $reportData;
    }

    public function  getReportlist(Request $request){
        $username = $request->input('username');
        $gameType = $request->input('gameType');
        $user = User::query()->where('sn', '=', $username)->first();
        $page = 1;
        if($request->input('page')) {
            $page = (int)$request->input('page');
        }
        $time = $request->input('time');
        //当天零点开始
        $starTime = date('Y-m-d H:i:s',strtotime(Carbon::parse('today')->toDateTimeString()));
        $endTime = date('Y-m-d H:i:s');
        if($time === 'yesterday'){
            $starTime = $this->getYestoday();
            $endTime = date('Y-m-d H:i:s',strtotime(Carbon::parse('today')->toDateTimeString()));
        }
        if($time === 'week'){
            $starTime = $this->getWeekday();
            $endTime =  date('Y-m-d H:i:s', strtotime(Carbon::now()->endOfWeek()));
        }
        if($time === 'month'){
            $starTime = date('Y-m-d H:i:s', strtotime(Carbon::now()->startOfMonth()->toDateTimeString()));
        }
        $billQuery = BillInfo::query()->where('bet_time', '>=', date('Y-m-d H:i:s', strtotime($starTime)))
        ->where('bet_time', '<=', date('Y-m-d H:i:s', strtotime($endTime)));
        if ((int)$gameType === 1 ) {
            $billQuery->where('game_type', '<', 5);
        }
        else if((int)$gameType === 2){
            $billQuery->where('game_type', '=', 5);
        }
        else if((int)$gameType === 3){
            $billQuery->where('game_type', '=', 6);
        }
        else if((int)$gameType === 4){
            $billQuery->where('game_type', '=', 7);
        }
        else if((int)$gameType === 5){
            $billQuery->where('game_type', '=', 21);
        }
         $billInfo = $billQuery->where('user_id', $user->id)->orderByDesc('bet_time')->paginate(20, '*', 'page', $page);
        $mate['currentPage'] = $billInfo->currentPage();
         $mate['total'] = $billInfo->total();
        $items = [];
        foreach ($billInfo as $bill) {
            $item['gameNo'] = $bill->round_id;
            if($bill->game_type == 5){
                $item['lobby'] = 'Pragmatic Slot';
            }
            else if($bill->game_type == 6){
                $item['lobby'] = 'HABANERO Slot';
            } else if($bill->game_type == 7){
                $item['lobby'] = 'PG Slot';
            } else if($bill->game_type == 21){
            $item['lobby'] = 'AFB Sports';
           }
            else {
                $item['lobby'] = 'LG88';
            }
            $item['table'] = $bill->table_id;
            $item['bet'] = $bill->bet_amount;
            $item['gameType'] = $bill->game_type;
            $item['validBet'] = number_format($bill->rolling,2,'.','');
            $item['result'] = $bill->round_result;
            $item['round_id'] = $bill->round_id;
            $item['shoe_id'] = $bill->shoe_id;
            $item['betType'] = $bill->bet_type;
            $item['round_details'] = $bill->round_details;
            $item['commission'] = number_format($bill->user_pump,2,'.','');
            $item['videoUrl'] = $bill->play_video;
            //前端显示输赢需要算上佣金
            $item['winLose'] =  number_format($bill->settle_result + $bill->user_pump,2,'.','');
            //前端详情需要显示下注输赢金额
           // $item['result'] = $bill->settle_result;
            $item['transfer_id'] = $bill->transaction_id;
            $item['betTime'] = date('Y-m-d H:i:s', strtotime($bill->bet_time));
            array_push($items, $item);
        }
        $reportData = [
            'code' => 1,
            'data' => [
                'msg' => 'success',
                'param' => $items,
                'mate' => $mate
            ]
        ];
        return $reportData;
    }

    /**
     * 获取电子游戏
     * @param Request $request
     * @return
     */
    public function getSlot(Request $request){
        $page = $request->input('page');
        $name = $request->input('name');
        if(trim($name)){
            $pPlay = PplayInfo::query()->where('game_name','like','%'.$name.'%')->paginate(150, '*', 'page', $page);;
        }else {
            $pPlay = PplayInfo::query()->paginate(150, '*', 'page', $page);
        }
        $slotData = [
            'code' => 1,
            'data' => [
                'msg' => 'success',
                'param' => $pPlay->items()
            ]
        ];
        return $slotData;
    }
    public function getGameResult(Request $request)
    {
        $username =  $request->input('username');
        $user = User::query()->where('sn', '=', $username)->first();
        $playerId = $user->id;
        $roundId = $request->input('round_id');
        $secureLogin = 'lg_lg88';
        $md5Str = 'playerId='.$playerId.'&roundId='.$roundId.'&secureLogin='.$secureLogin.'4eB3C3F4F4494406';
       // $md5Str = 'playerId='.$playerId.'&roundId='.$roundId.'&secureLogin='.$secureLogin.'testKey';
        $hash = md5($md5Str);
        $resulturl = $this->getPpApi('HistoryAPI/OpenHistory/',['secureLogin' => $secureLogin,'roundId' => $roundId,'playerId' => $playerId,'hash' => $hash]);
        $returnData = [
                'code' => 1,
                'data' => [
                    'url' => $resulturl
                ]
            ];
        return  $returnData;
    }

    /**
     * @param Request $request
     *
     */
    public function  getPpurl(Request  $request){
        $playerId =  $request->input('game_id');
        $username = $request->input('username');
        $user = User::query()->where('sn', '=', $username)->first();
        $token = $user->token;
        $secureLogin = 'lg_lg88';
        $language = 'en';
        $lobbyUrl =  Config::get('agent.lobbyUrl').'lobby.html';
        //正式
        $md5Str = 'language='.$language.'&lobbyUrl='.$lobbyUrl.'&secureLogin='.$secureLogin.'&symbol='.$playerId.'&token='.$token.'4eB3C3F4F4494406';
        //测试
       // $md5Str = 'language='.$language.'&lobbyUrl='.$lobbyUrl.'&secureLogin='.$secureLogin.'&symbol='.$playerId.'&token='.$token.'testKey';
        $hash = md5($md5Str);
        $resulturl = $this->getPpGameUrlApi('CasinoGameAPI/game/url/',['language' => $language,'lobbyUrl' => $lobbyUrl,'secureLogin' => $secureLogin,'token' => $token,'symbol' => $playerId,'hash' => $hash]);
        $returnData = [
            'code' => 1,
            'data' => [
                'gameURL' => $resulturl
            ]
        ];

        return $returnData;
    }

    public function  getCurl(Request  $request){
        $agent_id =  Config::get('agent.agentId');
        $playset = PlaySet::query()->where('agent_id','=',$agent_id)
            ->where('status','=',1)->first();
        $url = "https://direct.lc.chat/13218531/";
        if($playset){
            if($playset->type === 1){
                $url = "https://direct.lc.chat/".$playset->url."/";
            }
            if($playset->type === 2){
                $url = "https://tawk.to/chat/".$playset->url."/default";
            }
            if($playset->type === 3){
                $url = "http://v2.zopim.com/widget/livechat.html?key=".$playset->url;
            }
        }
        $returnData = [
            'code' => 1,
            'data' => [
                'url' => $url
            ]
        ];
        return $returnData;
    }


    public function getPpApi($uri,$requestData){
        try{
            $client = new Client([
                'base_uri' => Config::get('agent.ppurl'),
                'timeout'  => 5.0,
            ]);
            $response = $client->request('post', $uri, [
                'form_params' => $requestData,
            ]);
            $returnResult = json_decode( $response->getBody()->getContents(),true);
//            if($returnResult['error'] !== 0){
//                throw new GameException(107);
//            }
            return $returnResult['url'];
        }catch (RequestException $e){
            throw new GameException(107);
        }
    }

    public function getPpGameUrlApi($uri,$requestData){
        try{
            $client = new Client([
                'base_uri' => Config::get('agent.ppurl'),
                'timeout'  => 5.0,
            ]);
            $response = $client->request('post', $uri, [
                'form_params' => $requestData,
            ]);
            $returnResult = json_decode( $response->getBody()->getContents(),true);
            Log::warning($returnResult);
            return $returnResult['gameURL'];
        }catch (RequestException $e){
            throw new GameException(107);
        }
    }

    /**
     * 获取电子游戏
     * @param Request $request
     * @return
     */
    public function getHb(Request $request){
        $page = $request->input('page');
        $slotName = $request->input('name');
        if(trim($slotName)) {
            $hb = HbSlots::query()->where('name', 'like', '%' . $slotName . '%')->paginate(150, '*', 'page', $page);
        }else {
            $hb = HbSlots::query()->paginate(150, '*', 'page', $page);
        }
        $slotData = [
            'code' => 1,
            'data' => [
                'msg' => 'success',
                'param' => $hb->items()
            ]
        ];
        return $slotData;
    }

    /**
     * @param Request $request
     *
     */
    public function getHburl(Request  $request){
        $playerId =  $request->input('game_id');
        $username = $request->input('username');
        $user = User::query()->where('sn', '=', $username)->first();
        $token = $user->token;
        $resulturl = 'https://app-b.insvr.com/go.ashx?brandid=db9223e4-2848-ec11-981f-501ac5e59727&keyname='.$playerId.
            '&token='.$token.'&mode=real&locale=en';
        $returnData = [
            'code' => 1,
            'data' => [
                'gameURL' => $resulturl
            ]
        ];

        return $returnData;
    }

    public function getHbResultPhone(Request $request)
    {
 //       $username =  $request->input('username');
//        $user = User::query()->where('sn', '=', $username)->first();
        $roundId = $request->input('round_id');
        $brandid = 'db9223e4-2848-ec11-981f-501ac5e59727';
        $apiKey = '32186BC1-80C3-4B69-8EA4-7F45171770CE';
        $hash = hash('sha256',strtolower($roundId . $brandid . $apiKey));
        $resulturl = 'https://app-b.insvr.com/games/history/?brandid='.$brandid.'&friendlyid='.$roundId.'&hash='.$hash.'&locale=en&viewtype=game&showpaytable=1';
        $returnData = [
            'code' => 1,
            'data' => [
                'url' => $resulturl
            ]
        ];
        return  $returnData;
    }

    public function getPgUrl(Request  $request){
        $username = $request->input('username');
        $user = User::query()->where('sn', '=', $username)->first();
        $operator_token = Config::get('agent.pg_ot');
        $url = "https://public.pgjksjk.com/web-lobby/games/?operator_token=$operator_token&operator_player_session=$user->token&language=en";
        $returnData = [
            'code' => 1,
            'data' => [
                'gameURL' => $url
            ]
        ];
        return $returnData;
    }

    public function getAfbPhone(Request $request){
        $username = $request->input('username');
        $companyKey = Config::get('agent.afb_key');
        $currencyName = 'IDR';
        $agentName = '';
        $token = $this->getAfbApi('Public/ckAcc.ashx',['userName' => $username,'companyKey' => $companyKey,
            'currencyName' => $currencyName,'agentName' => $agentName]);
        $url = Config::get('agent.afb_url')."/Public/validate.aspx?us=$username&k=$token&device=m&oddsstyle=MY&oddsmode=Single&lang=EN-US&currencyName=$currencyName";
        $returnData = [
            'code' => 1,
            'data' => [
                'gameURL' => $url
            ]
        ];
        return $returnData;
    }

    public function getPgResultPhone(Request $request)
    {

        $trace_id = Str::uuid();
        $key = Config::get('agent.pg_key');
        $operator_token = Config::get('agent.pg_ot');
        $psid = $request->input('psid');
        $sid = $request->input('sid');
        $resulturl = 'Login/v1/LoginProxy?'.$trace_id;
        $data = $this->getPgApi($resulturl,['operator_token' => $operator_token,'secret_key' => $key]);
        $t = $data['operator_session'];
        $trace_id = Str::uuid();
        $checkUrl =  Config::get('agent.pg_check').'trace_id='.$trace_id.'&t='.$t.'&psid='.$psid.'&sid='.$sid.'&lang=en&type=operator';
        $returnData =[
            'code' => 1,
            'data' => [
                'url' => $checkUrl
            ]
        ];
        return  $returnData;
    }

    public function getAfbPhoneResult(Request $request)
    {
        $roundId = $request->input('roundId');
        $username =  $request->input('username');
        $companyKey = Config::get('agent.afb_key');
        $resulturl = $this->getAfbResultApi('Public/InnoExcData.ashx',['userName' => $username,'companyKey' => $companyKey,
            'Act' => 'MB_GET_PAR_URL','VenTransId' => $roundId,'lang' => 'EN-US']);
        $returnData =[
            'code' => 1,
            'data' => [
                'url' => Config::get('agent.afb_url').$resulturl
            ]
        ];
        return $returnData;
    }
    public function getPgApi($uri,$requestData){
        try{
            $client = new Client([
                'base_uri' => Config::get('agent.pg_url'),
                'timeout'  => 5.0,
            ]);
            $response = $client->request('post', $uri, [
                'form_params' => $requestData,
            ]);
            $returnResult = json_decode( $response->getBody()->getContents(),true);
            return $returnResult['data'];
        }catch (RequestException $e){
            throw new GameException(107);
        }
    }

    /**
     * 获取afb球的token
     * @param $uri
     * @param $requestData
     * @return mixed
     */
    public function getAfbApi($uri,$requestData){
        try{
            //  Log::warning('请求数据：'.json_encode($requestData));
            $client = new Client([
                'base_uri' => Config::get('agent.afb_url'),
                'timeout'  => 5.0,
            ]);
            $response = $client->request('post', $uri, [
                'json' => $requestData,
            ]);
            $returnResult = json_decode( $response->getBody()->getContents(),true);
            // Log::warning('收到的数据'.json_encode($returnResult));
            return $returnResult['token'];
        }catch (RequestException $e){
            Log::warning('错误:'.$e);
        }
    }

    public function getAfbResultApi($uri,$requestData){
        try{
            $client = new Client([
                'base_uri' => Config::get('agent.afb_url'),
                'timeout'  => 5.0,
            ]);
            $response = $client->request('post', $uri, [
                'json' => $requestData,
            ]);
            $returnResult = json_decode( $response->getBody()->getContents(),true);
            return $returnResult['PARURL'];
        }catch (\Exception $e){
            Log::warning('错误:'.$e);
        }
    }
    // 制作邀请码
    public function make_coupon_card() {
        $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $rand = $code[rand(0,25)]
            .strtoupper(dechex(date('m')))
            .date('d').substr(time(),-5)
            .substr(microtime(),2,5)
            .sprintf('%02d',rand(0,99));
        for(
            $a = md5( $rand, true ),
            $s = '0123456789ABCDEFGHIJKLMNOPQRSTUV',
            $d = '',
            $f = 0;
            $f < 8;
            $g = ord( $a[ $f ] ),
            $d .= $s[ ( $g ^ ord( $a[ $f + 8 ] ) ) - $g & 0x1F ],
            $f++
        );
        return  $d;
    }

}

