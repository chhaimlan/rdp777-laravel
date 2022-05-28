<?php


namespace App\Http\Controllers;



use App\Agent;
use App\AgentBill;
use App\BillInfo;
use App\Exceptions\GameException;
use App\HbSlots;
use App\Logger;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
class PgController extends Controller
{
    //用户信息获取
    public function authenticate(Request $request)
    {
        try{
            $operator_token = $request->input('operator_token');
            $secret_key = $request->input('secret_key');
            $operator_player_session = $request->input('operator_player_session');
            $ot = Config::get('agent.pg_ot');
            $key = Config::get('agent.pg_key');
            $error = null;
            $status = null;
            if($operator_token == $ot && $secret_key == $key) {
                if ($operator_player_session) {
                    $user = User::query()->where('token', '=', $operator_player_session)->first();
                    if ($user) {
                        $currency = 'IDR';
                        $status = [
                            'player_name' => $user->sn,
                            'nickname' => $user->nickname,
                            'currency' => $currency
                        ];
                    } else {
                        $error = [
                            'code' => 1034,
                            'message' => 'user not exits'
                        ];
                    }
                } else {
                    $error = [
                        'code' => 1200,
                        'message' => 'user not exits'
                    ];
                }
            }else{
                Log::warning('operator_token:'.$operator_token.'----secret_key:'.$secret_key);
                $error = [
                    'code' => 1034,
                    'message' => 'ot is error'
                ];
            }
        }catch (\Exception $e){
            $error = [
                'code' => 1200,
                'message' => 'user not exits'
            ];
        }
        $returnData2 = [
            'data' => $status,
            'error'=> $error
        ];
        return new Response($returnData2);
    }

    //用户信息获取
    public function balance(Request $request)
    {
        try{
            $operator_token = $request->input('operator_token');
            $secret_key = $request->input('secret_key');
            $operator_player_session = $request->input('operator_player_session');
            $player_name = $request->input('player_name');
            $error = null;
            $status = null;
            $ot = Config::get('agent.pg_ot');
            $key = Config::get('agent.pg_key');
            if($operator_token == $ot && $secret_key == $key) {
            $user = User::query()->where('sn','=',$player_name)->first();
            if($user) {
                $currency = 'IDR';
                $cash = number_format($user->point, 2, '.', '');
                $status = [
                    'balance_amount' => $cash,
                    'currency_code' => $currency,
                    "updated_time" => time()
                ];
                }else{
                    $error = [
                        'code' => 1034,
                        'message' => 'user not exits'
                    ];
                }
            }else{
                Log::warning('operator_token:'.$operator_token.'----secret_key:'.$secret_key);
                $error = [
                    'code' => 1034,
                    'message' => 'ot or key is error'
                ];
            }
        }catch (\Exception $e){
            $error = [
                'code' => 1200,
                'message' => 'user not exits'
            ];
        }
        $returnData2 = [
            'data' => $status,
            'error'=> $error
        ];
        return new Response($returnData2);
    }


    //用户下注
    public function bet(Request $request)
    {
        //hash根据参数排序去比较,后面补,先完成逻辑
        $operator_token = $request->input('operator_token');
        $secret_key = $request->input('secret_key');
        $operator_player_session = $request->input('operator_player_session');
        $player_name = $request->input('player_name');
        $game_id = $request->input('game_id');
        $parent_bet_id = $request->input('parent_bet_id');
        $bet_id = $request->input('bet_id');
        $currency_code = $request->input('currency_code');
        $bet_amount = $request->input('bet_amount');
        $win_amount = $request->input('win_amount');
        $transfer_amount = $request->input('transfer_amount');
        $transaction_id = $request->input('transaction_id');
        $bet_type = $request->input('bet_type');
        $updated_time = $request->input('updated_time');
        $issuccess = true;
        $isPoint = false;
        $error = null;
        $data = null;
        $ot = Config::get('agent.pg_ot');
        $key = Config::get('agent.pg_key');
        if($operator_token == $ot && $secret_key == $key && $currency_code == 'IDR') {
        try {
            //减点数
            DB::beginTransaction();
            if($player_name) {
                $user = User::query()->where('sn', $player_name)->lockForUpdate()->first();
                if ($user) {
                    $bills = BillInfo::query()->where('transaction_id', '=', $transaction_id)->first();
                    if (!$bills) {
                        if ($bet_amount <= $user->point) {
                            $playNo = $bet_id;
                            $gameId = $parent_bet_id;
                            $timestamp = time();
                            $sel_amount = $win_amount;
                            if($sel_amount > 0){
                                $transferamount = $sel_amount - $bet_amount;
                                if($transferamount != $transfer_amount){
                                    $sel_amount = $transfer_amount + $bet_amount;
                                }
                            }
                            $bill_id = str_replace('-', '0', Str::uuid());
                            $this->saveBetInfo($user, $gameId, $playNo, $transaction_id, $game_id, $timestamp, $bet_amount, $bill_id, $sel_amount, $bet_type);
                            //保存到代理
                            $agent = Agent::query()->find($user->agent_id);
                            $bill = BillInfo::query()->where('transaction_id', '=', $transaction_id)->first();
                            $this->saveAgentBills($bill, $agent);
                            // 加减点日志 TODO 异步
                            $loggerPoint = $user->point;
                            if($sel_amount > 0){
                                $user->point = $loggerPoint - $sel_amount;
                            }
                            $logger = new Logger();
                            $loggerData = [
                                'type' => 1,// 用户的
                                'event' => Logger::PLACED_BET,
                                'change_point' => -$bet_amount,
                                'msg_data' => [$user->sn, $playNo, $bet_amount, $user->point],
                                'remark' => '',
                                'ip' => $user->last_login_ip
                            ];
                            $logger->save($user, $loggerData);
                            // 加减点日志 TODO 异步
                            if($sel_amount > 0) {
                                $logger = new Logger();
                                $user->point = $loggerPoint;
                                $loggerData = [
                                    'type' => 1,// 用户的
                                    'event' => Logger::BET_WIN,
                                    'change_point' => $sel_amount,
                                    'msg_data' => [$user->sn, $playNo, $sel_amount, $user->point],
                                    'remark' => '',
                                    'ip' => $user->last_login_ip
                                ];
                                $logger->save($user, $loggerData);
                            }
                            $cash = number_format($user->point, 2, '.', '');
                            $data = [
                                'currency_code' => $currency_code,
                                'balance_amount' => $cash,
                                "updated_time" => $updated_time
                            ];
                        } else {
                            $isPoint = true;
                        }
                    }else{
                        $cash = number_format($user->point, 2, '.', '');
                        $data = [
                            'currency_code' => $currency_code,
                            'balance_amount' => $cash,
                            "updated_time" => $updated_time
                        ];
                    }
                }else{
                    $issuccess = false;
                }
            }
            DB::commit();
            } catch (\Exception $e) {
                $issuccess = false;
                Log::warning($e);
                DB::rollBack();
            }
        }else{
            Log::warning('operator_token:'.$operator_token.'----secret_key:'.$secret_key);
            $error = [
                'code' => 1034,
                'message' => 'ot or key is error'
            ];
        }
        if(!$issuccess){
            $error = [
                'code' => 1034,
                'message' => 'user not exits'
            ];
        }
        if($isPoint){
            $error = [
                'code' => 3202,
                'message' => 'insufficient balance'
            ];
        }
        $returnData = [
            'data' => $data,
            'error' => $error

        ];
        return new Response($returnData);
    }





