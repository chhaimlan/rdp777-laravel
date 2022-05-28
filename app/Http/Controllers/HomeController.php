<?php

namespace App\Http\Controllers;

use App\Agent;
use App\BankBlack;
use App\BankInfo;
use App\BillInfo;
use App\BonusDetail;
use App\Exceptions\GameException;
use App\HbSlots;
use App\JionUs;
use App\PlaySet;
use App\PplayInfo;
use App\TransferRecord;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
class HomeController extends Controller
{

//   rdp777



/**
     * appp
     * @return \Illuminate\View\View
     */
    public  function  rdp(){
        return view('rdpapp');
    }
/**
     * index
     * @return \Illuminate\View\View
     */
    public  function  rdpindex(){
        return view('rdpindex');
    }
/**
     * casino
     * @return \Illuminate\View\View
     */
    public  function  rdpcasino(){
        return view('rdpcasino');
    }

/**
     * fishing
     * @return \Illuminate\View\View
     */
    public  function  rdpfishing(){
        return view('rdpfishing');
    }
/**
     *poker
     * @return \Illuminate\View\View
     */
    public  function  rdppoker(){
        return view('rdppoker');
    }
/**
     *promo
     * @return \Illuminate\View\View
     */
    public  function  rdppromo(){
        return view('rdppromo');
    }

/**
     *slot
     * @return \Illuminate\View\View
     */
    public  function  rdpslot(){
        return view('rdpslot');
    }

/**
     * sportbook
     * @return \Illuminate\View\View
     */
    public  function  rdpsportbook(){
        return view('rdpsportbook');
    }

/**
     * play
     * @return \Illuminate\View\View
     */
    public  function  rdpplay(){
        return view('rdpplay');
    }




// endrdp777


    /**
     * 首页
     * @return \Illuminate\View\View
     */
    public  function  index(){
        return view('index');
    }

    /**
     * home
     * @return \Illuminate\View\View
     */
    public function home(){
        $user = Auth::user();
        if($user&&$user->statics === 1){
            return redirect('/play');
        }
        return view('game');
    }



    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\
     */
    public function  play(Request $request){
        $params=$request->input();
        $user = Auth::user();
//        if($user->is_online === 0){
//            return redirect('/game/logout');
//        }
//        获取第三方游戏平台的登录认证信息,暂时只要一个token
        $deviceType = 0;
        $returnData = $this->request('rpc/auth',['username' => $user->sn,'ip' => $user->last_login_ip,'deviceType' =>$deviceType,'keyword' => Config::get('agent.keyword')]);
        $userToken = $returnData['authToken'];
        $gameUrl = $returnData['gameUrl'];
        // 可以验证有效性
        $lang = isset($params['lang']) ? $params['lang'] : 'en';
        $viewData = [
            'token'=>$userToken,
            'lang' => $lang,
            'gameUrl' => $gameUrl,
            'username' => Config::get('agent.agentSn').'_'.$user->sn,
        ];
        return view('play',$viewData);
    }
    /**
     * 规则
     * @return \Illuminate\View\View
     */
    public function  rule(){
        return view('rule');
    }

    /**
     * 游戏
     * @return \Illuminate\View\View
     */
    public function game(){
        return view('game');
    }

    /**奖金
     * @return \Illuminate\View\View
     */
    public  function  bonus(){
        return view('bonus');
    }

    /**
     * 联系我们
     * @return \Illuminate\View\View
     */
    public function contact(){
        return view('contact');
    }

    /**
     * 加盟
     * @return \Illuminate\View\View
     */
    public function listJion(){
        return view('jion');
    }

