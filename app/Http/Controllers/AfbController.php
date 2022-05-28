<?php


namespace App\Http\Controllers;



use App\Agent;
use App\AgentBill;
use App\BillInfo;
use App\Exceptions\GameException;
use App\Logger;
use App\PplayInfo;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
class AfbController extends Controller
{

    //用户钱包
    public function balance(Request $request)
    {
        $providerId = $request->input('providerId');
        //hash根据参数排序去比较,后面补,先完成逻辑
        $userName = $request->input('userName');
        //这里需要判断一下key是否正确
        $companyKey = $request->input('companyKey');
        $user = User::query()->where('sn','=',$userName)->first();
		$cash = number_format($user->point,2,'.','');
        $returnData = [
            'errorCode' => 0,
            'errorMessage' => "OK",
            'balance' => $cash
        ];
        return new Response($returnData);
    }

    //用户下注
    public function bet(Request $request)
    {
        $userName = $request->input('userName');
        //这里需要判断一下key是否正确
        $companyKey = $request->input('companyKey');
        $amount = $request->input('amount');
        //交易id
        $transferCode = $request->input('transferCode');
        $timestamp = $request->input('betTime');
        $betType = $request->input('BetType');
        $game_code = $request->input('Game');
        $id = $request->input('id');
        $ip = $request->input('ip');
        $t = $request->input('t');
        DB::beginTransaction();
        try{
            //减点数
                $user = User::query()->where('sn',$userName)->lockForUpdate()->first();
                $bills = BillInfo::query()->where('transaction_id','=',$transferCode)->first();
                if(!$bills) {
                    if($amount <= $user->point) {
                        $playNo = $id;
                        $bill_id = str_replace('-', '0', Str::uuid());
                        $this->saveBetInfo($user, $game_code, $playNo, $transferCode, $betType, $timestamp, $amount, $bill_id);
                        //保存到代理
                        $agent = Agent::query()->find($user->agent_id);
                        $bill = BillInfo::query()->where('transaction_id', '=', $transferCode)->first();
                        $this->saveAgentBills($bill, $agent);
                        // 加减点日志 TODO 异步
                        $logger = new Logger();
                        $loggerData = [
                            'type' => 1,// 用户的
                            'event' => Logger::PLACED_BET,
                            'change_point' => -$amount,
                            'msg_data' => [$user->sn, $playNo, $amount, $user->point],
                            'remark' => '',
                            'ip' => $user->last_login_ip
                        ];
                        $logger->save($user, $loggerData);
                    }
                }
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
        }
		$cash = number_format($user->point,2,'.','');
        $returnData = [
            'errorMessage' => 'No Error.',
            'betAmount' => $amount,
            'balance' => (double)$cash,
            'accountName' => $userName,
            'errorCode'=> 0
        ];
        return new Response($returnData);
    }

