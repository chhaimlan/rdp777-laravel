<?php

namespace App\Http\Controllers\OpenApi;
use App\BillInfo;
use App\Exceptions\GameException;
use App\Http\Controllers\OpenApi\BaseInfoController;
use App\TransferRecord;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class OpenApiController extends BaseInfoController {
    /**
     *用户注册,加密策略还未定义(根据秘钥Key定义)
     *
     */
    public function regUserInfo(Request $request)
    {
        // TODO 获取解密后的数据,解密的方法暂时未写
        $decryptData = $this->getDecryptData($request);
        try{
            // TODO 校验解密数据的数据格式
            $this->checkSign($request, $decryptData);
            $reName = $decryptData['username'];
            $username = $this->agent->sn.'-'.$reName;
            //获取到的是agent的限红，和用户保持一致
            $bet_limit_groups = $this->agent->bet_limit;
        }catch (\Exception $e){
            throw new GameException(103);
        }
        $user = User::query()->where('sn', $username)->first();
        if ($user) {
            throw new GameException(109);
        }
        $user = $this->createUser($username, $decryptData,$bet_limit_groups);
        // TODO 创建用户下注钱包
        $nickename = '***'.substr($reName,strlen($reName)-3,3);
        $this->request('RegUserInfo',['username' => $user->sn,'nickname' => $nickename,'currency' => $this->agent->currency, 'keyword' => Config::get('agent.keyword'),'bet_limit_groups' => 'AGENTTING']);
        if (!$user) {
            throw new GameException(133);
        }
        $data = ['username' => $reName];
        return $this->successReturn($data);
    }

    private function createUser($username, $decryptData,$bet_limit_groups)
    {
        //需要区分是转账钱包，还是第三方调用代理用户,第三方调用代理用户没有钱包
        $user = User::query()->create([
            'sn' => $username,
            'nickname' => $decryptData['username'],
            'agent_id' => $this->agent->id,
            'bet_limit' => $bet_limit_groups,
            'credit' => 0,
            'point' => 0,
            'status' => 1,
            'type' => 0,
            'agent_chain' => $this->agent->agent_chain.'>'.$this->agent->sn,
            'commission' => $this->agent->commission,
            'create_id' => $this->agent->id,
            'win_limit' => $this->agent->win_limit,
            'statics' => 2,
            'game_type'=> $this->agent->game_type
        ]);
        return $user;
    }
    /**
     * 验证用户名
     * @param Request $request
     * @return
     */
    public function verifyUsername(Request $request)
    {
        $decryptData = $this->getDecryptData($request);
        try{
            $this->checkSign($request, $decryptData);
            $reName = $decryptData['username'];
            $username = $this->agent->sn . '-' . $reName;
            $user = User::query()->where('sn', $username)->first();
            $isExit = !!$user ? 1 : 0;
            $data = ['username' => $reName, 'is_exist' => $isExit];
            return $this->successReturn($data);
        }catch (RequestException $e){
            throw new GameException(103);
        }
    }

    /**
     *用户登录(根据秘钥Key定义)
     *
     */
    public function loginRequest(Request $request)
    {
        //TODO 解密的方法暂时未写
        $decryptData = $this->getDecryptData($request);
        // TODO 校验解密数据的数据格式
        try{
            $this->checkSign($request, $decryptData);
            $reName = $decryptData['username'];
            $ip = $decryptData['ip'];
            $deviceType = $decryptData['deviceType'];
        }catch (\Exception $e){
            throw new GameException(106);
        }
        $username = $this->agent->sn . '-' . $reName;
        $token = $request->input('token');
        $user = User::query()->where('sn','=', $username)->first();
        if (!$user) {
            throw new GameException(100);
        }
        if($user->status === 0){
            throw new GameException(101);
        }
        $user->last_login_time = date('Y-m-d H:i:s');
        $user->login_times += 1;
        $user->is_online = 0;
        $user->last_login_ip = $ip;
        if($token) {
            $user->token = $token;
        }
        $user->last_login_location = 'API';
        $user->update();
        $returnData = $this->request('LoginRequest',['username' => $user->sn,'currency' => $this->agent->currency,'ip' => $user->last_login_ip,'deviceType' =>$deviceType,'keyword' => Config::get('agent.keyword')]);
        $returnData['displayName'] = $reName;
        return $this->successReturn($returnData);
    }

    /**
     * @param Request $request
     * 此服务用于获取当前大厅当指定日子(中午12:00:00至上午11:59:59)的下注信息, 没有指定日期则使用服务器当天日期.每5分钟可以调用10次,否则报错.
     */
    public function getAllBetDetailsDV(Request $request){
        $decryptData = $this->getDecryptData($request);
        // TODO 校验解密数据的数据格式
        try{
            $this->checkSign($request, $decryptData);
         //  $reName = $decryptData['username'];
            $selectDate = $decryptData['starTime'];
            $endDate = $decryptData['endTime'];
            $page = $decryptData['page'];
            $perPage = $decryptData['perPage'];
            $gameType = $decryptData['gameType'];
        }catch (\Exception $e){
            throw new GameException(103);
        }
        $round_id = $request->input('game_id');
        if(!$page){
            $page = 1;
        }
        if(!$perPage){
            $perPage = 20;
        }
        $reName = $request->input('username');
        if($reName) {
            $username = $this->agent->sn . '-' . $reName;
            $user = User::query()->where('sn', $username)->first();
        }
        if (!$reName) {
               $betQuery = BillInfo::query()->where('agent_id',$this->agent->id)->where('bet_time', '>=', $selectDate)->where('bet_time', '<=', $endDate);
        }else {
            if($user) {
                $betQuery = BillInfo::query()->where('user_id', $user->id)->where('bet_time', '>=', $selectDate)->where('bet_time', '<=', $endDate);
            }else{
                throw new GameException(100);
            }
        }
        if($gameType!=='') {
            if ((int)$gameType === 0 || (int)$gameType === 1 || (int)$gameType === 2 || (int)$gameType === 3) {
                $betQuery = $betQuery->where('game_type', '=',$gameType);
            }
        }
        if($round_id){
            $betQuery = $betQuery->where('round_id', '=',$round_id);
        }
        $betdetails =  $betQuery->orderByDesc('bet_time')->paginate($perPage,['*'],'page',$page);
        foreach ($betdetails->items() as &$item){
            $item['username'] = explode('-',$item['username'])[1];
        }
        $returnBets = [
            'bet_details' => $betdetails->items(),
            'current_page' => $betdetails->currentPage(),
            'last_page' => $betdetails->lastPage(),
            'total' => $betdetails->total()
        ];
        return $this->successReturn($returnBets);
    }

    /**
     * @param Request $request
     * 试玩接口,试玩接口获取的随机的机器人，目前机器人下注没有订单保存
     *
     */
    public function testPlay(Request $request){
        // TODO 校验解密数据的数据格式
        $decryptData = $this->getDecryptData($request);
        try{
            $this->checkSign($request, $decryptData);
            $type =  $request->input('type');
        }catch (\Exception $e){
            throw new GameException(103);
        }
        $returnData = $this->request('TestPlay',['currency' => $this->agent->currency,'type' =>$type,'keyword' => Config::get('agent.keyword')]);
        return $this->successReturn($returnData);
    }

    /**
     * @param Request $request
     * @return mixed
     * 从用户账户转出点数
     */
    public function debitBalanceDV(Request $request)
    {
        $decryptData = $this->getDecryptData($request);
        // TODO 校验解密数据的数据格式  OrderId 参数
        try{
            $this->checkSign($request, $decryptData);
            $reName = $decryptData['username'];
            $transferId = $decryptData['transferId'];
            $debitAmount = $decryptData['debitAmount'];
            $username = $this->agent->sn . '-' . $reName;
        }catch (\Exception $e){
            throw new GameException(106);
        }
        if($this->agent->is_transfer === 1){
            throw new GameException(106);
        }
        $user = User::query()->where('sn', $username)->first();
        if (!$user) {
            throw new GameException(100);
        }
        if($debitAmount <= $user->point) {
            $transfer = TransferRecord::query()->where('transfer_id','=',$transferId)->first();
            if(!$transfer) {
                TransferRecord::create([
                    'user_id' => $user->agent_id,
                    'user_name' => $user->sn,
                    'name' => $user->nickname,
                    'money_value' => -$debitAmount,
                    'set_money' => -$debitAmount,
                    'transfer_id' => $transferId,
                    'transfer_time' => date('Y-m-d H:i:s'),
                    'w_bank_info' => '钱包提现',
                    'opperson' => $user->sn,
                    'type' => 1,
                    'status' => 3,
                    'd_bank_info' => '提现',
                    'set_name' => $user->sn,
                    'auperson' => '提现',
                    'set_time' => date('Y-m-d H:i:s')
                ]);
                $user->point = $user->point - $debitAmount;
                $user->update();
            }else{
                throw new GameException(122);
            }

        }else{
            throw new GameException(121);
        }
        return $this->successReturn([]);
    }

    /**
     * @param Request $request
     * 充值接口
     */
    public function creditBalanceDV (Request $request)
    {
        $decryptData = $this->getDecryptData($request);
        // TODO 校验解密数据的数据格式  OrderId 参数
        try{
            $this->checkSign($request, $decryptData);
            $reName = $decryptData['username'];
            $transferId = $decryptData['transferId'];
            $creditAmount = $decryptData['creditAmount'];
        }catch (\Exception $e){
            throw new GameException(103);
        }
        if($this->agent->is_transfer === 1){
            throw new GameException(106);
        }
        $username = $this->agent->sn . '-' . $reName;
        $user = User::query()->where('sn', $username)->first();
        if (!$user) {
            throw new GameException(100);
        }
        $transfer = TransferRecord::query()->where('transfer_id','=',$transferId)->first();
        if(!$transfer) {
            TransferRecord::create([
                'user_id' => $user->agent_id,
                'user_name' => $user->sn,
                'name' => $user->nickname,
                'money_value' => $creditAmount,
                'set_money' => $creditAmount,
                'transfer_id' => $transferId,
                'transfer_time' => date('Y-m-d H:i:s'),
                'w_bank_info' => '钱包转账',
                'opperson' => $user->sn,
                'type' => 0,
                'status' => 3,
                'd_bank_info' => '转帐',
                'set_name' => $user->sn,
                'auperson' => '转账',
                'set_time' => date('Y-m-d H:i:s')
            ]);
            $user->point += $creditAmount;
            $user->update();
        }else{
            throw new GameException(122);
        }
        return $this->successReturn([]);
    }

    /**
     * 查询用户点数接口
     *
     */
    public function  getBalance(Request $request){
        $decryptData = $this->getDecryptData($request);
        // TODO 校验解密数据的数据格式  OrderId 参数
        try{
            $this->checkSign($request, $decryptData);
            $reName = $decryptData['username'];
        }catch (\Exception $e){
            throw new GameException(103);
        }
        if($this->agent->is_transfer === 1){
            throw new GameException(106);
        }
        $username = $this->agent->sn . '-' . $reName;
        $user = User::query()->where('sn', $username)->first();
        if (!$user) {
            throw new GameException(100);
        }
        $returnBets = [
            'username' => $reName,
            'balance' => $user->point
        ];
        return $this->successReturn($returnBets);
    }

    /**
     * 查询用户点数接口
     *
     */
    public function  getTransferStatus(Request $request){
        $decryptData = $this->getDecryptData($request);
        // TODO 校验解密数据的数据格式  OrderId 参数
        try{
            $this->checkSign($request, $decryptData);
            $transferId = $decryptData['transferId'];
        }catch (\Exception $e){
            throw new GameException(103);
        }
        if($this->agent->is_transfer === 1){
            throw new GameException(106);
        }
        //0代表成功1代表失败
        $status = 1;
        $transfer = TransferRecord::query()->where('transfer_id','=',$transferId)->first();
        if($transfer){
            if($transfer->status === 3){
                $status = 0;
            }
        }
        $returnBets = [
            'transferId' => $transferId,
            'status' => $status
        ];
        return $this->successReturn($returnBets);
    }
}