   public function getBalance(Request $request){
           $code = 0;
           $token = $request->input('token');
           if($token != Auth::user()->token){
               $code = 1;
           }
           if (Auth::user()->is_online === 2) {
               $code = 1;
           } else {
               if (Auth::user()->table_id === '0') {
                   $code = 2;
                   $newuser = User::query()->where('id', '=', Auth::user()->id)->first();
                   $newuser->table_id = '';
                   $newuser->save();
               } else if (Auth::user()->status === 0) {
                   $code = 1;
               } else if (Auth::user()->last_login_location != 'PC') {
                   $code = 1;
               }
           }
       return new Response(['data' => ['balance' => Auth::user()->point],'code' => $code]);
   }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function jionSend(Request $request)
    {
        $agent_id =  Config::get('agent.agentId');
        $reffername = $request->input('reffername');
        $olduser = User::query()->where('ref_code', $reffername)->where('agent_id','=',$agent_id)->first();
        if ($olduser != null) {
            //E-WALLET BANK
            $banker = BankInfo::query()->select(array('acount_type'))->where('status', '=', 1)->where('is_select', '=', 1)
                ->where('category', '!=', 'CELLULAR')
                ->where('agent_id', '=', $agent_id)->groupBy('acount_type')->get();
            $viewData = ['referral' => $reffername, 'bankerList' => $banker];
            return view('jion', $viewData);
        }else{
            return redirect('/register');
            
        }
    }

    public  function  checkUser(Request $request){
        $agent_id =  Config::get('agent.agentId');
        $agent_name =  Config::get('agent.userName');
        $name = $request->input('name');
        $ref_code = $request->input('ref_code');
        $oldpwd = $request->input('oldpwd');
        if($name) {
            $name = $agent_name.'-'.$name;
            $olduser = User::query()->where('sn', $name)->first();
            if ($olduser != null) {
                return new Response(['data' => ['istrue' => 1]]);
            } else {
                return new Response(['data' => ['istrue' => 0]]);
            }
        }
        if($ref_code){
            $olduser = User::query()->where('ref_code', $ref_code)->where('agent_id','=',$agent_id)->first();
            if ($olduser != null) {
                return new Response(['data' => ['istrue' => 0]]);
            } else {
                return new Response(['data' => ['istrue' => 1]]);
            }
        }
        if($oldpwd) {
            $user = Auth::user();
            if (Hash::check($oldpwd, $user->password)) {
                return new Response(['data' => ['istrue' => 1]]);
            } else {
                return new Response(['data' => ['istrue' => 0]]);
            }
        }
    }