    //该接口只在通知更新游戏的时候开放,平时不给予开放
    public function getGameList(Request $request)
    {
        $hbSlots = HbSlots::query()->delete();
        $brandId = 'c14da484-bd5f-ec11-94f6-0050f238c13c';
        $apiKey = 'B2C1743D-6BEE-4312-B608-2295D3322323';
        $gameList = $this->getGameListApi('GetGames',['BrandId' => $brandId,'APIKey' => $apiKey]);
        foreach ($gameList as $game){
            $isnew = 0;
            if($game['IsNew']){
                $isnew = 1;
            }
            HbSlots::create([
                'name' => $game['Name'],
                'key_name' =>  $game['KeyName'],
                'game_id' =>  $game['BrandGameId'],
                'is_new' =>  $game['IsNew'],
                'game_type' =>  $game['GameTypeId']
            ]);
        }
        $returnData = [
            'error'=> 0,
            'description' => 'Success'
        ];
        return new Response($returnData);
    }
    public function getGameListApi($uri,$requestData){
        try{
            $client = new Client([
                'base_uri' => Config::get('agent.hburl'),
                'timeout'  => 5.0,
            ]);
            $response = $client->request('post', $uri, [
                'json' => $requestData,
            ]);
            $returnResult = json_decode( $response->getBody()->getContents(),true);
            return $returnResult['Games'];
        }catch (RequestException $e){
            throw new GameException(107);
        }
    }
    public function getPgResult(Request $request)
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
        $returnData = [
            'error'=> 0,
            'description' => 'Success',
            'url' => $checkUrl
        ];
        return new Response($returnData);
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
            return $returnResult['gameURL'];
        }catch (RequestException $e){
            throw new GameException(107);
        }
    }

    /**
     * @param User $user
     * @param $log
     */

    public function saveBetInfo(User $user,$gameId,$roundId,$reference,$providerId,$timestamp,$amount,$bill_id,$sel_amount,$bet_type): void
    {
        $bills = [];
        $balance = $user->point;
        //需要计算不同游戏的佣金--这里先不计算佣金
        $temp['user_id'] = $user->id;
        $temp['username'] = $user->sn;
        $temp['agent_id'] = $user->agent_id;
        $temp['bill_id'] = $bill_id;
        $temp['table_id'] = $providerId;
        $temp['shoe_id'] = $gameId;//game_id 取
        $temp['bet_time'] = date('Y-m-d H:i:s', (int)$timestamp);
        $temp['settle_time'] = date('Y-m-d H:i:s', (int)$timestamp + 3);
        $temp['bet_amount'] = $amount;//betdetails取betAmount
        if($sel_amount > 0){
            $settle_amount = $sel_amount - $amount;
            $temp['settle_result'] = $settle_amount;
        }else{
            $temp['settle_result'] = -$amount;
        }
        $temp['rolling'] = $amount;
        $balance = $balance - $amount;
        $temp['after_balance'] = $balance;//现取 下注后的balance
        $temp['bet_source'] = 'API';
        $temp['game_type'] = 7;
        $temp['bet_type'] = $bet_type;
        $temp['status'] = 1;
        $temp['round_id'] = $roundId;
        $temp['bet_ip'] = $user->last_login_ip;
        $temp['transaction_id'] = $reference;
        $bills[] = $temp;
        $user->point -= $amount;
        $user->point += $sel_amount;
        $user->total_wl -= $amount;
        $user->total_wl += $sel_amount;
        $user->win_loss -= $amount;
        $user->win_loss += $sel_amount;
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
            $sharRate = $agentItems->pg_share;
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
            $agentBill['win_lose'] = -$bill->settle_result * ($agentComm['sharRate'] / 100);
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
        $agentBill2['win_lose'] = -$bill->settle_result * ($agentBill2['sharing'] / 100);
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
}
