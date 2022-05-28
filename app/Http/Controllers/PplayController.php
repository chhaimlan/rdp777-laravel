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
class PplayController extends Controller
{
    //用户信息获取
    public function authenticate(Request $request)
    {
       // providerId=pragmaticplay&hash=e1467eb30743fb0a180ed141a26c58f7&token=5v93mto7jr
        //PragmaticPlay
        $providerId = $request->input('providerId');
        //hash根据参数排序去比较,后面补,先完成逻辑
        $hash = $request->input('hash');
        $token = $request->input('token');
        $user = User::query()->where('token','=',$token)->firstOrFail();
        $currency =  Config::get('agent.currency');
		$cash = number_format($user->point,2,'.','');
        $returnData = [
            'userId' => $user->id.'',
            'currency' => $currency,
            'cash' => (double)$cash,
            'bonus' => 0,
            'error'=> 0,
            'description' => 'Success'
        ];
        return new Response($returnData);
    }

    //用户钱包
    public function balance(Request $request)
    {
        $providerId = $request->input('providerId');
        //hash根据参数排序去比较,后面补,先完成逻辑
        $hash = $request->input('hash');
        $userId = $request->input('userId');
        $user = User::query()->findOrFail($userId);
        $currency =  Config::get('agent.currency');
		$cash = number_format($user->point,2,'.','');
        $returnData = [
            'currency' => $currency,
            'cash' => (double)$cash,
            'bonus' => 0,
            'error'=> 0,
            'description' => 'Success'
        ];
        return new Response($returnData);
    }

