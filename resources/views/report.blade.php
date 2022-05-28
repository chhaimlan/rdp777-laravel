@extends('layouts.app2')
@section('content')
    <p class="tc fs-2 pt-2">REPORT</p>
    <div class="container report">
        <div class="button-report back-report flex space-between">
            <div class="button-1">
                <select class="st-1 back-report" id="gameType" name="gameType" onchange="reporType(this.value)">
                    <option value="0"  @if($gameType === '0') selected  @endif>All</option>
                    <option value="1"  @if($gameType === '1') selected  @endif>LG88 Casino</option>
                    <option value="5"  @if($gameType === '5') selected  @endif>Pragmatic</option>
                    <option value="6"  @if($gameType === '6') selected  @endif>Habanero</option>
                    <option value="7"  @if($gameType === '7') selected  @endif>PG Slots</option>
                    <option value="21"  @if($gameType === '21') selected @endif>AFB Sports</option>
                </select>
            </div>
            <div class="button-2">
                <a href="#" onclick="reporTj(1)"><input class="vz back-report klavika" type="button"  value="Hari Ini"></a>
                <a href="#" onclick="reporTj(2)"><input class="vz back-report klavika" type="button"  value="Kemarin"></a>
                <a href="#" onclick="reporTj(3)"><input class="vz back-report klavika" type="button"  value="Mingguan"></a>
                <a href="#" onclick="reporTj(4)"><input class="vz back-report klavika" type="button"  value="Bulan"></a>
            </div>
        </div>
        <div class="button-3">
            <a href="#"><input class="vx klavika" type="button" value="Bet Record">
            <input type="hidden" id="time" value="{{$time}}"/>
            </a>
        </div>
        <div class="table-1 back-report">
            <table>
                <tr class="tc">
                    <th>No</th>
                    <th>Game No</th>
                    <th>Lobby</th>
                    <th>Table</th>
                    <th>Bet Time</th>
                    <th>Bet</th>
                    <th>Valid Bet</th>
                    <th>Commission</th>
                    <th>Result</th>
                    <th>Win/Lose</th>
                </tr>
                <?php $bacc=[1=>'Banker',2=>'Tie',3=>'Player'];
                $dt=[1=>'Drangon',2=>'Tie',3=>'Tiger'];
                ?>
                @foreach ($param as $bets)
                <tr class="tc">
                    <td>{{ $bets['showId'] }}</td>
                    <td>{{ $bets['gameNo'] }}</td>
                    <td>
                        @if($bets['gameType'] == 5)
                            Pragmatic Slot
                        @elseif($bets['gameType'] == 6)
                            HABANERO Slot
                        @elseif($bets['gameType'] == 7)
                            PG Slot
                        @elseif($bets['gameType'] == 21)
                            AFB Sports
                        @else
                            LG88 Casino
                        @endif
                    </td>
                    <td>{{ $bets['table'] }}</td>
                    <td>{{ $bets['betTime'] }}</td>
                    <td>{{ number_format($bets['bet'], 2, '.', ',')}}</td>
                    <td>{{ number_format($bets['validBet'], 2, '.', ',')}}</td>
                    <td>{{ number_format($bets['user_pump'], 2, '.', ',')}}</td>
                    <td>
                        @if ($bets['gameType'] == 0)
                            @foreach (explode(',',$bets['result']) as $res)
                                @if ($res == 1 )
                                    <a href="#" onclick="lookVideo('{{$bets['videoUrl']}}')" style="color: red">
                                        {{@$bacc[$res]}}
                                    </a>
                                @elseif($res == 2)
                                    <a href="#" onclick="lookVideo('{{$bets['videoUrl']}}')" style="color: green">
                                        {{@$bacc[$res]}}
                                    </a>
                                @elseif($res == 3)
                                    <a href="#" onclick="lookVideo('{{$bets['videoUrl']}}')" style="color: #2779bd">
                                        {{@$bacc[$res]}}
                                    </a>
                                @endif
                            @endforeach
                        @elseif($bets['gameType'] == 1)
                            @foreach (explode(',',$bets['result']) as $res)
                                @if ($res == 1 )
                                    <a href="#" onclick="lookVideo('{{$bets['videoUrl']}}')" style="color: red">
                                        {{@$dt[$res]}}
                                    </a>
                                @elseif($res == 2)
                                    <a href="#" onclick="lookVideo('{{$bets['videoUrl']}}')" style="color: green">
                                        {{@$dt[$res]}}
                                    </a>
                                @elseif($res == 3)
                                    <a href="#" onclick="lookVideo('{{$bets['videoUrl']}}')" style="color: #2779bd">
                                        {{@$dt[$res]}}
                                    </a>
                                @endif
                            @endforeach
                            @elseif($bets['gameType'] == 2)
                                            @if ($bets['resultDetail'])
                                                @if (in_array((json_decode($bets['resultDetail']))->n,[1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36]))
                                                <a href="#" onclick="lookVideo('{{$bets['videoUrl']}}')" style="color: red">
                                                                {{json_decode($bets['resultDetail'])->n}}
                                                </a>
                                                @elseif(json_decode($bets['resultDetail'])->n == 0)
                                                <a href="#" onclick="lookVideo('{{$bets['videoUrl']}}')" style="color: green">
                                                    {{json_decode($bets['resultDetail'])->n}}
                                                </a>
                                                @else
                                                <a href="#" onclick="lookVideo('{{$bets['videoUrl']}}')" style="color: #636b6f">
                                                    {{json_decode($bets['resultDetail'])->n}}
                                                </a>
                                                @endif
                            @endif
                            @elseif($bets['gameType'] == 3)
                                <a href="#" onclick="lookVideo('{{$bets['videoUrl']}}')">
                                    @if ($bets['resultDetail'])
                                        <img src="assets/img/Dice/dice_{{json_decode($bets['resultDetail'])[0]}}.png" alt="">
                                        <img src="assets/img/Dice/dice_{{json_decode($bets['resultDetail'])[1]}}.png" alt="">
                                        <img src="assets/img/Dice/dice_{{json_decode($bets['resultDetail'])[2]}}.png" alt="">
                                    @endif
                                </a>
                            @elseif($bets['gameType'] == 5)
                                <a href="#"  onclick="checkResult('{{$bets['round_id']}}')">
                                    Enter Detail
                                </a>
                            @elseif($bets['gameType'] == 6)
                                <a href="#"  onclick="checkHb('{{$bets['round_id']}}')">
                                    Enter Detail
                                </a>
                           @elseif($bets['gameType'] == 7)

                            </a>
                            @elseif($bets['gameType'] == 21)
                            <a href="#" onclick="getAfbResult('{{$bets['transfer_id']}}')" >
                                {{$bets['resultDetail']}}
                                <br>
                                {{$bets['bet_type']}}
                            </a>
                            @endif
                    </td>

                      <td >
                          @if ($bets['winlose'] > 0)
                             <a style="color: green">
                            {{ number_format($bets['winlose'], 2, '.', ',') }}
                             </a>
                          @else
                              <a style="color: red">
                                  {{ number_format($bets['winlose'], 2, '.', ',') }}
                              </a>
                          @endif
                      </td>
                </tr>
                @endforeach
                <tr class="tc">
                    <td colspan="5"><p class="tr">Subtotal</p></td>
                    <td>{{ number_format($curBets['curBetAmount'], 2, '.', ',') }}</td>
                    <td>{{ number_format($curBets['curValidBet'], 2, '.', ',') }}</td>
                    <td></td>
                    <td></td>
                    <td>
                        @if ($curBets['curWinLose'] > 0)
                            <a style="color: green">
                                {{ number_format($curBets['curWinLose'], 2, '.', ',') }}
                            </a>
                        @else
                            <a style="color: red">
                                {{ number_format($curBets['curWinLose'], 2, '.', ',') }}
                            </a>
                        @endif
                    </td>
                </tr>
                <tr class="tc">
                    <td colspan="5"><p class="tr">Total</p></td>
                    <td>{{ number_format($total['betAmountCount'], 2, '.', ',') }}</td>
                    <td>{{ number_format($total['validBetCount'], 2, '.', ',') }}</td>
                    <td></td>
                    <td></td>
                    <td>
                        @if ($total['winloseCount'] > 0)
                            <a style="color: green">
                                {{ number_format($total['winloseCount'], 2, '.', ',') }}
                            </a>
                        @else
                            <a style="color: red">
                                {{ number_format($total['winloseCount'], 2, '.', ',') }}
                            </a>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        <div style="float: right;display: inline;">
            {{ $billList->appends(['time' => $time,'gameType' => $gameType])->links() }}
        </div>
    </div>

         <div class="contact flex">
				<a href="#"><i class="fab fa-whatsapp"></i><h3>Whtasapp</h3>1234567890</a>
				<a href="#"><i class="fab fa-facebook"></i><h3>Facebook</h3>1234567890</a>
				<a href="#"><i class="fab fa-instagram"></i><h3>Instagram</h3>1234567890</a>
				<a href="#"><i class="fab fa-telegram-plane"></i><h3>Telegram</h3>1234567890</a>
				<a href="#"><i class="fab fa-line"></i><h3>Line</h3>1234567890</a>
		</div>
		<div class="content">
			<h2>Title Here</h2>
			Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi
			ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui
			officia deserunt mollit anim id est laborum.
			<br>
			Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta
			sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem
			ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem
			ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat
			quo voluptas nulla pariatur?
		</div>

    <script type="text/javascript">
        function reporTj(obj) {
            var seleTime = '';
            var gameType = $('#gameType').val();
              if(obj === 1) {
                  seleTime = 'today'
              }
              if(obj === 2) {
                    seleTime = 'yesterday'
              }
            if(obj === 3) {
                seleTime = 'week'
            }
            if(obj === 4) {
                seleTime = 'month'
            }
             window.location.href='/reportList?time='+seleTime+'&&page=1&gameType='+gameType;
        }
        function reporType(obj){
            var time = $('#time').val();
            // if(time === 1) {
            //     seleTime = 'today'
            // }
            // if(time === 2) {
            //     seleTime = 'yesterday'
            // }
            // if(time === 3) {
            //     seleTime = ' week'
            // }
            window.location.href='/reportList?time='+time+'&&page=1&&gameType='+obj;
        }
        function lookVideo(obj) {
            window.open(obj, "PLAY VIDEO", "height=500,width=600");
        }
        function checkResult(obj){
            var playerId = "{{Auth::user()->id}}";
            $.post('/getGameResult',{roundId:obj,playerId:playerId}, (data) => {
                if (data.error === 0) {
                    window.open(data.url);
                }
            }, 'json').error(function () {
                  console.warn('查看结果出错');
                }
            )
        }
        function checkHb(obj){
             // var brandid = 'c14da484-bd5f-ec11-94f6-0050f238c13c'.toLowerCase();
             // var gameinstanceid = obj.toLowerCase();
             // var apiKey = 'q9RWC5L3jXfBEfU'.toLowerCase();
             // var hash = sha256_digest(gameinstanceid + brandid + apiKey);
             // $url = 'https://app-test.insvr.com/games/history/?brandid=c14da484-bd5f-ec11-94f6-0050f238c13c&gameinstanceid='+obj+'&hash'+hash+'&locale=en&viewtype=game&showpaytable=1';
             // window.open($url);
            $.get('/getHbResult',{roundId:obj}, (data) => {
                if (data.error === 0) {
                    window.open(data.url);
                }
            }, 'json').error(function () {
                    console.warn('查看结果出错');
                }
            )

        }

        function checkPg(obj1,obj2){
            $.get('/getPgResult',{psid:obj1,sid:obj2}, (data) => {
                if (data.error === 0) {
                    window.open(data.url);
                }
            }, 'json').error(function () {
                    console.warn('查看结果出错');
                }
            )
        }

        function getAfbResult(obj1){
            var username = "{{Auth::user()->sn}}";
            $.get('/getAfbResult',{username:username,roundId:obj1}, (data) => {
                if (data.error === 0) {
                    window.open(data.url);
                }
            }, 'json').error(function () {
                    console.warn('查看结果出错');
                }
            )
        }

    </script>
@endsection
