<?php
namespace App\Http\Controllers\OpenApi;
use App\Agent;
use App\Exceptions\GameException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class BaseInfoController extends Controller
{
    protected $agent;


    public function __construct(Request $request)
    {
        $agent_sn = $request->header('x-agent');
        if(empty($agent_sn)){
            throw new GameException(104);
        }
        $keyword = $request->input('keyword');
        $agent = Agent::where('sn',$agent_sn)->first();
        if(!$agent){
            throw new GameException(105);
        }
        if($agent->secret!=$keyword){
            throw new GameException(105);
        }
        if($agent['status'] !== 1){
            throw new GameException(105);
        }
        $this->agent = $agent;
    }

    /**
     * 解密流程(todo)
     * @param Request $request
     * @return mixed
     */
    protected function getDecryptData(Request $request){
         $dataArray = $request->input();
         $sign = $request->input('sign');
         $t = $request->input('time');
         $dataArray = array_diff($dataArray, [$sign, $t]);
         return $dataArray;
    }

    protected function checkTime($data){
        // 接口请求时间超过5秒,就超时
        if($data['timestamp']>time()){
            throw new GameException(111);
        }
    }

    /**
     * 加密方法未定,先注释
     * @param Request $request
     * @param $decryptData
     */
    protected function checkSign(Request $request,$decryptData){
          $t = $request->input('time');
          $sign = $request->input('sign');
          $decryptData = json_encode($decryptData);
          $nowSign = md5($decryptData.'*'.$t);
        //  Log::warning($nowSign);
          if($sign != $nowSign){
              throw new GameException(103);
          }
    }

    protected function checkCurrency($currencySn){

    }

    protected function successReturn(array $data, array $headers = [], $options = 0)
    {
        $returnData =[
            'code'=>0,
            'msg' =>'success',
            'data'=>$data
        ];
        response()->json($returnData, Response::HTTP_OK, $headers, $options)->send();
    }
}