    //用户下注
    public function bet(Request $request)
    {
        //hash根据参数排序去比较,后面补,先完成逻辑
        $hash = $request->input('hash');
        $userId = $request->input('userId');
        $gameId = $request->input('gameId');
        $roundId = $request->input('roundId');
        $amount = $request->input('amount');
        //交易id
        $reference = $request->input('reference');
        $providerId = $request->input('providerId');
        $timestamp = $request->input('timestamp');
        //暂时不保存
        $roundDetails = $request->input('roundDetails');
        DB::beginTransaction();
        try{
            //减点数
                $user = User::query()->where('id',$userId)->lockForUpdate()->first();
                $bills = BillInfo::query()->where('transaction_id','=',$reference)->first();
                if(!$bills) {
                    if($amount <= $user->point) {
                        $playNo = $roundId;
                        $bill_id = str_replace('-', '0', Str::uuid());
                        $this->saveBetInfo($user, $gameId, $roundId, $reference, $providerId, $timestamp, $amount, $bill_id);
                        //保存到代理
                        $agent = Agent::query()->find($user->agent_id);
                        $bill = BillInfo::query()->where('transaction_id', '=', $reference)->first();
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
                    }else{
                        $bill_id = '';
                    }
                }else{
                    $bill_id = $bills->bill_id;
                }
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
        }
        $currency =  Config::get('agent.currency');
		$cash = number_format($user->point,2,'.','');
        $returnData = [
            'transactionId' => $bill_id,
            'currency' => $currency,
            'cash' => (double)$cash,
            'bonus' => 0,
            'usedPromo'=> 0,
            'error'=> 0,
            'description' => 'Success'
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

    //取消局
    public function refund(Request $request)
    {
        $hash = $request->input('hash');
        $userId = $request->input('userId');
        $reference = $request->input('reference');
        $providerId = $request->input('providerId');
        //查找下注的注单,进行结算
        $user = User::query()->findOrFail($userId);
        try{
            DB::beginTransaction();
            $bills = BillInfo::query()->where('transaction_id', '=',$reference)
            ->where('status', '!=', 3)->first();
        if($bills) {
            $bills->status = 3;
            $user = User::query()->where('id', $userId)->lockForUpdate()->first();
            $user->point += $bills->bet_amount;
            $user->total_wl += $bills->bet_amount;
            $user->win_loss = $user->win_loss + $bills->bet_amount;
            $user->update();
            $bills->save();
            $agent = Agent::query()->find($user->agent_id);
            $this->canleAgentBills($bills,$agent);
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
        }
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
        }
        $returnData = [
            'transactionId' => $reference,
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
    //结束当前游戏
    public function endround(Request $request)
    {
        $hash = $request->input('hash');
        $userId = $request->input('userId');
        $providerId = $request->input('providerId');
        $gameId = $request->input('gameId');
        $roundId = $request->input('roundId');
        //查找下注的注单,进行结算
        $bills = BillInfo::query()->where('round_id', '=',$roundId)
            ->where('shoe_id', '=', $gameId)
            ->where('table_id', '=', $providerId)
            ->where('user_id', '=', $userId)
            ->where('status', '=', 0)->first();
        if($bills) {
            $bills->status = 1;
            $bills->settle_time = date('Y-m-d H:i:s');
            //$bills->settle_result = -$bills->bet_amount;
            $bills->save();
        }
        $user = User::query()->findOrFail($userId);
		$cash = number_format($user->point,2,'.','');
        $returnData = [
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
       $pPlay = PplayInfo::query()->delete();
       $secureLogin = 'lg_lg88';
       $md5Str = 'secureLogin='.$secureLogin.'4eB3C3F4F4494406';
       $hash = md5($md5Str);
       $gameList = $this->getGameListApi('CasinoGameAPI/getCasinoGames/',['secureLogin' => $secureLogin,'hash' => $hash]);
       foreach ($gameList as $game){
           PplayInfo::create([
               'game_id' => $game['gameID'],
               'game_name' =>  $game['gameName'],
               'game_type' =>  $game['gameTypeID'],
               'game_numeric' =>  $game['gameIdNumeric'],
              // 'jurisdictions' =>  json_decode($game['jurisdictions']),
               'platform' =>  $game['platform']
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
                'base_uri' => Config::get('agent.ppurl'),
                'timeout'  => 5.0,
            ]);
            $response = $client->request('post', $uri, [
                'form_params' => $requestData,
            ]);
            $returnResult = json_decode( $response->getBody()->getContents(),true);
            return $returnResult['gameList'];
        }catch (RequestException $e){
            throw new GameException(107);
        }
    }
    public function getGameResult(Request $request)
    {
        $playerId =  $request->input('playerId');
        $roundId = $request->input('roundId');
        $secureLogin = 'lg_lg88';
       // secureLogin=username&playerId=421&roundId=5108924498
        $md5Str = 'playerId='.$playerId.'&roundId='.$roundId.'&secureLogin='.$secureLogin.'4eB3C3F4F4494406';
        $hash = md5($md5Str);
        $resulturl = $this->getPpApi('HistoryAPI/OpenHistory/',['secureLogin' => $secureLogin,'roundId' => $roundId,'playerId' => $playerId,'hash' => $hash]);
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

    public function saveBetInfo(User $user,$gameId,$roundId,$reference,$providerId,$timestamp,$amount,$bill_id): void
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
        $temp['bet_time'] = date('Y-m-d H:i:s', (int)$timestamp / 1000);
        $temp['settle_time'] = date('Y-m-d H:i:s', (int)$timestamp / 1000 + 3);
        if (strcmp($gameId, $roundId) == 0){
            $temp['bet_amount'] = 0;//betdetails取betAmount
            $temp['settle_result'] = $amount;
            $temp['rolling'] = 0;
        }else{
            $temp['bet_amount'] = $amount;//betdetails取betAmount
            $temp['settle_result'] = -$amount;
            $temp['rolling'] = $amount;
            $balance = $balance - $amount;
        }
        $temp['after_balance'] = $balance;//现取 下注后的balance
        $temp['bet_source'] = 'API';
        $temp['game_type'] = 5;
        $temp['bet_type'] = 0;
        $temp['status'] = 1;
        $temp['round_id'] = $roundId;
        $temp['bet_ip'] = $user->last_login_ip;
        $temp['transaction_id'] = $reference;
        $bills[] = $temp;
        if(strcmp($gameId, $roundId) == 0){

        }else {
            $user->point -= $amount;
            $user->total_wl -= $amount;
            $user->win_loss -= $amount;
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
            $sharRate = $agentItems->pp_share;
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