    /**
     * 存款页面
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public  function deposit(Request $request)
    {
        $fail_msg = $request->input('fail_msg');
        $user = Auth::user();
        $banker = BankInfo::query()->where('status','=',1)->where('is_select','=',1)
            ->where('agent_id','=',$user->agent_id)->orderBy('category')->get();
        $memberBanker = $user->banker;
        $mBanker = explode(',',$memberBanker);
        $mebankerName = $mBanker[0];
        $meAccountName = $mBanker[1];
        $meAccountNumber = $mBanker[2];
       //There are unprocessed deposits and cannot be deposited again
        $transferQuery = TransferRecord::query()->where('user_name',$user->sn)
            ->where('status',0)
            ->where('type',0)->first();
        if($transferQuery){
            $fail_msg = 'Tunggu permintaan sebelumnya selesai';
        }
        $viewData = ['fail_msg'=>$fail_msg,'banker' => $banker[0],'bankerList' => $banker,'mebankerName' => $mebankerName,'meAccountName'=>$meAccountName,'meAccountNumber' => $meAccountNumber];
        return view('deposit', $viewData);
    }

     public function  depositSb(Request  $request)
     {
         $user = Auth::user();
         $moneyValue = $request->input('moneyValue');
         $mebankerName = $request->input('mebankerName');
         $meAccountName = $request->input('meAccountName');
         $meAccountNumber = $request->input('meAccountNumber');
         $remark = $request->input('remark');
         $bankId = $request->input('bankId');
         $agent = Agent::query()->findOrFail($user->agent_id);
         $fail_msg = '';
         $is_no = true;
         if($agent->statics != 1 ){
             $fail_msg = 'Sorry,Cash deposits are not supported under this agent';
             $is_no = false;
         }
         $black_bank = BankBlack::query()->where('acount_type','=',$mebankerName)
             ->where('acount_name','=',$meAccountName)->where('acount_number','=',$meAccountNumber)->first();
         if($black_bank){
             $fail_msg = 'Nomor Rekening ini sedang tidak aktif';
             $is_no = false;
         }
         $transferQuery = TransferRecord::query()->where('user_name',$user->sn)
             ->where('status',0)
             ->where('type',0)->first();
         if($transferQuery){
             $fail_msg = 'Tunggu permintaan sebelumnya selesai';
             $is_no = false;
         }
         if ($is_no && $moneyValue != '' && $mebankerName != '' && $meAccountName != '' && $meAccountNumber != '') {
             $bank = BankInfo::query()->where('id', '=', $bankId)->first();
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
                 return redirect('/dw-list');
             }
         }
         return redirect('/deposit?fail_msg='.$fail_msg);
     }

    /**
     * 交易记录
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function dwList(Request $request)
    {
        $user = Auth::user();
        $type = $request->input('type');
        $page = 1;
        if($request->input('page')) {
            $page = (int)$request->input('page');
        }
        $starTime = $request->input('starTime') ?? Carbon::parse('today')->subDays(7)->toDateTimeString();
        $endTime = $request->input('endTime') ?? date('Y-m-d H:i:s',strtotime(date('Y-m-d')) + 24*3600 -1);
        $transferQuery = TransferRecord::query()->where('user_name',$user->sn);
        if($type || $type === '0'){
            $transferQuery  ->where('type',$type);
        }
        $transferRecords = $transferQuery->orderBy('id','desc')->paginate(15,'*','page',$page);
        $viewData = [
            'starTime'=>$starTime,
            'endTime' => $endTime,
            'type' => $type,
            'transferRecords' => $transferRecords
        ];
        return view('dwList',$viewData);
    }

    /**
     * 个人中心
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function  person(Request $request){
        $jionUrl =  Config::get('agent.jionUrl');
        $user = Auth::user();
        if(!$user->ref_code){
            $user->ref_code = $this->make_coupon_card();
            $user->update();
        }
        $viewData = [
            'jionUrl'=>$jionUrl
        ];
        return view('person',$viewData);
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
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function withdraw(Request $request)
    {
        $fail_msg = $request->input('fail_msg');
        $user = Auth::user();
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
            $fail_msg = 'Tunggu permintaan sebelumnya selesai';
        }
        //真实点数,如果用户是IDR,VND,NMK,KHR
        $currency = $agent->currency;
        $blance = $user->point;
        if($currency == 'IDR' || $currency == 'VND' || $currency == 'NMK' || $currency == 'KHR'){
            $blance = $blance * 1000;
        }
        $viewData = ['fail_msg'=>$fail_msg,'blance' => $blance,'min_withdraw' => $agent->min_withdraw,'mebankerName' => $mebankerName,'meAccountName'=>$meAccountName,'meAccountNumber' => $meAccountNumber];
        return view('withdraw',$viewData);
    }

    public function withdrawSb(Request $request)
    {
        $user = Auth::user();
        $moneyValue = $request->input('moneyValue');
        $mebankerName = $request->input('mebankerName');
        $meAccountName = $request->input('meAccountName');
        $meAccountNumber = $request->input('meAccountNumber');
        $agent = Agent::query()->findOrFail($user->agent_id);
        $fail_msg = '';
        if($agent->statics != 1 ){
            $fail_msg = 'Sorry,Cash withdraw are not supported under this agent';
            return redirect('/withdraw?fail_msg='.$fail_msg);
        }
        $black_bank = BankBlack::query()->where('acount_type','=',$mebankerName)
            ->where('acount_name','=',$meAccountName)->where('acount_number','=',$meAccountNumber)->first();
        if($black_bank){
            $fail_msg = 'Nomor Rekening ini sedang tidak aktif';
            return redirect('/withdraw?fail_msg='.$fail_msg);
        }
        $transferQuery = TransferRecord::query()->where('user_name',$user->sn)
            ->where('status',0)
            ->where('type',1)->first();
        if($transferQuery){
            $fail_msg = 'Tunggu permintaan sebelumnya selesai';
            return redirect('/withdraw?fail_msg='.$fail_msg);
        }
        if($moneyValue < 0){
            $fail_msg = 'Sorry,Withdraw Amount Error';
            return redirect('/withdraw?fail_msg='.$fail_msg);
        }
        //真实点数,如果用户是IDR,VND,NMK,KHR
        $currency = $agent->currency;
        $setMoney = $moneyValue;
        if($currency == 'IDR' || $currency == 'VND' || $currency == 'NMK' || $currency == 'KHR'){
            $setMoney = $moneyValue * 0.001;
        }
        if($setMoney > $user->point){
            $fail_msg = 'Sorry,Insufficient balance';
            return redirect('/withdraw?fail_msg='.$fail_msg);
        }
        if ($moneyValue != '' && $mebankerName != '' && $meAccountName != '' && $meAccountNumber != '') {
            DB::beginTransaction();
            try{
                $user = User::query()->where('id', $user->id)->lockForUpdate()->first();
                    if($setMoney > $user->point){
                        $fail_msg = 'Sorry,Insufficient balance';
                        return redirect('/withdraw?fail_msg='.$fail_msg);
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
                        if($transferQuery) {
                            $category = $transferQuery->endperson;
                        }
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
               return redirect('/dw-list');
            }
        return redirect('/withdraw?fail_msg='.$fail_msg);
    }

    public  function checkBank(Request $request){
        $data = $request->input();
       // $bankerType =  $data['bank'];
       // $bankerName =  $data['bank_name'];
        $bankerNumber =  $data['bank_number'];
        $black_bank = BankBlack::query()->where('acount_number','=',$bankerNumber)->first();
       // $bankInfo = $bankerType.','.$bankerName.','.$bankerNumber;
        if($black_bank){
            return new Response(['data'=>['istrue'=> 2]]);
        }else{
            $agent_id =  Config::get('agent.agentId');
            $userlist = User::query()->where('banker','like','%'.$bankerNumber)
                ->where('agent_id','=',$agent_id)->get();
            $isTrue = false;
               foreach ($userlist as $user) {
                   $memberBanker = $user->banker;
                   $mBanker = explode(',', $memberBanker);
                   $meAccountNumber = $mBanker[2];
                   if (strcmp($bankerNumber, $meAccountNumber) == 0) {
                       $isTrue = true;
                   }
            }
            if($isTrue){
                return new Response(['data' => ['istrue' => 1]]);
            }
        }

        return new Response(['data'=>['istrue'=> 0]]);
    }

    /**
     * slot
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function slot(Request $request){
        $agent_id =  Config::get('agent.agentId');
        $agent = Agent::query()->find($agent_id);
        $ispp = 0;
        if (strpos($agent->game_type, '5') !== false) {
            $ispp = 1;
        } else {
            $ispp = 0;
        }
        if($ispp === 1) {
            $slotName = $request->input('slotName');
            if($slotName){
                $pPlay = PplayInfo::query()->where('game_name','like','%'.$slotName.'%')->get();
            }else {
                $pPlay = PplayInfo::query()->get();
            }
            $viewData = [
                'slot_name' => $slotName,
                'ppList' => $pPlay
            ];
            return view('slot', $viewData);
        }else{
            return redirect('/index');
        }
    }

    public function hbslot(Request $request){
        $slotName = $request->input('slotName');
        if($slotName){
            $hbslot = HbSlots::query()->where('name','like','%'.$slotName.'%')->get();
        }else {
            $hbslot = HbSlots::query()->get();
        }
        $viewData = [
            'slot_name' => $slotName,
            'hbList' => $hbslot
        ];
        return view('hbslot', $viewData);
    }

    public function pgslot(Request $request){
        $user = Auth::user();
        if($user) {
            $operator_token = Config::get('agent.pg_ot');
            $url = "https://public.pgjksjk.com/web-lobby/games/?operator_token=$operator_token&operator_player_session=$user->token&language=en";
            $viewData = [
                'url' => $url,
            ];
            return view('pgslot',$viewData);
        }else{
            return view('toslot');
        }

    }

    public function goSlot(Request $request){
        return view('toslot');
    }

    public function sports(Request $request){
        return view('sports');
    }

    public function sabungAyam(Request $request){
        return view('sabungAyam');
    }

    public function poker(Request $request){
        return view('poker');
    }

    /**
     * 获取推荐列表
     * @param Request $request
     */
    public function  refList(Request $request){
        $user = Auth::user();
        $page = 1;
        if(!$user->ref_code){
            $user->ref_code = $this->make_coupon_card();
            $user->update();
        }
        $jionUrl =  Config::get('agent.jionUrl');
        if($request->input('page')) {
            $page = (int)$request->input('page');
        }
        $refList = User::query()->where('referral','=',$user->sn)->paginate(10,'*','page',$page);
        $viewData = [
            'jionUrl' => $jionUrl,
            'refList' => $refList
        ];
        return view('refList',$viewData);
    }