    //结果返回
    public function result(Request $request)
    {
        $userName = $request->input('userName');
        //这里需要判断一下key是否正确
        $companyKey = $request->input('companyKey');
        $winlose = $request->input('winlose');
        //交易id
        $transferCode = $request->input('transferCode');
        $timestamp = $request->input('resultTime');
        $resultType = $request->input('resultType');
        $result_code = $request->input('SendResultNum');
        $w = $request->input('w');
        $a = $request->input('a');
        $t = $request->input('t');
        $betAmount = 0.00;
        $cash = 0.00;
        Log::warning('进来没有AFB的结算：'.$userName);
        try {
            DB::beginTransaction();
            $bills = BillInfo::query()->where('transaction_id', '=', $transferCode)
                ->where('status', '=', 0)->first();
            if ($bills) {
                $user = User::query()->where('sn',$userName)->lockForUpdate()->first();
                //保存开奖结果和详情
                $bills->round_result = $resultType;
                $bills->round_details = $result_code;
                $bills->settle_time = date('Y-m-d H:i:s');
                $bills->status = 1;
                $bills->settle_result = $winlose;
                $betAmount = $bills->bet_amount;
                $addamount = $betAmount + $winlose;
                $user->point += $addamount;
                $user->total_wl += $addamount;
                $user->win_loss = $user->win_loss + $addamount;
                $user->update();
                // 加减点日志 TODO 异步
                $logger = new Logger();
                $loggerData = [
                    'type' => 1,// 用户的
                    'event' => Logger::BET_WIN,
                    'change_point' => $addamount,
                    'msg_data' => [$user->sn, $bills->round_id, $addamount, $user->point],
                    'remark' => '',
                    'ip' => $user->last_login_ip
                ];
                $logger->save($user, $loggerData);
            if(!$bills->round_details){
                $playerBetList = $this->getAfbApi('Public/InnoExcData.ashx',['Act' => 'RP_GET_CUSTOMERReNo','companyKey' => $companyKey,
                    'RefNo' => $bills->round_id,'lang' => 'EN-US']);
                $playDetail = $playerBetList[0];
                $league = $playDetail['league'];
                $home = $playDetail['home'];
                $away = $playDetail['away'];
                $odds = $playDetail['odds'];
                $side = $playDetail['side'];
                $info = $playDetail['info'];
              //  $workdate = $playDetail['workdate'];
                $score = $playDetail['score'];
                $oddsType = $playDetail['OddsType'];
                $ValidAmt = $playDetail['ValidAmt'];
                $game = trim($playDetail['game']);
                $half =  $playDetail['half'];
                $runscore = '';
                if(array_key_exists("runscore", $playDetail)){
                    $runscore = $playDetail['runscore'];
                }
                $bills->rolling = $ValidAmt;
                $betType = 'Draw';
                if (strcmp($game,"OU") == 0) {
                    if (strcmp($side, "1") == 0) {
                        $betType = 'Over';
                        //客场
                    } else if (strcmp($side, "2") == 0) {
                        $betType = 'Under';
                    }
                }
                else if(strcmp($game,"OE") == 0){
                    if (strcmp($side, "1") == 0) {
                        $betType = 'Odd';
                        //客场
                    } else if (strcmp($side, "2") == 0) {
                        $betType = 'Even';
                    }
                }else {
                    //主场
                    if (strcmp($side, "1") == 0) {
                        $betType = $home;
                        //客场
                    } else if (strcmp($side, "2") == 0) {
                        $betType = $away;
                    }
                }
                if (strcmp($game,"PAR") != 0) {
                    $odds = round((float)$odds, 2);
                }
                if((int)($half) === 1){
                    $home = $home.'(First Half)';
                }
                $bills->bet_type = $bills->bet_type . ',' .$betType.','.$info.'@'.$odds.','.$oddsType;
                if($runscore){
                    $bills->round_details = $league. ',' .$home.' vs '.$away.',running('.$runscore.'),Result:'.$score;
                }else{
                    $bills->round_details = $league. ',' .$home.' vs '.$away.',Result:'.$score;
                }
            }
                $bills->save();
                $agent = Agent::query()->find($user->agent_id);
                $this->updateAgentBills($bills, $agent);
                $cash = number_format($user->point,2,'.','');
        }
            DB::commit();
        }catch (\Exception $e){
            Log::warning('难道是报错了:'.$e);
            DB::rollBack();
        }
        $returnData = [
            'errorMessage' => 'No Error.',
            'betAmount' => $betAmount,
            'balance' => (double)$cash,
            'accountName' => $userName,
            'errorCode'=> 0
        ];
        return new Response($returnData);
    }

