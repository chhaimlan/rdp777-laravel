<?php

namespace App\Http\Controllers;

use App\Exceptions\GameException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    /**
     * 内部调用game-api 项目的接口
     */
    public function request($uri,$requestData){
        try{
            $client = new Client([
                'base_uri' => Config::get('agent.apiUrl'),
                'timeout'  => 5.0,
            ]);
            $response = $client->request('post', $uri, [
                'headers' => [
                    'x-agent' => Config::get('agent.agentSn')
                ],
                'json'    => $requestData,
            ]);
            $returnResult = json_decode( $response->getBody()->getContents(),true);
            if($returnResult['code'] !== 0){
                throw new GameException(107);
            }
            return $returnResult['data'];
        }catch (RequestException $e){
            throw new GameException(107);
        }
    }
}
