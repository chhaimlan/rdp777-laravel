<?php


namespace App\Http\Controllers;



use App\Agent;
use App\AgentBill;
use App\BillInfo;
use App\Exceptions\GameException;
use App\HbSlots;
use App\Logger;
use App\PplayInfo;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
class HbController extends Controller
{
    //用户信息获取
    public function authenticate(Request $request)
    {
        try{
            $playerdetailrequest = $request->input('playerdetailrequest');
            $token = $playerdetailrequest['token'];
            $user = User::query()->where('token','=',$token)->first();
            if($user) {
                $currency = Config::get('agent.currency');
                $cash = number_format($user->point, 2, '.', '');
                $status = [
                    'success' => true,
                    'autherror' => false,
                    'message' => 'ok'
                ];
                $playerdetailresponse = [
                    'status' => $status,
                    'accountid' => $user->id . '',
                    'accountname' => $user->sn,
                    'balance' => (double)$cash,
                    'currencycode' => $currency
                ];
                $returnData = [
                    'playerdetailresponse' => $playerdetailresponse
                ];
                return new Response($returnData);
            }
        }catch (\Exception $e){
           // DB::rollBack();
        }
        $status2 = [
            'success' => false,
            'autherror' => true,
            'message' => 'error'
        ];
        $playerdetailresponse2 = [
            'status' => $status2
        ];
        $returnData2 = [
            'playerdetailresponse' => $playerdetailresponse2
        ];
        return new Response($returnData2);
    }