    //取消局
    public function refund(Request $request)
    {
        $userName = $request->input('userName');
        //这里需要判断一下key是否正确
        $companyKey = $request->input('companyKey');
        //交易id
        $transferCode = $request->input('transferCode');
        $timestamp = $request->input('t');
        //查找下注的注单,进行结算
        $betAmount = 0.00;
        $cash = 0.00;
        try{
            DB::beginTransaction();
            $bills = BillInfo::query()->where('transaction_id', '=',$transferCode)
            ->where('status', '!=', 3)->first();
        if($bills) {
            $user = User::query()->where('sn',$userName)->lockForUpdate()->first();
            if($bills->status === 0) {
                $betAmount = $bills->bet_amount;
                $bills->status = 3;
                $user->point += $bills->bet_amount;
                $user->total_wl += $bills->bet_amount;
                $user->win_loss = $user->win_loss + $bills->bet_amount;
                $user->update();
                $bills->save();
                $agent = Agent::query()->find($user->agent_id);
                $this->canleAgentBills($bills, $agent);
                // 加减点日志 TODO 异步
                $logger = new Logger();
                $loggerData = [
                    'type' => 1,// 用户的
                    'event' => Logger::CANCEL_SETTLE,
                    'change_point' => $bills->bet_amount,
                    'msg_data' => [$bills->uesrname, $bills->round_id, $bills->bet_amount, $user->point],
                    'remark' => '',
                    'ip' => $request->ip()
                ];
                $logger->save($user, $loggerData);
            }else if($bills->status === 1){
                $addAmount = $bills->settle_result;
                $bills->status = 3;
                $user->point -= $addAmount;
                $user->total_wl -= $addAmount;
                $user->win_loss -= $addAmount;
                $user->update();
                $bills->save();
                $agent = Agent::query()->find($user->agent_id);
                $this->canleAgentBills($bills, $agent);
                // 加减点日志 TODO 异步
                $logger = new Logger();
                $loggerData = [
                    'type' => 1,// 用户的
                    'event' => Logger::CANCEL_SETTLE,
                    'change_point' => -$addAmount,
                    'msg_data' => [$bills->uesrname, $bills->round_id, -$addAmount, $user->point],
                    'remark' => '',
                    'ip' => $request->ip()
                ];
                $logger->save($user, $loggerData);
            }
            $cash = number_format($user->point,2,'.','');
        }
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
        }
        $returnData = [
            'errorMessage' => 'No Error.',
            'betAmount' => $betAmount,
            'balance' => (double)$cash,
            'accountName' => $userName,
            'errorCode'=> 0
        ];
        return new Response($returnData);
    }



    /**
     * @param User $user
     * @param $log
     */

    public function saveBetInfo(User $user,$gameId,$roundId,$reference,$betType,$timestamp,$amount,$bill_id): void
    {
        $bills = [];
        $balance = $user->point;
        //需要计算不同游戏的佣金--这里先不计算佣金
        $temp['user_id'] = $user->id;
        $temp['username'] = $user->sn;
        $temp['agent_id'] = $user->agent_id;
        $temp['bill_id'] = $bill_id;
        $temp['table_id'] = $gameId;
        $temp['shoe_id'] = $gameId;//game_id 取
        $temp['bet_time'] = date('Y-m-d H:i:s');
        $temp['bet_amount'] = $amount;//betdetails取betAmount
        $temp['rolling'] = 0;
        $balance = $balance - $amount;
        $temp['after_balance'] = $balance;//现取 下注后的balance
        $temp['bet_source'] = 'API';
        //2开头为球,21为afbc球网
        $temp['game_type'] = 21;
        $temp['bet_type'] = $betType;
        $temp['status'] = 0;
        $temp['round_id'] = $roundId;
        $temp['bet_ip'] = $user->last_login_ip;
        $temp['transaction_id'] = $reference;
        $bills[] = $temp;
        $user->point -= $amount;
        $user->total_wl -= $amount;
        $user->win_loss -= $amount;
        $user->update();
        if($bills) {
            BillInfo::query()->insert($bills);
        }
    }

