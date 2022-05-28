<?php


namespace App\Http\Controllers;


use App\MsgInfo;
use App\NoticeInfo;
use App\TransferRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class NoticeController extends Controller
{
    //获取最新的公告信息5条
    public function newList(Request $request)
    {
        $agent_id =  Config::get('agent.agentId');
        $notices = NoticeInfo::query()->where('status', '=', 1)->where('agent_id','=',$agent_id)
            ->where('notice_type', '=', 2)->orderByDesc('created_at')->get();
        return new Response(['data' => ['notices' => $notices],'code' => 1]);
    }

   //公告列表
    public function noctie(Request $request)
    {
        $notices = NoticeInfo::query()->where('status', '=', 1)
            ->where('notice_type', '=', 1)->orderByDesc('created_at')->paginate(15);
        $viewData = [
            'notices' => $notices
        ];
        return view('notice', $viewData);
    }

    //消息列表
    public function msgLsit(Request $request)
    {
        $user = Auth::user();
        $msgs = MsgInfo::query()->where('status', '=', 1)
            ->where('user_name', '=', $user->sn)->orderByDesc('created_at')->paginate(15);
        $viewData = [
            'msgs' => $msgs
        ];
        return view('msg', $viewData);
    }

    public function  getMsg(Request $request){
        $page = 1;
        if($request->input('page')) {
            $page = (int)$request->input('page');
        }
        $username = $request->input('username');
        $msgs = MsgInfo::query()->where('status', '=', 1)
            ->where('user_name', '=', $username)->orderByDesc('created_at')->paginate(20, '*', 'page', $page);
        $mate['currentPage'] = $msgs->currentPage();
        $mate['total'] = $msgs->total();
        $items = [];
        foreach ($msgs as $r) {
            $item['date'] = date('Y-m-d H:i:s', strtotime($r->created_at));
            $item['notice_name'] = $r->notice_name;
            $item['content'] = $r->content;
            array_push($items, $item);
        }
        return new Response(['data' => ['param' => $items,'mate' => $mate],'code' => 1]);
    }
}