    public function  reportList(Request $request){
        $user = Auth::user();
        $gameType =  $request->input('gameType');
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
        $countQurey = BillInfo::query()->where('user_id', '=', $user['id'])
            ->where('bet_time', '<=', date('Y-m-d H:i:s', strtotime($endTime)))
            ->where('bet_time', '>=', date('Y-m-d H:i:s', strtotime($starTime)));
        if ((int)$gameType === 1) {
            $billQuery->where('game_type', '<', 5);
            $countQurey->where('game_type', '<', 5);
        }else if((int)$gameType === 5){
            $billQuery->where('game_type', '=', 5);
            $countQurey->where('game_type', '=', 5);
        }
        else if((int)$gameType === 6){
            $billQuery->where('game_type', '=', 6);
            $countQurey->where('game_type', '=', 6);
        }
        else if((int)$gameType === 7){
            $billQuery->where('game_type', '=', 7);
            $countQurey->where('game_type', '=', 7);
        }
        else if((int)$gameType === 21){
            $billQuery->where('game_type', '=', 21);
            $countQurey->where('game_type', '=', 21);
        }
        $countData = $countQurey->selectRaw('sum(user_pump) as user_pump,sum(bet_amount) as bet_amount ,sum(settle_result) as settle_result,sum(rolling) as rolling')->firstOrFail();
        $billInfo = $billQuery->where('user_id', $user->id)->orderByDesc('bet_time')->paginate(10, '*', 'page', $page);
        $total = [
            'betAmountCount' => $countData['bet_amount'],
            'validBetCount'=> $countData['rolling'],
            //前端显示算上用户的佣金
            'winloseCount' => $countData['settle_result'] + $countData['user_pump']
        ];
        $items = [];
        $showId = 1;
        $curBetAmount = 0.00;
        $curValidBet = 0.00;
        $curWinLose = 0.00;
        foreach ($billInfo as $bill){
            $item['showId'] = $showId;
            $item['gameNo'] = $bill->round_id;
            $item['shoe_id'] = $bill->shoe_id;
            $item['gameLobby'] = 'LG88';
            $item['table'] = $bill->table_id;
            $item['caLculate'] = $bill->settle_time;
            $item['bet_type'] = $bill->bet_type;
            $item['bet'] = $bill->bet_amount;
            $item['validBet'] = number_format($bill->rolling,2,'.','');
            $item['result'] = $bill->round_result;
            $item['round_id'] = $bill->round_id;
            $item['resultDetail'] =  $bill->round_details;
            //前端显示输赢需要算上佣金
            $item['winlose'] = number_format( $bill->settle_result + $bill->user_pump,2,'.','');
            //前端详情需要显示下注输赢金额
            $item['settleResult'] = $bill->settle_result;
            $item['user_pump'] =  number_format( $bill->user_pump,2,'.','');
            $item['betTime'] =  $bill->bet_time;
            $item['gameType'] = $bill->game_type;
            $item['betDetail'] = (int)$bill->bet_type;
            $item['status'] = $bill->status;
            $item['transfer_id'] = $bill->transaction_id;
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
        $reportData = [
            'param' => $items,
            'curBets' => $curBets,
            'total' => $total,
            'billList' => $billInfo,
            'time' => $time,
            'gameType' => $gameType
        ];
        return view('report',$reportData);
    }

    /**
     * 验证码判断
     * @param Request $request
     * @throws \Illuminate\Validation\ValidationException
     */
    public function checkCaptcha(Request $request){
        if (captcha_check($request->input('captcha'))) {
            return new Response(['data'=>['isTrue'=> 0]]);
        }else{
            return new Response(['data'=>['isTrue'=> 1]]);
        }
    }

    /**
     * @param Request $request
     */
    public function checkComBank(Request $request){
        $bankId = $request->input('bankId');
        $bank = BankInfo::query()->find($bankId);
        if($bank->status  === 0){
            return new Response(['data'=>['isTrue'=> 1]]);
        }else{
            return new Response(['data'=>['isTrue'=> 0]]);
        }
    }

    /**
     * @param Request $request
     */
    public function  bsList(Request  $request){
        $user = Auth::user();
        $type = $request->input('type');
        $page = 1;
        if($request->input('page')) {
            $page = (int)$request->input('page');
        }
        $starTime = $request->input('starTime') ?? Carbon::parse('today')->subDays(7)->toDateTimeString();
        $endTime = $request->input('endTime') ?? date('Y-m-d H:i:s',strtotime(date('Y-m-d')) + 24*3600 -1);
        $bounusDetail = BonusDetail::query()->where('user_id','=', $user->id)
            ->where('status','=',1)
            ->where('start_time','>=', date('Y-m-d H:i:s',strtotime($starTime)))
                ->where('end_time','<=', date('Y-m-d H:i:s',strtotime($endTime)))
            ->orderByDesc('created_at')->paginate(15, '*', 'page', $page);
        $viewData = [
            'starTime'=>$starTime,
            'endTime' => $endTime,
            'type' => $type,
            'bounusDetail' => $bounusDetail
        ];
        return view('bsList',$viewData);
    }

    public function getCustomer(Request $request){
        $agent_id =  Config::get('agent.agentId');
        $agent = Agent::query()->find($agent_id);
        $ispp = 0;
        if (strpos($agent->game_type, '5') !== false) {
            $ispp = 1;
        } else {
            $ispp = 0;
        }
        $playset = PlaySet::query()->where('agent_id','=',$agent_id)
            ->where('status','=',1)->first();
        $code = 0;
        $url = "";
        if($playset){
            if($playset->type === 1){
                $code = 1;
            }
            if($playset->type === 2){
                $code = 2;
            }
            if($playset->type === 3){
                $code = 3;
            }
            $url = $playset->url;
        }
        return new Response(['url' => $url,'code' => $code,'ispp' => $ispp]);
    }

    public function afbsport(Request $request){
        $user = Auth::user();
        if($user) {
            $username = $user->sn;
            $companyKey = Config::get('agent.afb_key');
            $currencyName = 'IDR';
            $agentName = '';
            $token = $this->getAfbApi('Public/ckAcc.ashx',['userName' => $username,'companyKey' => $companyKey,
                'currencyName' => $currencyName,'agentName' => $agentName]);
            $url = Config::get('agent.afb_url')."/Public/validate.aspx?us=$username&k=$token&device=d&oddsstyle=MY&oddsmode=Single&lang=EN-US&currencyName=$currencyName";
            return new Response(['url' => $url,'code' => 1]);
        }

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

    public function getTransferShow(Request $request){
        $type = $request->input('type');
        $agent_id =  Config::get('agent.agentId');
        $page = 1;
        $transferQuery = TransferRecord::query()->where('user_id','=',$agent_id)
            ->where('type','=',$type)
            ->where('status','=',3);
        $transferRecords = $transferQuery->orderBy('id','desc')->paginate(7,'*','page',$page);
        $items = [];
        foreach ($transferRecords as $record){
            $username = explode('-',$record->user_name)[1];
            $item['username'] = substr($username, 0,strlen($username)-4) .'***'.substr($username, strlen($username)-2,2);
            $item['date_time'] = date('m-d H:i', strtotime($record->created_at));
            if($type == 1) {
                $item['amount'] = -$record->money_value;
            }else{
                $item['amount'] = $record->money_value;
            }
            array_push($items, $item);
        }
        return new Response(['code' => 1,'items' => $items]);
    }

    public function  getSLotWin(Request $request){
        $agent_id =  Config::get('agent.agentId');
        $bills = BillInfo::query()->where('agent_id','=',$agent_id)
            ->where('settle_result','>=',500)
            ->where('game_type','=',5)
            ->orderBy('id','desc')
            ->paginate(4,'*','page',1);
        $items = [];
        foreach ($bills as $bill) {
            $pPlay = PplayInfo::query()->where('game_id', '=', $bill->shoe_id)->first();
            $username = explode('-',$bill->username)[1];
            $item['username'] = substr($username, 0,strlen($username)-4) .'***';
            $item['amount'] =$bill->settle_result;
            $item['pic'] = 'https://api-sg13.ppgames.net/game_pic/square/200/'.$bill->shoe_id.'.png';
            $item['gamename'] = $pPlay->game_name;
            array_push($items, $item);
        }
        return new Response(['code' => 1,'items' => $items]);
    }
}