    public function saveAgentBills(BillInfo $bill,Agent $agent): void{
//上级们的输赢
        $listParents = [];
        array_push($listParents,$agent);
        //判断代理有多少级,获取所有父级代理
        if($agent->level > 1){
            //获取父级代理
            $agentLevel3 = Agent::query()->findOrFail($agent->parent_id);
            array_push($listParents,$agentLevel3);
            if($agentLevel3->level > 1 ) {
                //获取父级代理
                $agentLevel2 = Agent::query()->findOrFail($agentLevel3->parent_id);
                array_push($listParents,$agentLevel2);
                if($agentLevel2->level > 1 ) {
                    //获取父级代理
                    $agentLevel1 = Agent::query()->findOrFail($agentLevel2->parent_id);
                    array_push($listParents,$agentLevel1);
                }
            }
        }
        $agentComss = [];
        $comm4 = 0.00;
        $sharing4 = 0;
        $comm3 = 0.00;
        $sharing3 = 0;
        $comm2 = 0.00;
        $sharing2 = 0;
        $comm1 = 0.00;
        $sharing1 = 0.00;
        foreach ($listParents as $agentItems) {
            $sharRate = $agentItems->afb_share;
            $commsTemp['agentId'] = $agentItems->id;
            $commsTemp['level'] = $agentItems->level;
            //根据级别减掉子级占成部分和抽水,才能正确得到父级在该笔订单中的占成和抽水
            $commsTemp['sharRate'] = $sharRate;
            $commsTemp['agentCommDetail'] = 0;
            $agentComss[] = $commsTemp;
            if($agentItems->level === 4){
                $sharing4 = $sharRate;
            }
            if($agentItems->level === 3){
                $sharing3 = $sharRate;
            }
            if($agentItems->level === 2){
                $sharing2 = $sharRate;
            }
            if($agentItems->level === 1){
                $sharing1 = $sharRate;
            }
        }
        // 上级们的输赢计算
        $agentBills = [];
            foreach ($agentComss as $agentComm) {
                $agentBill['bill_id'] = $bill['id'];
                $agentBill['agent_id'] = $agentComm['agentId'];
                //根据级别减掉子级占成部分和抽水,才能正确得到父级在该笔订单中的占成和抽水
                if ($agentComm['level'] === 1) {
                    $agentComm['agentCommDetail'] = 0;
                    $agentComm['sharRate'] = $agentComm['sharRate'] - $sharing2;
                }
                if ($agentComm['level'] === 2) {
                    $agentComm['agentCommDetail'] = 0;
                    $agentComm['sharRate'] = $agentComm['sharRate'] - $sharing3;
                }
                if ($agentComm['level'] === 3) {
                    $agentComm['agentCommDetail'] = 0;
                    $agentComm['sharRate'] = $agentComm['sharRate'] - $sharing4;
                }
                if ($agentComm['level'] === 4) {
                    if($agentComm['sharRate'] > 0) {
                        $agentComm['agentCommDetail'] = 0;
                    }
                }
                $agentBill['commission'] = 0;
                $agentBill['sharing'] = $agentComm['sharRate'];
                $agentBill['level'] = $agentComm['level'];
                $agentBill['bet_time'] = $bill['bet_time'];
                $agentBill['win_lose'] = $bill->bet_amount * ($agentComm['sharRate'] / 100);
                $agentBill['status'] = 1;
                $agentBills[] = $agentBill;
            }
            //加上公司的输赢统计计算id
            $agentBill2['bill_id'] = $bill['id'];
            $agentBill2['agent_id'] = 1;
            // $comCom = (1 - $sharing1 / 100) * $comm1;
            $agentBill2['commission'] = 0;
            $agentBill2['sharing'] = 100 - $sharing1;
            $agentBill2['level'] = 0;
            $agentBill2['bet_time'] = $bill['bet_time'];
            $agentBill2['win_lose'] = $bill->bet_amount * ($agentBill2['sharing'] / 100);
            $agentBill2['status'] = 1;
            $agentBills[] = $agentBill2;
            //往代理订单表中插入数据
            AgentBill::query()->insert($agentBills);
    }

    public function updateAgentBills(BillInfo $bill,Agent $agent): void{
        // 上级们的输赢计算
        $agentBills = AgentBill::query()->where('bill_id','=',$bill->id)->get();
        foreach ($agentBills as $agentBill) {
            $agentBill['win_lose'] = -$bill->settle_result * ($agentBill['sharing'] / 100);
            $agentBill['status'] = 1;
            $agentBill->update();
        }
    }
    //取消
    public function canleAgentBills(BillInfo $bill,Agent $agent): void{
        // 上级们的输赢计算
        $agentBills = AgentBill::query()->where('bill_id','=',$bill->id)->get();
        foreach ($agentBills as $agentBill) {
            $agentBill->commission = 0;
            $agentBill->win_lose = 0;
            //状态置为取消
            $agentBill->status = 3;
            $agentBill->update();
        }
    }

    public function getAfbResult(Request $request)
    {

        $roundId = $request->input('roundId');
        $username =  $request->input('username');
        $companyKey = Config::get('agent.afb_key');
        $resulturl = $this->getAfbResultApi('Public/InnoExcData.ashx',['userName' => $username,'companyKey' => $companyKey,
            'Act' => 'MB_GET_PAR_URL','VenTransId' => $roundId,'lang' => 'EN-US']);
        $returnData = [
            'error'=> 0,
            'description' => 'Success',
            'url' =>  Config::get('agent.afb_url').$resulturl
        ];
        return new Response($returnData);
    }
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
            Log::warning('收到的数据'.json_encode($returnResult));
            return $returnResult['playerBetList'];
        }catch (\Exception $e){
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

}