    //用户下注
    public function bet(Request $request)
    {
        //hash根据参数排序去比较,后面补,先完成逻辑
        $issuccess = true;
        $notFound = false;
        $autherror = false;
        $dtsent =  $request->input('dtsent');
        $fundtransferrequest = $request->input('fundtransferrequest');
        if($fundtransferrequest) {
            //是否取消的标记
            $isrefund = $fundtransferrequest['isrefund'];
            $funds = $fundtransferrequest['funds'];
            $userId = $fundtransferrequest['accountid'];
            //如果是取消局
            if($isrefund){
                //1完成退款,2未有注单信息,没处理
                $refund = $funds['refund'];
                $originaltransferid = $refund['originaltransferid'];
                $refundstatus = 2;
                $cash = 0.00;
                try{
                    DB::beginTransaction();
                    $user = User::query()->where('id', $userId)->lockForUpdate()->first();
                    if ($user) {
                        $cash = number_format($user->point, 2, '.', '');
                    }
                    $bills = BillInfo::query()->where('transaction_id', '=', $originaltransferid)->first();
                    if($bills) {
                        $refundstatus = 1;
                        $bills->status = 3;
                        $user->point -= $bills->settle_result;
                        $cash = number_format($user->point, 2, '.', '');
                        $user->total_wl -= $bills->settle_result;
                        $user->win_loss = $user->win_loss + $bills->settle_result;
                        $user->update();
                        $bills->save();
                        $agent = Agent::query()->find($user->agent_id);
                        $this->canleAgentBills($bills,$agent);
                        // 加减点日志 TODO 异步
                        $logger = new Logger();
                        $loggerData = [
                            'type' => 1,// 用户的
                            'event' => Logger::CANCEL_SETTLE,
                            'change_point' => -$bills->settle_result,
                            'msg_data' => [$bills->uesrname, $bills->round_id, -$bills->settle_result, $user->point],
                            'remark' => '',
                            'ip' => $request->ip()
                        ];
                        $logger->save($user, $loggerData);
                    }
                DB::commit();
                }catch (\Exception $e){
                    Log::warning($e);
                    DB::rollBack();
                }
                $status = [
                    'success' => $issuccess,
                    'refundstatus' => $refundstatus,
                ];
            }else {
                $fundinfo = $funds['fundinfo'];
                $token = $fundtransferrequest['token'];
                //table
                $friendlygameinstanceid = $fundtransferrequest['friendlygameinstanceid'];
                //roundid
                $gameinstanceid = $fundtransferrequest['gameinstanceid'];
                $debitandcredit = $funds['debitandcredit'];
                $bet_amount = 0.00;
                $sel_amount = 0.00;
                $transferId = '';
                //有下注,且有赢,否则是单个处理
                if ($debitandcredit) {
                    $transfer1 = $fundinfo[0];
                    $bet_amount = $transfer1['amount'];
                    $transfer2 = $fundinfo[1];
                    $sel_amount = $transfer2['amount'];
                    $transferId = $transfer1['transferid'];
                } else {
                    $transfer = $fundinfo[0];
                    if ($transfer['amount'] < 0) {
                        $bet_amount = $transfer['amount'];
                    } else {
                        $sel_amount = $transfer['amount'];
                    }
                    $transferId = $transfer['transferid'];
                }
                // Log::warning($fundtransferrequest);
                DB::beginTransaction();
                try {
                    //减点数
                    $user = User::query()->where('id', $userId)->lockForUpdate()->first();
                    if ($user) {
                        $bills = BillInfo::query()->where('transaction_id', '=', $transferId)->first();
                        if (!$bills) {
                            if (-$bet_amount <= $user->point) {
                                $playNo = $gameinstanceid;
                                $gameId = $friendlygameinstanceid;
                                $timestamp = strtotime($dtsent);
                                $bill_id = str_replace('-', '0', Str::uuid());
                                if ($bet_amount < 0) {
                                    $bet_amount = -$bet_amount;
                                }
                                $this->saveBetInfo($user, $gameId, $playNo, $transferId, $gameId, $timestamp, $bet_amount, $bill_id, $sel_amount);
                                //保存到代理
                                $agent = Agent::query()->find($user->agent_id);
                                $bill = BillInfo::query()->where('transaction_id', '=', $transferId)->first();
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
                                    'msg_data' => [$user->sn, $gameId, $bet_amount, $user->point],
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
                                        'msg_data' => [$user->sn, $gameId, $sel_amount, $user->point],
                                        'remark' => '',
                                        'ip' => $user->last_login_ip
                                    ];
                                    $logger->save($user, $loggerData);
                                }
                            } else {
                                $issuccess = false;
                                $notFound = true;
                            }
                        }
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    Log::warning($e);
                    DB::rollBack();
                }
                $cash = 0.00;
                if ($user) {
                    $cash = number_format($user->point, 2, '.', '');
                } else {
                    $autherror = true;
                    $issuccess = false;
                }
                if ($autherror) {
                    $status = [
                        'success' => $issuccess,
                        'autherror' => $autherror,
                    ];
                } else if ($notFound) {
                    $status = [
                        'success' => $issuccess,
                        'nofunds' => $notFound,
                    ];
                } else {
                    $status = [
                        'success' => $issuccess,
                        'successdebit' => $issuccess,
                        'successcredit' => $issuccess
                    ];
                }
            }
            $currency = Config::get('agent.currency');
            $fundtransferresponse = [
                'status' => $status,
                'currencycode' => $currency,
                'balance' => (double)$cash
            ];
        }else{
            $queryrequest = $request->input('queryrequest');
            if($queryrequest) {
                $initialdebittransferid = $queryrequest['initialdebittransferid'];
                $bills = BillInfo::query()->where('transaction_id', '=', $initialdebittransferid)->first();
                if(!$bills){
                    $issuccess = false;
                }
            }
            $status = [
                'success' => $issuccess
            ];
            $fundtransferresponse = [
                'status' => $status
            ];
        }
        $returnData = [
            'fundtransferresponse' => $fundtransferresponse

        ];
        return new Response($returnData);
    }

    //结果返回
    public function result(Request $request)
    {
        $hash = $request->input('hash');
        $userId = $request->input('userId');
        $gameId = $request->input('gameId');
        $roundId = $request->input('roundId');
        $amount = $request->input('amount');
        $reference = $request->input('reference');
        $providerId = $request->input('providerId');
        $timestamp = $request->input('timestamp');
        //暂时不保存
        $roundDetails = $request->input('roundDetails');
        $promoWinAmount = $request->input('promoWinAmount');
        $promoWinReference =  $request->input('promoWinReference');
        $promoCampaignID =  $request->input('promoCampaignID');
        $promoCampaignType =  $request->input('promoCampaignType');
        //查找下注的注单,进行结算
        $user = User::query()->findOrFail($userId);
        try{
            DB::beginTransaction();
            $bills = BillInfo::query()->where('round_id', '=',$roundId)
                ->where('shoe_id', '=', $gameId)
                ->where('table_id', '=', $providerId)
                ->where('user_id', '=', $userId)
                ->where('bet_type', '=', 0)->firstOrFail();
            $user = User::query()->where('id', $userId)->lockForUpdate()->first();
            if($amount > 0) {
                if($promoWinAmount){
                    $amount = $amount + $promoWinAmount;
                }
                $bills->bet_type = 1;
                $bills->settle_time = date('Y-m-d H:i:s', (int)$timestamp / 1000);
                $bills->settle_result = $amount - $bills->bet_amount;
                $user->point += $amount;
                $user->total_wl += $amount;
                $user->win_loss = $user->win_loss + $amount;
                $user->update();
                // 加减点日志 TODO 异步
                $logger =  new Logger();
                $loggerData = [
                    'type' => 1,// 用户的
                    'event' => Logger::BET_WIN,
                    'change_point' => $amount,
                    'msg_data' => [$user->sn, $roundId, $amount, $user->point],
                    'remark' => '',
                    'ip' => $user->last_login_ip
                ];
                $logger->save($user, $loggerData);
            }
            $bills->save();
            $agent = Agent::query()->find($user->agent_id);
            $this->updateAgentBills($bills,$agent);
            DB::commit();
        }catch (\Exception $e){
            Log::warning('难道是报错了:'.$e);
            DB::rollBack();
        }
        $currency =  Config::get('agent.currency');
        $cash = number_format($user->point,2,'.','');
        $returnData = [
            'transactionId' => $reference,
            'currency' => $currency,
            'cash' => (double)$cash,
            'bonus' => 0,
            'error'=> 0,
            'description' => 'Success'
        ];
        return new Response($returnData);
    }


    //奖金发送
    public function bonusWin(Request $request)
    {
        $hash = $request->input('hash');
        $userId = $request->input('userId');
        $amount = $request->input('amount');
        $reference = $request->input('reference');
        $providerId = $request->input('providerId');
        $timestamp = $request->input('timestamp');
        //查找下注的注单,进行结算
        try{
            DB::beginTransaction();
            DB::beginTransaction();
            $user = User::query()->where('id', $userId)->lockForUpdate()->first();
            $bills = BillInfo::query()->where('transaction_id','=',$reference)->first();
            if(!$bills) {
                if ($amount) {
                    $user->point += $amount;
                    $user->total_wl += $amount;
                    $user->win_loss = $user->win_loss + $amount;
                    $user->update();
                    $campaignId = $timestamp;
                    $playNo = $campaignId;
                    $bill_id = str_replace('-', '0', Str::uuid());
                    $this->saveBetInfo($user, $campaignId, $playNo, $reference, $providerId, $timestamp, $amount, $bill_id);
                    // 加减点日志 TODO 异步
                    $agent = Agent::query()->find($user->agent_id);
                    $bill = BillInfo::query()->where('transaction_id','=',$reference)->first();
                    $this->saveBetInfo($bill,$agent);
                    $logger = new Logger();
                    $loggerData = [
                        'type' => 1,// 用户的
                        'event' => Logger::BET_WIN,
                        'change_point' => $amount,
                        'msg_data' => [$user->sn, $reference, $amount, $user->point],
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
        $currency =  Config::get('agent.currency');
        $cash = number_format($user->point,2,'.','');
        $returnData = [
            'transactionId' => $reference,
            'currency' => $currency,
            'cash' => (double)$cash,
            'bonus' => 0,
            'error'=> 0,
            'description' => 'Success'
        ];
        return new Response($returnData);
    }
    //
    public function jackpotWin(Request $request)
    {
        $hash = $request->input('hash');
        $userId = $request->input('userId');
        $providerId = $request->input('providerId');
        $timestamp = $request->input('timestamp');
        $gameId = $request->input('gameId');
        $roundId = $request->input('roundId');
        $jackpotId = $request->input('jackpotId');
        $amount = $request->input('amount');
        $reference = $request->input('reference');
        //查找下注的注单,进行结算
        $user = User::query()->findOrFail($userId);
        try{
            DB::beginTransaction();
            $bills = BillInfo::query()->where('round_id', '=',$roundId)
                ->where('shoe_id', '=', $gameId)
                ->where('table_id', '=', $providerId)
                ->where('user_id', '=', $userId)->firstOrFail();
            $user = User::query()->where('id', $userId)->lockForUpdate()->first();
            if($bills->result){
                //如果result存在，代表头奖已经发放,不再发放第二次
            }else {
                if ($amount > 0) {
                    $bills->bet_type = 1;
                    $bills->result = $jackpotId;
                    $bills->settle_time = date('Y-m-d H:i:s', (int)$timestamp / 1000);
                    $bills->settle_result = $amount + $bills->settle_result;
                    $user->point += $amount;
                    $user->total_wl += $amount;
                    $user->win_loss = $user->win_loss + $amount;
                    $user->update();
                    // 加减点日志 TODO 异步
                    $logger = new Logger();
                    $loggerData = [
                        'type' => 1,// 用户的
                        'event' => Logger::BET_WIN,
                        'change_point' => $amount,
                        'msg_data' => [$user->sn, $roundId, $amount, $user->point],
                        'remark' => '',
                        'ip' => $user->last_login_ip
                    ];
                    $logger->save($user, $loggerData);
                }
                $bills->save();
                $agent = Agent::query()->find($user->agent_id);
                $this->updateAgentBills($bills,$agent);
            }
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
        }
        $currency =  Config::get('agent.currency');
        $cash = number_format($user->point,2,'.','');
        $returnData = [
            'transactionId' => $reference,
            'currency' => $currency,
            'cash' =>(double)$cash,
            'bonus' => 0,
            'error'=> 0,
            'description' => 'Success'
        ];
        return new Response($returnData);
    }

    public function promoWin(Request $request)
    {
        $hash = $request->input('hash');
        $userId = $request->input('userId');
        $providerId = $request->input('providerId');
        $campaignId = $request->input('campaignId');
        $campaignType = $request->input('campaignType');
        $amount = $request->input('amount');
        $currency = $request->input('currency');
        $reference = $request->input('reference');
        $timestamp = $request->input('timestamp');
        //查找下注的注单,进行结算
        try{
            DB::beginTransaction();
            $user = User::query()->where('id', $userId)->lockForUpdate()->first();
            $bills = BillInfo::query()->where('transaction_id','=',$reference)->first();
            if(!$bills) {
                if ($amount) {
                    $user->point += $amount;
                    $user->total_wl += $amount;
                    $user->win_loss = $user->win_loss + $amount;
                    $user->update();
                    $playNo = $campaignId;
                    $bill_id = str_replace('-', '0', Str::uuid());
                    $this->saveBetInfo($user, $campaignId, $playNo, $reference, $providerId, $timestamp, $amount, $bill_id);
                    $agent = Agent::query()->find($user->agent_id);
                    $bill = BillInfo::query()->where('transaction_id','=',$reference)->first();
                    $this->saveBetInfo($bill,$agent);
                    // 加减点日志 TODO 异步
                    $logger = new Logger();
                    $loggerData = [
                        'type' => 1,// 用户的
                        'event' => Logger::BET_WIN,
                        'change_point' => $amount,
                        'msg_data' => [$user->sn, $reference, $amount, $user->point],
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
        $currency =  Config::get('agent.currency');
        $cash = number_format($user->point,2,'.','');
        $returnData = [
            'transactionId' => $reference,
            'currency' => $currency,
            'cash' => (double)$cash,
            'bonus' => 0,
            'error'=> 0,
            'description' => 'Success'
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
    public function getHbResult(Request $request)
    {

        $roundId = $request->input('roundId');
         //测试
//        $brandid = 'c14da484-bd5f-ec11-94f6-0050f238c13c';
//        $apiKey = 'B2C1743D-6BEE-4312-B608-2295D3322323';
        //正式
        $brandid = 'db9223e4-2848-ec11-981f-501ac5e59727';
        $apiKey = '32186BC1-80C3-4B69-8EA4-7F45171770CE';
        $hash = hash('sha256',strtolower($roundId . $brandid . $apiKey));
     //   $resulturl = 'https://app-test.insvr.com/games/history/?brandid='.$brandid.'&gameinstanceid='.$roundId.'&hash='.$hash.'&locale=en&viewtype=game&showpaytable=1';
        $resulturl = 'https://app-b.insvr.com/games/history/?brandid='.$brandid.'&friendlyid='.$roundId.'&hash='.$hash.'&locale=en&viewtype=game&showpaytable=1';
        $returnData = [
            'error'=> 0,
            'description' => 'Success',
            'url' => $resulturl
        ];
        return new Response($returnData);
    }

    /**
     * @param Request $request
     *
     */
    public function  getGameUrl(Request  $request){
        $playerId =  $request->input('playerId');
        $token = $request->input('token');
        $secureLogin = 'lg_lg88';
        $language = 'en';
        $lobbyUrl =  Config::get('agent.lobbyUrl').'slot';
        $md5Str = 'language='.$language.'&lobbyUrl='.$lobbyUrl.'&secureLogin='.$secureLogin.'&symbol='.$playerId.'&token='.$token.'4eB3C3F4F4494406';
        $hash = md5($md5Str);
        $resulturl = $this->getPpGameUrlApi('CasinoGameAPI/game/url/',['language' => $language,'lobbyUrl' => $lobbyUrl,'secureLogin' => $secureLogin,'token' => $token,'symbol' => $playerId,'hash' => $hash]);
        $returnData = [
            'error'=> 0,
            'description' => 'Success',
            'gameURL' => $resulturl
        ];
        return new Response($returnData);
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
            return $returnResult['gameURL'];
        }catch (RequestException $e){
            throw new GameException(107);
        }
    }

    /**
     * @param User $user
     * @param $log
     */

    public function saveBetInfo(User $user,$gameId,$roundId,$reference,$providerId,$timestamp,$amount,$bill_id,$sel_amount): void
    {
        $bills = [];
        $balance = $user->point;
        //需要计算不同游戏的佣金--这里先不计算佣金
        $temp['user_id'] = $user->id;
        $temp['username'] = $user->sn;
        $temp['agent_id'] = $user->agent_id;
        $temp['bill_id'] = $bill_id;
        $temp['table_id'] = $providerId;
        $temp['shoe_id'] = $roundId;//game_id 取
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
        $temp['game_type'] = 6;
        $temp['bet_type'] = 0;
        $temp['status'] = 1;
        $temp['round_id'] = $gameId;
        $temp['bet_ip'] = $user->last_login_ip;
        $temp['transaction_id'] = $reference;
        $bills[] = $temp;
        if(strcmp($gameId, $roundId) == 0){

        }else {
            $user->point -= $amount;
            $user->point += $sel_amount;
            $user->total_wl -= $amount;
            $user->total_wl += $sel_amount;
            $user->win_loss -= $amount;
            $user->win_loss += $sel_amount;
            $user->update();
        }
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
            $sharRate = $agentItems->hb_share;
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
